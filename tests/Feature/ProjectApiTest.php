<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Laravel\Sanctum\Sanctum;

describe('Проекты', function () {
    it('Нет доступа без регистрации', function () {
        $projects = $this->getJson('/api/projects');

        $projects->assertUnauthorized();
    });

    it('Проверка пагинации', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        for ($i = 0; $i < 11; $i++) {
            createProjectFor($user);
        }
        $projects = $this->getJson('/api/projects');

        $projects
            ->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.total', 11);
    });

    it('Показывает только проекты текущего пользователя', function () {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        Sanctum::actingAs($user);

        createProjectFor($user, 'Мой проект');
        createProjectFor($otherUser, 'Чужой проект');

        $projects = $this->getJson('/api/projects');

        $projects
            ->assertOk()
            ->assertJsonFragment(['name' => 'Мой проект'])
            ->assertJsonMissing(['name' => 'Чужой проект']);
    });

    it('Показывает свой проект', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $project = createProjectFor($user, 'Личный проект');

        $response = $this->getJson('/api/projects/' . $project->id);

        $response
            ->assertOk()
            ->assertJsonPath('data.id', $project->id)
            ->assertJsonPath('data.name', 'Личный проект');
    });

    it('Не показывает чужой проект', function () {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        Sanctum::actingAs($user);

        $project = createProjectFor($otherUser, 'Закрытый проект');

        $response = $this->getJson('/api/projects/' . $project->id);

        $response->assertForbidden();
    });

    it('Создает проект', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/projects', [
            'name' => 'Новый проект',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.name', 'Новый проект')
            ->assertJsonPath('data.owner_id', $user->id);

        $this->assertDatabaseHas('projects', [
            'name' => 'Новый проект',
            'owner_id' => $user->id,
        ]);
    });

    it('Не создает проект без имени', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/projects', []);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    });

    it('Не создает проект со слишком длинным именем', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/projects', [
            'name' => str_repeat('a', 256),
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    });

    it('Обновляет свой проект', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $project = createProjectFor($user, 'Старое имя');

        $response = $this->patchJson('/api/projects/' . $project->id, [
            'name' => 'Новое имя',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.name', 'Новое имя');

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'name' => 'Новое имя',
        ]);
    });

    it('Удаляет свой проект', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $project = createProjectFor($user, 'Проект под удаление');

        $response = $this->deleteJson('/api/projects/' . $project->id);

        $response->assertNoContent();

        $this->assertDatabaseMissing('projects', [
            'id' => $project->id,
        ]);
    });
});




