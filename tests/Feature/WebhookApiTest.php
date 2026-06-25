<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\TaskStatus;
use App\Models\User;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;

describe('Webhook API', function () {
    it('не принимает некорректный url webhook', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $project = createProjectFor($user);

        $response = $this->postJson('/api/projects/'.$project->id.'/webhooks', [
            'url' => 'not-a-url',
            'secret' => 'test-secret',
            'enabled' => true,
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['url']);
    });

    it('отправляет webhook с заголовком идемпотентности', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $project = createProjectFor($user);
        $task = createTaskFor($project);
        createWebhookFor($user, $project);

        Http::fake([
            'receiver.test/*' => Http::response(['ok' => true]),
        ]);

        $this->patchJson('/api/projects/'.$project->id.'/tasks/'.$task->id, [
            'status' => TaskStatus::Done->value,
        ])->assertOk();

        Http::assertSent(fn (Request $request): bool => $request->hasHeader('Idempotency-Key'));
    });
});
