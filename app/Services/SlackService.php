<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AdScriptTask;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SlackService
{
    public function __construct(
        private readonly ?string $webhookUrl = null,
    ) {}

    public function notifyTaskCompleted(AdScriptTask $task): void
    {
        $url = $this->webhookUrl ?? config('services.slack.webhook_url');

        if (empty($url)) {
            return;
        }

        try {
            Http::post($url, [
                'text' => "Task #{$task->id} completed",
                'blocks' => [
                    [
                        'type' => 'header',
                        'text' => [
                            'type' => 'plain_text',
                            'text' => "Ad Script Task #{$task->id} Completed",
                        ],
                    ],
                    [
                        'type' => 'section',
                        'fields' => [
                            [
                                'type' => 'mrkdwn',
                                'text' => "*Status:*\n{$task->status->label()}",
                            ],
                            [
                                'type' => 'mrkdwn',
                                'text' => "*Created:*\n{$task->created_at->diffForHumans()}",
                            ],
                        ],
                    ],
                    [
                        'type' => 'section',
                        'text' => [
                            'type' => 'mrkdwn',
                            'text' => "*Goal:*\n".substr($task->outcome_description, 0, 200),
                        ],
                    ],
                ],
            ]);
        } catch (\Throwable $e) {
            Log::warning('Slack notification failed', [
                'task_id' => $task->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function notifyTaskFailed(AdScriptTask $task): void
    {
        $url = $this->webhookUrl ?? config('services.slack.webhook_url');

        if (empty($url)) {
            return;
        }

        try {
            Http::post($url, [
                'text' => "Task #{$task->id} failed",
                'blocks' => [
                    [
                        'type' => 'header',
                        'text' => [
                            'type' => 'plain_text',
                            'text' => "Ad Script Task #{$task->id} Failed",
                            'emoji' => true,
                        ],
                    ],
                    [
                        'type' => 'section',
                        'fields' => [
                            [
                                'type' => 'mrkdwn',
                                'text' => "*Error:*\n{$task->error_message}",
                            ],
                        ],
                    ],
                ],
            ]);
        } catch (\Throwable $e) {
            Log::warning('Slack notification failed', [
                'task_id' => $task->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
