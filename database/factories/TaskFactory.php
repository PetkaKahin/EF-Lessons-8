<?php

namespace Database\Factories;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Comment;
use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => 'Задача №'.fake()->randomNumber(),
            'description' => $this->faker->paragraph(),
            'status' => TaskStatus::cases()[array_rand(TaskStatus::cases())],
            'priority' => TaskPriority::cases()[array_rand(TaskPriority::cases())],
            'due_date' => $this->faker->date(),
            'project_id' => 1,
        ];
    }

    public function withComments(int $count = 5): static
    {
        return $this->afterCreating(function (Task $task) use ($count) {
            Comment::factory($count)->for($task)->create();
        });
    }
}
