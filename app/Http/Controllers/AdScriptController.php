<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\TaskStatus;
use App\Http\Requests\AdScriptResultRequest;
use App\Http\Requests\StoreAdScriptRequest;
use App\Jobs\TriggerN8nWorkflow;
use App\Models\AdScriptTask;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class AdScriptController extends Controller
{
    public function store(StoreAdScriptRequest $request): JsonResponse
    {
        $task = AdScriptTask::create([
            'reference_script' => $request->validated('reference_script'),
            'outcome_description' => $request->validated('outcome_description'),
            'status' => TaskStatus::Pending,
        ]);

        TriggerN8nWorkflow::dispatch($task);

        return response()->json([
            'message' => 'Task created successfully.',
            'data' => [
                'id' => $task->id,
                'status' => $task->status->value,
            ],
        ], Response::HTTP_CREATED);
    }

    public function show(AdScriptTask $task): JsonResponse
    {
        return response()->json([
            'data' => [
                'id' => $task->id,
                'reference_script' => $task->reference_script,
                'outcome_description' => $task->outcome_description,
                'new_script' => $task->new_script,
                'analysis' => $task->analysis,
                'status' => $task->status->value,
                'error_message' => $task->error_message,
                'created_at' => $task->created_at->toIso8601String(),
                'updated_at' => $task->updated_at->toIso8601String(),
            ],
        ]);
    }

    public function result(AdScriptTask $task, AdScriptResultRequest $request): JsonResponse
    {
        if ($task->status->isTerminal()) {
            return response()->json([
                'message' => 'Task already processed.',
                'status' => $task->status->value,
            ], Response::HTTP_CONFLICT);
        }

        if ($request->isSuccess()) {
            $task->markAsCompleted(
                $request->validated('new_script'),
                $request->validated('analysis')
            );

            return response()->json([
                'message' => 'Task completed successfully.',
                'status' => $task->status->value,
            ]);
        }

        $task->markAsFailed($request->validated('error'));

        return response()->json([
            'message' => 'Task marked as failed.',
            'status' => $task->status->value,
        ]);
    }
}
