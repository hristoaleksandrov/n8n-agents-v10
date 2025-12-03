<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TaskStatus;
use App\Events\TaskStatusUpdated;
use App\Services\SlackService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdScriptTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_script',
        'outcome_description',
        'new_script',
        'analysis',
        'status',
        'error_message',
    ];

    protected $casts = [
        'status' => TaskStatus::class,
    ];

    public function markAsProcessing(): void
    {
        $this->update(['status' => TaskStatus::Processing]);
        event(new TaskStatusUpdated($this));
    }

    public function markAsCompleted(string $newScript, string $analysis): void
    {
        $this->update([
            'status' => TaskStatus::Completed,
            'new_script' => $newScript,
            'analysis' => $analysis,
        ]);
        event(new TaskStatusUpdated($this));
        app(SlackService::class)->notifyTaskCompleted($this);
    }

    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => TaskStatus::Failed,
            'error_message' => $errorMessage,
        ]);
        event(new TaskStatusUpdated($this));
        app(SlackService::class)->notifyTaskFailed($this);
    }

    public function isPending(): bool
    {
        return $this->status === TaskStatus::Pending;
    }

    public function isProcessing(): bool
    {
        return $this->status === TaskStatus::Processing;
    }

    public function isCompleted(): bool
    {
        return $this->status === TaskStatus::Completed;
    }

    public function isFailed(): bool
    {
        return $this->status === TaskStatus::Failed;
    }
}
