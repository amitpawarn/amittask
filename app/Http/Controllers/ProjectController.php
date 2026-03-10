<?php

namespace App\Http\Controllers;

use App\Models\Projects;
use App\Services\ProjectService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ProjectController extends Controller
{
    public function __construct(private readonly ProjectService $projectService)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $projects = $this->projectService->listForUser(auth()->id(), 10);

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

            $project = $this->projectService->createForUser(auth()->id(), $data);

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
        } catch (AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 403);
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
            $project = $this->projectService->getForUser(auth()->id(), $project);

            return response()->json([
                'success' => true,
                'data'    => $project,
            ]);
        } catch (AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 403);
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
            $data = $request->validate([
                'name'       => ['sometimes', 'string', 'max:255'],
                'start_date' => ['sometimes', 'date'],
                'end_date'   => ['sometimes', 'date'],
            ]);

            $project = $this->projectService->updateForUser(auth()->id(), $project, $data);

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
        } catch (AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 403);
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
            $this->projectService->deleteForUser(auth()->id(), $project);

            return response()->json([
                'success' => true,
                'message' => 'Project deleted successfully.',
            ]);
        } catch (AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 403);
        } catch (\Throwable $e) {
            Log::error('Project destroy error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete project.',
            ], 500);
        }
    }
}
