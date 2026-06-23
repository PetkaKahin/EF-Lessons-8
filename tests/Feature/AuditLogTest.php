<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\AuditLogActions;
use App\Models\Task;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

describe('Audit Log', function () {
    it('Записывает лог при эвенте', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $project = createProjectFor($user);
        $response = $this->postJson("api/projects/{$project->id}/tasks", taskPayload());
        $response->assertCreated();

        $taskId = $response->json('data.id');

        $this->assertDatabaseHas('audit_logs', [
            'entity_type' => Task::class,
            'entity_id' => $taskId,
            'action' => AuditLogActions::Created->value,
        ]);
    });
});
