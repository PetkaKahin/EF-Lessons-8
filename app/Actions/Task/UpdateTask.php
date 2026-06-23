<?php

declare(strict_types=1);

namespace App\Actions\Task;

use App\Enums\TaskStatus;
use App\Events\Task\TaskCompleted;
use App\Events\Task\TaskStatusChanged;
use App\Jobs\Task\SendTaskCompletedNotification;
use App\Models\Task;
use Illuminate\Support\Facades\DB;
use Throwable;

final class UpdateTask
{
    /**
     * @param array<string, mixed> $data
     * @throws Throwable
     */
    public function handle(Task $task, array $data): Task
    {
        $oldStatus = $task->status;

        DB::transaction(function () use ($task, $data, $oldStatus) {
            $task->update($data);

            $newStatus = $task->status;

            if ($oldStatus !== $newStatus) {
                TaskStatusChanged::dispatch($task);

                if ($newStatus === TaskStatus::Done) {
                    TaskCompleted::dispatch($task);
                    SendTaskCompletedNotification::dispatch($task)->afterCommit();
                }
            }
        });

        return $task;
    }
}
