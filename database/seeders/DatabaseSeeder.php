<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::factory()
            ->has(
                Project::factory()
                    ->count(2)
                    ->has(
                        Task::factory()
                            ->count(10)
                            ->has(
                                Comment::factory()->count(5),
                                'comments'
                            ),
                        'tasks'
                    ),
                'ownedProjects'
            )
            ->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
    }
}
