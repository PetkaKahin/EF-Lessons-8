<?php

declare(strict_types=1);

use App\Enums\TaskStatus;
use App\Jobs\Task\SendTaskCompletedNotification;
use App\Models\User;
use Illuminate\Log\LogManager;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\patchJson;

$notificationPatch = 'var/notifications.log';

describe('Проверка jobs', function () use ($notificationPatch) {
    it('После смены статуса job попадает в очередь', function () {
        Queue::fake();

        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $project = createProjectFor($user);
        $task = createTaskFor($project);

        $response = patchJson('api/projects/'.$project->id.'/tasks/'.$task->id, [
            'status' => TaskStatus::Done,
        ]);

        $response->assertSuccessful();
        Queue::assertPushed(SendTaskCompletedNotification::class);
        Queue::assertCount(1);
    });

    it('После провалов job попадает в failed_jobs', function () {
        config(['queue.default' => 'database']);

        $channel = Mockery::mock();
        $channel->shouldReceive('info')->andThrow(new RuntimeException('Notification failed'));
        $channel->shouldReceive('error')->andReturnNull();

        $logManager = Mockery::mock(LogManager::class, [app()])->makePartial();
        $logManager->shouldReceive('channel')->with('notifications')->andReturn($channel);
        Log::swap($logManager);

        $user = User::factory()->create();
        $project = createProjectFor($user);
        $task = createTaskFor($project);

        SendTaskCompletedNotification::dispatch($task);

        for ($i = 0; $i < 3; $i++) {
            Artisan::call('queue:work', ['--once' => true]);

            DB::table('jobs')->update([
                'available_at' => now()->subSecond()->timestamp,
            ]);
        }

        $this->assertDatabaseCount('jobs', 0);
        $this->assertDatabaseCount('failed_jobs', 1);
        $this->assertDatabaseCount('idempotency_keys', 0);

        $payload = json_decode(DB::table('failed_jobs')->value('payload'), true);
        expect($payload['displayName'])->toBe(SendTaskCompletedNotification::class);
    });

    it('При повторном выполнении job нет дубликатов в логах', function () use ($notificationPatch) {
        File::delete($notificationPatch);

        $user = User::factory()->create();
        $project = createProjectFor($user);
        $task = createTaskFor($project);

        SendTaskCompletedNotification::dispatch($task);
        SendTaskCompletedNotification::dispatch($task);

        $linesNotification = file($notificationPatch, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        $this->assertDatabaseCount('idempotency_keys', 1);
        $this->assertCount(1, $linesNotification);
    });
});
