<?php

namespace App\Services;

use App\Models\Projects;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProjectService
{
    public function listForUser(int $userId, int $perPage = 10): LengthAwarePaginator
    {
        return Projects::with(['tasks', 'user'])
            ->where('user_id', $userId)
            ->paginate($perPage);
    }

    public function createForUser(int $userId, array $data): Projects
    {
        $data['user_id'] = $userId;

        return Projects::create($data);
    }

    public function getForUser(int $userId, Projects $project): Projects
    {
        $this->authorizeOwnership($userId, $project);

        return $project->load(['tasks', 'user']);
    }

    public function updateForUser(int $userId, Projects $project, array $data): Projects
    {
        $this->authorizeOwnership($userId, $project);

        $project->update($data);

        return $project->fresh(['tasks', 'user']);
    }

    public function deleteForUser(int $userId, Projects $project): void
    {
        $this->authorizeOwnership($userId, $project);

        $project->delete();
    }

    private function authorizeOwnership(int $userId, Projects $project): void
    {
        if ($project->user_id !== $userId) {
            throw new AuthorizationException('You do not have access to this project.');
        }
    }
}

