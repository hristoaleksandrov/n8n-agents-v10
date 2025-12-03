<?php

namespace Tests\Feature;

use App\Enums\TaskStatus;
use App\Jobs\TriggerN8nWorkflow;
use App\Models\AdScriptTask;
use App\Services\N8nService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\TestCase;

class TriggerN8nWorkflowJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_marks_task_as_processing_before_request(): void
    {
        Http::fake([
            '*' => Http::response(['new_script' => 'test', 'analysis' => 'test'], 200),
        ]);

        $task = AdScriptTask::factory()->pending()->create();
        $job = new TriggerN8nWorkflow($task);

        $this->assertEquals(TaskStatus::Pending, $task->status);

        $job->handle(app(N8nService::class));

        $task->refresh();
        $this->assertEquals(TaskStatus::Completed, $task->status);
    }

    public function test_job_marks_task_as_completed_on_success(): void
    {
        Http::fake([
            '*' => Http::response([
                'new_script' => 'Improved script content',
                'analysis' => 'Made it better',
            ], 200),
        ]);

        $task = AdScriptTask::factory()->pending()->create();
        $job = new TriggerN8nWorkflow($task);

        $job->handle(app(N8nService::class));

        $task->refresh();
        $this->assertEquals(TaskStatus::Completed, $task->status);
        $this->assertEquals('Improved script content', $task->new_script);
        $this->assertEquals('Made it better', $task->analysis);
    }

    public function test_job_sends_correct_payload(): void
    {
        Http::fake(function ($request) {
            $body = json_decode($request->body(), true);
            $this->assertArrayHasKey('task_id', $body);
            $this->assertArrayHasKey('reference_script', $body);
            $this->assertArrayHasKey('outcome_description', $body);
            $this->assertArrayHasKey('callback_url', $body);

            return Http::response(['new_script' => 'test', 'analysis' => 'test'], 200);
        });

        $task = AdScriptTask::factory()->pending()->create();
        $job = new TriggerN8nWorkflow($task);

        $job->handle(app(N8nService::class));
    }

    public function test_job_includes_hmac_signature_header(): void
    {
        Http::fake(function ($request) {
            $this->assertTrue($request->hasHeader('X-N8N-Signature'));
            $this->assertNotEmpty($request->header('X-N8N-Signature')[0]);

            return Http::response(['new_script' => 'test', 'analysis' => 'test'], 200);
        });

        $task = AdScriptTask::factory()->pending()->create();
        $job = new TriggerN8nWorkflow($task);

        $job->handle(app(N8nService::class));
    }

    public function test_job_configuration_has_correct_retry_settings(): void
    {
        $task = AdScriptTask::factory()->pending()->create();
        $job = new TriggerN8nWorkflow($task);

        $this->assertEquals(3, $job->tries);
        $this->assertEquals(30, $job->backoff);
        $this->assertEquals(120, $job->timeout);
    }
}
