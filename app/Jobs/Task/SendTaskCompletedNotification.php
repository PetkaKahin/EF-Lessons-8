<?php

declare(strict_types=1);

namespace App\Jobs\Task;

use App\Models\IdempotencyKey;
use App\Models\Task;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

final class SendTaskCompletedNotification implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 30;

    public function __construct(private readonly Task $task) {}

    public function backoff(): array
    {
        return [5, 30, 180];
    }

    /**
     * @throws Throwable
     */
    public function handle(): void
    {
        DB::transaction(function (): void {
            $affected = IdempotencyKey::query()->insertOrIgnore([
                'key' => $this->uniqueId(),
                'created_at' => now(),
            ]);

            if ($affected === 0) {
                return;
            }

            Log::channel('notifications')->info('Send task completed', [
                'task_id' => $this->task->id,
            ]);
        });
    }

    public function uniqueId(): string
    {
        return "task_completed:{$this->task->id} time:{$this->task->updated_at}";
    }

    public function failed(Throwable $error): void
    {
        Log::channel('notifications')->error('Send task failed', [
            'task_id' => $this->task->id,
            'error_message' => $error->getMessage(),
        ]);
    }
}
