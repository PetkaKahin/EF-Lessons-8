<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

function createProjectFor(User $user, string $name = 'Тестовый проект'): Project
{
    return $user->ownedProjects()->create([
        'name' => $name,
    ]);
}

/**
 * @param array<string, mixed> $overrides
 * @return array<string, mixed>
 */
function taskPayload(array $overrides = []): array
{
    return [
        ...[
            'title' => 'Тестовая задача',
            'description' => 'Описание тестовой задачи',
            'status' => TaskStatus::New->value,
            'priority' => TaskPriority::Normal->value,
            'due_date' => now()->addDay()->toDateString(),
        ],
        ...$overrides,
    ];
}

/**
 * @param array<string, mixed> $overrides
 */
function createTaskFor(Project $project, array $overrides = []): Task
{
    return $project->tasks()->create(taskPayload($overrides));
}


/**
 * @param array<string, mixed> $overrides
 */
function createCommentFor(Task $task, User $user, array $overrides = []): Comment
{
    return $task->comments()->create([
        ...[
            'body' => 'Тестовый комментарий',
            'user_id' => $user->id,
        ],
        ...$overrides,
    ]);
}
