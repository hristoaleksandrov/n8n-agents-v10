<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\AdScriptTask;
use App\Services\N8nService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class TriggerN8nWorkflow implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $backoff = 30;

    public int $timeout = 120;

    public function __construct(
        public AdScriptTask $task,
    ) {}

    public function handle(N8nService $n8nService): void
    {
        $this->task->markAsProcessing();

        try {
            $response = $n8nService->triggerWorkflow($this->task);

            if ($response->successful() && $response->json('new_script')) {
                $this->task->markAsCompleted(
                    $response->json('new_script'),
                    $response->json('analysis', '')
                );

                return;
            }

            if ($response->failed()) {
                Log::warning('n8n workflow trigger failed', [
                    'task_id' => $this->task->id,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (Throwable $e) {
            Log::error('n8n workflow trigger exception', [
                'task_id' => $this->task->id,
                'exception' => $e->getMessage(),
            ]);

            if ($this->attempts() >= $this->tries) {
                $this->task->markAsFailed('Failed to trigger n8n workflow: '.$e->getMessage());
            }

            throw $e;
        }
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('TriggerN8nWorkflow job failed permanently', [
            'task_id' => $this->task->id,
            'exception' => $exception?->getMessage(),
        ]);

        $this->task->markAsFailed(
            'Job failed after '.$this->tries.' attempts: '.($exception?->getMessage() ?? 'Unknown error')
        );
    }
}
