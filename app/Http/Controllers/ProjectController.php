<?php

namespace App\Http\Controllers;

use App\Models\Projects;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $projects = Projects::with('tasks', 'user')
                ->where('user_id', auth()->id())
                ->paginate(10);

            if ($projects->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'data'    => [],
                    'message' => 'No projects found.',
                ]);
            }

            return response()->json([
                'success' => true,
                'data'    => $projects,
            ]);
        } catch (\Throwable $e) {
            Log::error('Project index error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch projects.',
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
                'name'       => ['required', 'string', 'max:255'],
                'start_date' => ['required', 'date'],
                'end_date'   => ['required', 'date'],
            ]);

            $data['user_id'] = auth()->id();
            $project = Projects::create($data);

            return response()->json([
                'success' => true,
                'data'    => $project,
                'message' => 'Project created successfully.',
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Project store error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create project.',
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Projects $project): JsonResponse
    {
        try {
            if ($project->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have access to this project.',
                ], 403);
            }

            $project->load('tasks', 'user');

            return response()->json([
                'success' => true,
                'data'    => $project,
            ]);
        } catch (\Throwable $e) {
            Log::error('Project show error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch project.',
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Projects $project): JsonResponse
    {
        try {
            if ($project->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have access to this project.',
                ], 403);
            }

            $data = $request->validate([
                'name'       => ['sometimes', 'string', 'max:255'],
                'start_date' => ['sometimes', 'date'],
                'end_date'   => ['sometimes', 'date'],
            ]);

            $project->update($data);

            return response()->json([
                'success' => true,
                'data'    => $project->fresh('tasks', 'user'),
                'message' => 'Project updated successfully.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Project update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update project.',
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Projects $project): JsonResponse
    {
        try {
            if ($project->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have access to this project.',
                ], 403);
            }

            $project->delete();

            return response()->json([
                'success' => true,
                'message' => 'Project deleted successfully.',
            ]);
        } catch (\Throwable $e) {
            Log::error('Project destroy error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete project.',
            ], 500);
        }
    }
}
