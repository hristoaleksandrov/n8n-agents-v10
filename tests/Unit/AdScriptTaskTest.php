<?php

namespace Tests\Unit;

use App\Enums\TaskStatus;
use App\Models\AdScriptTask;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdScriptTaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_task(): void
    {
        $task = AdScriptTask::create([
            'reference_script' => 'Test script',
            'outcome_description' => 'Make it better',
            'status' => TaskStatus::Pending,
        ]);

        $this->assertDatabaseHas('ad_script_tasks', [
            'id' => $task->id,
            'reference_script' => 'Test script',
        ]);
    }

    public function test_status_is_cast_to_enum(): void
    {
        $task = AdScriptTask::factory()->pending()->create();

        $this->assertInstanceOf(TaskStatus::class, $task->status);
    }

    public function test_mark_as_processing(): void
    {
        $task = AdScriptTask::factory()->pending()->create();

        $task->markAsProcessing();

        $this->assertEquals(TaskStatus::Processing, $task->fresh()->status);
    }

    public function test_mark_as_completed(): void
    {
        $task = AdScriptTask::factory()->processing()->create();

        $task->markAsCompleted('New script content', 'Analysis here');

        $task->refresh();
        $this->assertEquals(TaskStatus::Completed, $task->status);
        $this->assertEquals('New script content', $task->new_script);
        $this->assertEquals('Analysis here', $task->analysis);
    }

    public function test_mark_as_failed(): void
    {
        $task = AdScriptTask::factory()->processing()->create();

        $task->markAsFailed('Something went wrong');

        $task->refresh();
        $this->assertEquals(TaskStatus::Failed, $task->status);
        $this->assertEquals('Something went wrong', $task->error_message);
    }

    public function test_is_pending(): void
    {
        $task = AdScriptTask::factory()->pending()->create();
        $this->assertTrue($task->isPending());
        $this->assertFalse($task->isProcessing());
    }

    public function test_is_completed(): void
    {
        $task = AdScriptTask::factory()->completed()->create();
        $this->assertTrue($task->isCompleted());
        $this->assertFalse($task->isPending());
    }

    public function test_is_failed(): void
    {
        $task = AdScriptTask::factory()->failed()->create();
        $this->assertTrue($task->isFailed());
        $this->assertFalse($task->isCompleted());
    }
}
