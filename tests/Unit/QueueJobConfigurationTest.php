<?php

declare(strict_types=1);

use App\Jobs\Task\DeliverTaskWebhook;
use App\Jobs\Task\SendTaskCompletedNotification;
use App\Models\Task;
use App\Models\Webhook;

it('настраивает ретраи и ключ идемпотентности уведомления о завершенной задаче', function () {
    $task = new Task;
    $task->timestamps = false;
    $task->setRawAttributes([
        'id' => 15,
        'updated_at' => '2026-06-25 10:00:00',
    ]);

    $job = new SendTaskCompletedNotification($task);

    expect($job->tries)->toBe(3)
        ->and($job->backoff())->toBe([5, 30, 180])
        ->and($job->uniqueId())->toContain('task_completed:15')
        ->and($job->uniqueId())->toContain('2026-06-25 10:00:00');
});

it('настраивает ретраи и ключ идемпотентности доставки webhook', function () {
    $job = new DeliverTaskWebhook(new Task, new Webhook, 'webhook-key');

    expect($job->tries)->toBe(3)
        ->and($job->backoff())->toBe([5, 30, 90])
        ->and($job->uniqueId())->toBe('webhook-key');
});
