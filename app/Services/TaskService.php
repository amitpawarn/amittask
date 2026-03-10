<?php

namespace App\Services;

use App\Models\Projects;
use App\Models\Task;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class TaskService
{
    public function listForUser(int $userId, array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = Task::with(['project', 'user'])
            ->where('user_id', $userId);

        $this->applyFilters($query, $filters);

        return $query->paginate($perPage);
    }

    public function createForUser(int $userId, array $data): Task
    {
        $project = Projects::query()
            ->where('id', $data['project_id'])
            ->where('user_id', $userId)
            ->first();

        if (!$project) {
            throw new \RuntimeException('Project not found or you do not have access to it.', 404);
        }

        $data['project_name'] = $project->name;
        $data['user_id'] = $userId;

        return Task::create($data);
    }

    public function getForUser(int $userId, Task $task): Task
    {
        $this->authorizeOwnership($userId, $task);

        return $task->load(['project', 'user']);
    }

    public function updateForUser(int $userId, Task $task, array $data): Task
    {
        $this->authorizeOwnership($userId, $task);

        if (isset($data['project_id'])) {
            $project = Projects::query()
                ->where('id', $data['project_id'])
                ->where('user_id', $userId)
                ->first();

            if (!$project) {
                throw new \RuntimeException('Project not found or you do not have access to it.', 404);
            }

            $data['project_name'] = $project->name;
        }

        $task->update($data);

        return $task->fresh(['project', 'user']);
    }

    public function deleteForUser(int $userId, Task $task): void
    {
        $this->authorizeOwnership($userId, $task);

        $task->delete();
    }

    private function authorizeOwnership(int $userId, Task $task): void
    {
        if ($task->user_id !== $userId) {
            throw new AuthorizationException('You do not have access to this task.');
        }
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (!empty($filters['start_from'])) {
            $query->where('start_date', '>=', $filters['start_from']);
        }
        if (!empty($filters['start_to'])) {
            $query->where('start_date', '<=', $filters['start_to']);
        }

        if (!empty($filters['end_from'])) {
            $query->where('end_date', '>=', $filters['end_from']);
        }
        if (!empty($filters['end_to'])) {
            $query->where('end_date', '<=', $filters['end_to']);
        }
    }
}

