<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\AdScriptTask;
use Livewire\Attributes\On;
use Livewire\Component;

class TaskShow extends Component
{
    public AdScriptTask $task;

    public function mount(AdScriptTask $task): void
    {
        $this->task = $task;
    }

    #[On('task-updated')]
    public function handleTaskUpdated($id): void
    {
        if ($id === $this->task->id) {
            $this->task->refresh();
        }
    }

    public function render()
    {
        return view('livewire.task-show');
    }
}
