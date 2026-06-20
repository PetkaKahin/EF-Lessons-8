<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\TaskStatus;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

describe('Задачи', function () {
    it('Показывает задачи проекта с пагинацией', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $project = createProjectFor($user);

        for ($i = 1; $i <= 11; $i++) {
            createTaskFor($project, [
                'title' => 'Задача ' . $i,
            ]);
        }

        $response = $this->getJson('/api/projects/' . $project->id . '/tasks');

        $response
            ->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.total', 11);
    });

    it('Фильтрует задачи по статусу', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $project = createProjectFor($user);
        createTaskFor($project, [
            'title' => 'Новая задача',
            'status' => TaskStatus::New->value,
        ]);
        createTaskFor($project, [
            'title' => 'Готовая задача',
            'status' => TaskStatus::Done->value,
        ]);

        $response = $this->getJson('/api/projects/' . $project->id . '/tasks?status=' . TaskStatus::Done->value);

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['title' => 'Готовая задача'])
            ->assertJsonMissing(['title' => 'Новая задача']);
    });

    it('Создает задачу в проекте', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $project = createProjectFor($user);

        $response = $this->postJson('/api/projects/' . $project->id . '/tasks', taskPayload([
            'title' => 'Созданная задача',
        ]));

        $response
            ->assertCreated()
            ->assertJsonPath('data.title', 'Созданная задача')
            ->assertJsonPath('data.project_id', $project->id);

        $this->assertDatabaseHas('tasks', [
            'title' => 'Созданная задача',
            'project_id' => $project->id,
        ]);
    });

    it('Не создает задачу без обязательных полей', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $project = createProjectFor($user);

        $response = $this->postJson('/api/projects/' . $project->id . '/tasks', []);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'title',
                'description',
                'status',
                'priority',
                'due_date',
            ]);
    });

    it('Не создает задачу с неправильными значениями', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $project = createProjectFor($user);

        $response = $this->postJson('/api/projects/' . $project->id . '/tasks', taskPayload([
            'status' => 'wrong_status',
            'priority' => 'wrong_priority',
            'due_date' => 'not-a-date',
        ]));

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'status',
                'priority',
                'due_date',
            ]);
    });

    it('Обновляет задачу', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $project = createProjectFor($user);
        $task = createTaskFor($project, [
            'title' => 'Старое название',
        ]);

        $response = $this->patchJson('/api/projects/' . $project->id . '/tasks/' . $task->id, [
            'title' => 'Новое название',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.title', 'Новое название');

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Новое название',
        ]);
    });
});
