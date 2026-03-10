<?php

namespace App\Http\Controllers;

use App\Models\Projects;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use App\Jobs\SendTaskNotificationJob;

class TaskController extends Controller
{
    public function __construct(private readonly TaskService $taskService)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        
        try {
            $tasks = $this->taskService->listForUser(auth()->id(), [
                'status'     => request('status'),
                'priority'   => request('priority'),
                'start_from' => request('start_from'),
                'start_to'   => request('start_to'),
                'end_from'   => request('end_from'),
                'end_to'     => request('end_to'),
            ], 10);

            if ($tasks->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'data'    => [],
                    'message' => 'No tasks found.',
                ]);
            }

            return response()->json([
                'success' => true,
                'data'    => $tasks,
            ]);
        } catch (\Throwable $e) {
            Log::error('Task index error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch tasks.',
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'task_name'  => ['required', 'string', 'max:255'],
                'project_id' => ['required', 'integer', 'exists:projects,id'],
                'status'     => ['required', 'in:pending,on-going,testing,done,complete,rework'],
                'priority'   => ['required', 'in:high,medium,low'],
                'start_date' => ['required', 'date'],
                'end_date'   => ['required', 'date'],
            ]);

            $userId = auth()->id();
            $task = $this->taskService->createForUser($userId, $data);

            if (! app()->environment('testing')) {
                SendTaskNotificationJob::dispatch($task, $userId);
            }
            return response()->json([
                'success' => true,
                'data'    => $task,
                'message' => 'Task created successfully.',
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\RuntimeException $e) {
            $code = $e->getCode();
            if ($code < 400 || $code > 599) {
                $code = 500;
            }
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $code);
        } catch (AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 403);
        } catch (\Throwable $e) {
            Log::error('Task store error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create task.',
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task): JsonResponse
    {
        try {
            $task = $this->taskService->getForUser(auth()->id(), $task);

            return response()->json([
                'success' => true,
                'data'    => $task,
            ]);
        } catch (AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 403);
        } catch (\Throwable $e) {
            Log::error('Task show error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch task.',
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task): JsonResponse
    {
        try {
            $data = $request->validate([
                'task_name'  => ['sometimes', 'string', 'max:255'],
                'project_id' => ['sometimes', 'integer', 'exists:projects,id'],
                'status'     => ['sometimes', 'in:complete,rework'],
                'priority'   => ['sometimes', 'in:high,medium,low'],
                'start_date' => ['sometimes', 'date'],
                'end_date'   => ['sometimes', 'date'],
            ]);

            $task = $this->taskService->updateForUser(auth()->id(), $task, $data);

            return response()->json([
                'success' => true,
                'data'    => $task->fresh('project', 'user'),
                'message' => 'Task updated successfully.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\RuntimeException $e) {
            $code = $e->getCode();
            if ($code < 400 || $code > 599) {
                $code = 500;
            }
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $code);
        } catch (AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 403);
        } catch (\Throwable $e) {
            Log::error('Task update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update task.',
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task): JsonResponse
    {
        try {
            $this->taskService->deleteForUser(auth()->id(), $task);

            return response()->json([
                'success' => true,
                'message' => 'Task deleted successfully.',
            ]);
        } catch (AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 403);
        } catch (\Throwable $e) {
            Log::error('Task destroy error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete task.',
            ], 500);
        }
    }
}
