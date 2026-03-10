<?php

namespace Database\Factories;

use App\Models\Projects;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories.Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $project = Projects::factory()->create();

        $start = $this->faker->dateTimeBetween('-1 week', 'now');
        $end = $this->faker->dateTimeBetween($start, '+1 week');

        return [
            'user_id'      => $project->user_id,
            'task_name'    => $this->faker->sentence(4),
            'project_id'   => $project->id,
            'project_name' => $project->name,
            'status'       => $this->faker->randomElement(['pending','on-going','testing','done','complete', 'rework']),
            'priority'     => $this->faker->randomElement(['high', 'medium', 'low']),
            'start_date'   => $start,
            'end_date'     => $end,
        ];
    }
}

