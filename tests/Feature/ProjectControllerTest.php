<?php

namespace Tests\Feature;

use App\Models\Projects;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProjectControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_returns_only_authenticated_users_projects(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Projects::factory()->count(2)->create(['user_id' => $user->id]);
        Projects::factory()->count(3)->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/projects');

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonCount(2, 'data.data');
    }

    #[Test]
    public function it_creates_project_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $payload = [
            'name'       => 'Test Project',
            'start_date' => now()->toDateString(),
            'end_date'   => now()->addDay()->toDateString(),
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/projects', $payload);

        $response->assertCreated()
            ->assertJson([
                'success' => true,
                'data'    => [
                    'name' => 'Test Project',
                ],
            ]);

        $this->assertDatabaseHas('projects', [
            'name'    => 'Test Project',
            'user_id' => $user->id,
        ]);
    }
}

