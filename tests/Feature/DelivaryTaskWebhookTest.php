<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\HttpStatus;
use App\Enums\TaskStatus;
use App\Enums\WebhookStatus;
use App\Jobs\Task\DeliverTaskWebhook;
use App\Models\User;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use RuntimeException;

describe('Task Webhook', function () {
    it('Правильно подписывает запрос HMAC', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $project = createProjectFor($user);
        $task = createTaskFor($project);
        $webhook = createWebhookFor($user, $project);

        Http::fake([
            'receiver.test/*' => Http::response(['ok' => true], 200),
        ]);

        $this->patchJson("api/projects/$project->id/tasks/$task->id", [
            'status' => TaskStatus::Done->value,
        ]);

        Http::assertSent(function (Request $request) use ($webhook) {
            $expectedSignature = 'sha256='.hash_hmac(
                'sha256',
                $request->body(),
                $webhook->secret,
            );

            return $request->url() === $webhook->url
                && $request->hasHeader('X-Webhook-Signature', $expectedSignature);
        });

        Http::assertSentCount(1);
    });

    it('создает лог успешной доставки webhook', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $project = createProjectFor($user);
        $task = createTaskFor($project);
        $webhook = createWebhookFor($user, $project);

        Http::fake([
            'receiver.test/*' => Http::response(['ok' => true], 200),
        ]);

        new DeliverTaskWebhook($task, $webhook, 'test-idempotency-key')->handle();

        $this->assertDatabaseHas('webhook_attempts', [
            'webhook_id' => $webhook->id,
            'status' => WebhookStatus::Success->value,
            'http_code' => HttpStatus::Ok->value,
            'error' => null,
        ]);

        $this->assertDatabaseCount('webhook_attempts', 1);
    });

    it('создает лог неуспешной доставки webhook', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $project = createProjectFor($user);
        $task = createTaskFor($project);
        $webhook = createWebhookFor($user, $project);

        Http::fake([
            'receiver.test/*' => Http::response(['error' => true], 500),
        ]);

        expect(fn () => new DeliverTaskWebhook(
            $task,
            $webhook,
            'test-idempotency-key',
        )->handle())->toThrow(RuntimeException::class);

        $this->assertDatabaseHas('webhook_attempts', [
            'webhook_id' => $webhook->id,
            'status' => WebhookStatus::Failed->value,
            'http_code' => HttpStatus::InternalServerError->value,
        ]);
    });
});
