<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Laravel\Sanctum\Sanctum;

describe('Комментарии', function () {
    it('Показывает комментарии задачи с пагинацией', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $project = createProjectFor($user);
        $task = createTaskFor($project);

        for ($i = 1; $i <= 11; $i++) {
            createCommentFor($task, $user, [
                'body' => 'Комментарий ' . $i,
            ]);
        }

        $response = $this->getJson('/api/projects/' . $project->id . '/tasks/' . $task->id . '/comments');

        $response
            ->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.total', 11);
    });

    it('Создает комментарий от текущего пользователя', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $project = createProjectFor($user);
        $task = createTaskFor($project);

        $response = $this->postJson('/api/projects/' . $project->id . '/tasks/' . $task->id . '/comments', [
            'body' => 'Новый комментарий',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.body', 'Новый комментарий')
            ->assertJsonPath('data.user_id', $user->id);

        $this->assertDatabaseHas('comments', [
            'body' => 'Новый комментарий',
            'task_id' => $task->id,
            'user_id' => $user->id,
        ]);
    });

    it('Не создает комментарий без текста', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $project = createProjectFor($user);
        $task = createTaskFor($project);

        $response = $this->postJson('/api/projects/' . $project->id . '/tasks/' . $task->id . '/comments', []);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['body']);
    });

    it('Не создает комментарий со слишком длинным текстом', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $project = createProjectFor($user);
        $task = createTaskFor($project);

        $response = $this->postJson('/api/projects/' . $project->id . '/tasks/' . $task->id . '/comments', [
            'body' => str_repeat('a', 65001),
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['body']);
    });

    it('Обновляет комментарий', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $project = createProjectFor($user);
        $task = createTaskFor($project);
        $comment = createCommentFor($task, $user, [
            'body' => 'Старый текст',
        ]);

        $response = $this->patchJson('/api/projects/' . $project->id . '/tasks/' . $task->id . '/comments/' . $comment->id, [
            'body' => 'Новый текст',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.body', 'Новый текст');

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'body' => 'Новый текст',
        ]);
    });
});
