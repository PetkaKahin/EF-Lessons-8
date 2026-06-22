<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Enums\AuditLogActions;
use App\Events\Task\TaskStatusChanged;
use App\Models\AuditLog;
use Illuminate\Contracts\Queue\ShouldQueue;

class RecordTaskStatusChangedAuditLog implements ShouldQueue
{
    public function handle(TaskStatusChanged $event): void
    {
        AuditLog::create([
            'entity_type' => $event->task::class,
            'entity_id' => $event->task->id,
            'action' => AuditLogActions::StatusChanged->value,
            'meta' => $event->task,
            'occurred_at' => now(),
        ]);
    }
}
