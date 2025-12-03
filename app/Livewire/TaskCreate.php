<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Jobs\TriggerN8nWorkflow;
use App\Models\AdScriptTask;
use Livewire\Component;

class TaskCreate extends Component
{
    public string $reference_script = '';
    public string $outcome_description = '';

    protected function rules(): array
    {
        return [
            'reference_script' => 'required|string|min:10|max:50000',
            'outcome_description' => 'required|string|min:5|max:2000',
        ];
    }

    public function save()
    {
        $this->validate();

        $task = AdScriptTask::create([
            'reference_script' => $this->reference_script,
            'outcome_description' => $this->outcome_description,
            'status' => 'pending',
        ]);

        TriggerN8nWorkflow::dispatch($task);

        session()->flash('success', 'Task created successfully!');

        return redirect()->route('tasks.show', $task);
    }

    public function render()
    {
        return view('livewire.task-create');
    }
}
