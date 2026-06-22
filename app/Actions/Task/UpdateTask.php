<?php

declare(strict_types=1);

namespace App\Actions\Task;

use App\Enums\TaskStatus;
use App\Events\Task\TaskCompleted;
use App\Events\Task\TaskStatusChanged;
use App\Models\Task;

class UpdateTask
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(Task $task, array $data): Task
    {
        $oldStatus = $task->status;

        $task->update($data);

        $newStatus = $task->status;

        if ($oldStatus !== $newStatus) {
            TaskStatusChanged::dispatch($task);

            if ($newStatus === TaskStatus::Done) {
                TaskCompleted::dispatch($task);
            }
        }

        return $task;
    }
}
