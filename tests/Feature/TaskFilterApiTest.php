<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\TaskPriority;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

describe('Фильтры для задач', function () {
    it('фильтрует задачи по приоритету, диапазону даты и поиску', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $project = createProjectFor($user);
        createTaskFor($project, [
            'title' => 'Payment release',
            'priority' => TaskPriority::High->value,
            'due_date' => '2026-07-10',
        ]);
        createTaskFor($project, [
            'title' => 'Payment draft',
            'priority' => TaskPriority::Low->value,
            'due_date' => '2026-07-10',
        ]);
        createTaskFor($project, [
            'title' => 'Payment archive',
            'priority' => TaskPriority::High->value,
            'due_date' => '2026-08-10',
        ]);

        $response = $this->getJson(
            '/api/projects/'.$project->id.'/tasks?priority='.TaskPriority::High->value
            .'&due_date_from=2026-07-01&due_date_to=2026-07-31&search=Payment'
        );

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['title' => 'Payment release'])
            ->assertJsonMissing(['title' => 'Payment draft'])
            ->assertJsonMissing(['title' => 'Payment archive']);
    });
});
