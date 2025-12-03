<?php

namespace Tests\Feature;

use App\Enums\TaskStatus;
use App\Jobs\TriggerN8nWorkflow;
use App\Models\AdScriptTask;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AdScriptApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_task_with_valid_data(): void
    {
        Queue::fake();

        $response = $this->postJson('/api/ad-scripts', [
            'reference_script' => 'Buy our amazing product now!',
            'outcome_description' => 'Make it more professional',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => ['id', 'status'],
            ]);

        $this->assertDatabaseHas('ad_script_tasks', [
            'reference_script' => 'Buy our amazing product now!',
            'status' => 'pending',
        ]);
    }

    public function test_dispatches_job_on_task_creation(): void
    {
        Queue::fake();

        $this->postJson('/api/ad-scripts', [
            'reference_script' => 'Test script content',
            'outcome_description' => 'Make it better',
        ]);

        Queue::assertPushed(TriggerN8nWorkflow::class);
    }

    public function test_validates_required_fields(): void
    {
        $response = $this->postJson('/api/ad-scripts', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['reference_script', 'outcome_description']);
    }

    public function test_validates_minimum_length(): void
    {
        $response = $this->postJson('/api/ad-scripts', [
            'reference_script' => 'short',
            'outcome_description' => 'ok',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['reference_script', 'outcome_description']);
    }

    public function test_can_retrieve_task_by_id(): void
    {
        $task = AdScriptTask::factory()->completed()->create();

        $response = $this->getJson("/api/ad-scripts/{$task->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'reference_script',
                    'outcome_description',
                    'new_script',
                    'analysis',
                    'status',
                ],
            ]);
    }

    public function test_returns_404_for_nonexistent_task(): void
    {
        $response = $this->getJson('/api/ad-scripts/99999');

        $response->assertStatus(404);
    }

    public function test_callback_endpoint_requires_valid_signature(): void
    {
        $task = AdScriptTask::factory()->processing()->create();

        $response = $this->postJson("/api/ad-scripts/{$task->id}/result", [
            'task_id' => $task->id,
            'new_script' => 'Improved script',
            'analysis' => 'Made it better',
        ]);

        $response->assertStatus(401);
    }

    public function test_callback_endpoint_accepts_valid_signature(): void
    {
        $task = AdScriptTask::factory()->processing()->create();

        $payload = [
            'task_id' => $task->id,
            'new_script' => 'Improved script',
            'analysis' => 'Made it better',
        ];

        $signature = hash_hmac('sha256', json_encode($payload), config('services.n8n.secret'));

        $response = $this->postJson(
            "/api/ad-scripts/{$task->id}/result",
            $payload,
            ['X-N8N-Signature' => $signature]
        );

        $response->assertStatus(200);

        $this->assertDatabaseHas('ad_script_tasks', [
            'id' => $task->id,
            'status' => 'completed',
            'new_script' => 'Improved script',
        ]);
    }

    public function test_callback_updates_task_status_to_completed(): void
    {
        $task = AdScriptTask::factory()->processing()->create();

        $payload = [
            'task_id' => $task->id,
            'new_script' => 'New improved script',
            'analysis' => 'Analysis of changes',
        ];

        $signature = hash_hmac('sha256', json_encode($payload), config('services.n8n.secret'));

        $this->postJson(
            "/api/ad-scripts/{$task->id}/result",
            $payload,
            ['X-N8N-Signature' => $signature]
        );

        $task->refresh();
        $this->assertEquals(TaskStatus::Completed, $task->status);
        $this->assertEquals('New improved script', $task->new_script);
    }

    public function test_callback_rejects_tampered_payload(): void
    {
        $task = AdScriptTask::factory()->processing()->create();

        $originalPayload = ['task_id' => $task->id, 'new_script' => 'Original'];
        $signature = hash_hmac('sha256', json_encode($originalPayload), config('services.n8n.secret'));

        $tamperedPayload = ['task_id' => $task->id, 'new_script' => 'Tampered!'];

        $response = $this->postJson(
            "/api/ad-scripts/{$task->id}/result",
            $tamperedPayload,
            ['X-N8N-Signature' => $signature]
        );

        $response->assertStatus(401);
    }
}
