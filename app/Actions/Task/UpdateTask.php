<?php

declare(strict_types=1);

namespace App\Actions\Task;

use App\Enums\TaskStatus;
use App\Events\Task\TaskCompleted;
use App\Events\Task\TaskStatusChanged;
use App\Jobs\Task\DeliverTaskWebhook;
use App\Jobs\Task\SendTaskCompletedNotification;
use App\Models\Project;
use App\Models\Task;
use App\Models\Webhook;
use Illuminate\Support\Facades\DB;
use Throwable;

final class UpdateTask
{
    /**
     * @param  array<string, mixed>  $data
     *
     * @throws Throwable
     */
    public function handle(Task $task, array $data): Task
    {
        $oldStatus = $task->status;

        DB::transaction(function () use ($task, $data, $oldStatus) {
            $task->update($data);

            $newStatus = $task->status;

            if ($oldStatus !== $newStatus) {
                $webhook = Webhook::query()
                    ->where('webhookable_type', Project::class)
                    ->where('webhookable_id', $task->project_id)
                    ->where('enabled', true)
                    ->first();

                TaskStatusChanged::dispatch($task);
                $this->dispatchDeliverTaskWebhook($task, $webhook);

                if ($newStatus === TaskStatus::Done) {
                    TaskCompleted::dispatch($task);
                    SendTaskCompletedNotification::dispatch($task)->afterCommit();
                }
            }
        });

        return $task;
    }

    private function dispatchDeliverTaskWebhook(Task $task, ?Webhook $webhook): void
    {
        if (! $webhook) {
            return;
        }

        DeliverTaskWebhook::dispatch(
            $task,
            $webhook,
            "webhook:{$webhook->id} task:{$task->id} time:{$task->updated_at}",
        )->afterCommit();
    }
}
