<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Проект №'.fake()->randomNumber(),
            'owner_id' => User::factory(),
        ];
    }

    public function withTasks(int $count = 10): static
    {
        return $this->afterCreating(function (Project $project) use ($count) {
            Task::factory($count)->for($project)->create();
        });
    }
}
