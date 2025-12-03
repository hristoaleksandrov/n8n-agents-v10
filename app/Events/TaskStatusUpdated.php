<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\AdScriptTask;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public AdScriptTask $task
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('tasks'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'task.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->task->id,
            'status' => $this->task->status->value,
            'status_label' => $this->task->status->label(),
            'new_script' => $this->task->new_script,
            'analysis' => $this->task->analysis,
            'error_message' => $this->task->error_message,
        ];
    }
}
