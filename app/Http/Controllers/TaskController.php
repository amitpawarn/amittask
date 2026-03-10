<?php

namespace App\Http\Controllers;

use App\Models\Projects;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use App\Jobs\SendTaskNotificationJob;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        
        try {
            $query = Task::with('project', 'user')
                ->where('user_id', auth()->id());

            if (request('status')) {
                $query->where('status', request('status'));
            }

            if (request('priority')) {
                $query->where('priority', request('priority'));
            }

            if (request('start_from')) {
                $query->where('start_date', '>=', request('start_from'));
            }
            if (request('start_to')) {
                $query->where('start_date', '<=', request('start_to'));
            }

            if (request('end_from')) {
                $query->where('end_date', '>=', request('end_from'));
            }
            if (request('end_to')) {
                $query->where('end_date', '<=', request('end_to'));
            }

            $tasks = $query->paginate(10);

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

            $project = Projects::where('id', $data['project_id'])
                ->where('user_id', auth()->id())
                ->first();

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project not found or you do not have access to it.',
                ], 404);
            }

            $data['project_name'] = $project->name;
            $data['user_id'] = auth()->id();
            $task = Task::create($data);
            SendTaskNotificationJob::dispatch($task, $data['user_id']);
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
            if ($task->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have access to this task.',
                ], 403);
            }

            $task->load('project', 'user');

            return response()->json([
                'success' => true,
                'data'    => $task,
            ]);
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
            if ($task->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have access to this task.',
                ], 403);
            }

            $data = $request->validate([
                'task_name'  => ['sometimes', 'string', 'max:255'],
                'project_id' => ['sometimes', 'integer', 'exists:projects,id'],
                'status'     => ['sometimes', 'in:complete,rework'],
                'priority'   => ['sometimes', 'in:high,medium,low'],
                'start_date' => ['sometimes', 'date'],
                'end_date'   => ['sometimes', 'date'],
            ]);

            if (isset($data['project_id'])) {
                $project = Projects::where('id', $data['project_id'])
                    ->where('user_id', auth()->id())
                    ->first();

                if (!$project) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Project not found or you do not have access to it.',
                    ], 404);
                }
                $data['project_name'] = $project->name;
            }

            $task->update($data);

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
            if ($task->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have access to this task.',
                ], 403);
            }

            $task->delete();

            return response()->json([
                'success' => true,
                'message' => 'Task deleted successfully.',
            ]);
        } catch (\Throwable $e) {
            Log::error('Task destroy error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete task.',
            ], 500);
        }
    }
}
