<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Enums\AuditLogActions;
use App\Events\Task\TaskCompleted;
use App\Models\AuditLog;

final class RecordTaskCompletedAuditLog
{
    public function handle(TaskCompleted $event): void
    {
        AuditLog::create([
            'entity_type' => $event->task::class,
            'entity_id' => $event->task->id,
            'action' => AuditLogActions::Completed->value,
            'meta' => $event->task,
            'occurred_at' => now(),
        ]);
    }
}
