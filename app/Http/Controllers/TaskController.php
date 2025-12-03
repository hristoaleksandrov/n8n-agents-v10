<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\TaskStatus;
use App\Jobs\TriggerN8nWorkflow;
use App\Models\AdScriptTask;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(): View
    {
        $tasks = AdScriptTask::latest()->paginate(10);

        return view('tasks.index', compact('tasks'));
    }

    public function create(): View
    {
        return view('tasks.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'reference_script' => ['required', 'string', 'min:10', 'max:50000'],
            'outcome_description' => ['required', 'string', 'min:5', 'max:2000'],
        ]);

        $task = AdScriptTask::create([
            ...$validated,
            'status' => TaskStatus::Pending,
        ]);

        TriggerN8nWorkflow::dispatch($task);

        return redirect()->route('tasks.show', $task)
            ->with('success', 'Task created and queued for processing.');
    }

    public function show(AdScriptTask $task): View
    {
        return view('tasks.show', compact('task'));
    }
}
