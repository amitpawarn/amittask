<?php

namespace Tests\Feature;

use App\Models\Projects;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_returns_only_authenticated_users_tasks_with_filters(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $project = Projects::factory()->create(['user_id' => $user->id]);
        $otherProject = Projects::factory()->create(['user_id' => $otherUser->id]);

        Task::factory()->create([
            'user_id'      => $user->id,
            'project_id'   => $project->id,
            'project_name' => $project->name,
            'status'       => 'complete',
            'priority'     => 'high',
        ]);

        Task::factory()->create([
            'user_id'      => $otherUser->id,
            'project_id'   => $otherProject->id,
            'project_name' => $otherProject->name,
            'status'       => 'rework',
            'priority'     => 'low',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/tasks?status=complete&priority=high');

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonCount(1, 'data.data');
    }

    #[Test]
    public function it_creates_task_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        $project = Projects::factory()->create(['user_id' => $user->id]);

        $payload = [
            'task_name'  => 'My Task',
            'project_id' => $project->id,
            'status'     => 'complete',
            'priority'   => 'medium',
            'start_date' => now()->toDateTimeString(),
            'end_date'   => now()->addHour()->toDateTimeString(),
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/tasks', $payload);

        $response->assertCreated()
            ->assertJson([
                'success' => true,
                'data'    => [
                    'task_name' => 'My Task',
                ],
            ]);

        $this->assertDatabaseHas('task', [
            'task_name' => 'My Task',
            'user_id'   => $user->id,
        ]);
    }
}

