<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\AdScriptTask;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class TaskList extends Component
{
    use WithPagination;

    #[On('task-updated')]
    public function handleTaskUpdated(): void
    {
        // Component will automatically re-render when this event is received
    }

    public function render()
    {
        return view('livewire.task-list', [
            'tasks' => AdScriptTask::latest()->paginate(10),
        ]);
    }
}
