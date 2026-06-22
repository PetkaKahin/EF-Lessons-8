<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Enums\AuditLogActions;
use App\Events\Task\TaskCreated;
use App\Models\AuditLog;
use Illuminate\Contracts\Queue\ShouldQueue;

final class RecordTaskCreatedAuditLog implements ShouldQueue
{
    public function handle(TaskCreated $event): void
    {
        AuditLog::create([
            'entity_type' => $event->task::class,
            'entity_id' => $event->task->id,
            'action' => AuditLogActions::Created->value,
            'meta' => $event->task,
            'occurred_at' => now(),
        ]);
    }
}
