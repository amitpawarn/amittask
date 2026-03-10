<?php

namespace Database\Factories;

use App\Models\Projects;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories.Factory<\App\Models\Projects>
 */
class ProjectsFactory extends Factory
{
    protected $model = Projects::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'    => User::factory(),
            'name'       => $this->faker->sentence(3),
            'start_date' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'end_date'   => $this->faker->dateTimeBetween('now', '+1 week'),
        ];
    }
}

