<?php

namespace App\Jobs\Task;

use App\Enums\WebhookStatus;
use App\Helpers\Timer;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Models\Webhook;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class DeliverTaskWebhook implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        private readonly Task $task,
        private readonly Webhook $webhook,
        private readonly string $idempotencyKey,
    ) {}

    public function backoff(): array
    {
        return [5, 30, 90];
    }

    /**
     * @throws Throwable
     */
    public function handle(): void
    {
        $startTime = microtime(true);

        try {
            $payload = TaskResource::make($this->task)->resolve();
            $body = json_encode($payload, JSON_THROW_ON_ERROR);

            $hmac = hash_hmac('sha256', $body, $this->webhook->secret);

            $response = Http::withHeaders([
                'Idempotency-Key' => $this->idempotencyKey,
                'X-Webhook-Signature' => "sha256=$hmac",
                'Content-Type' => 'application/json',
            ])->withBody(
                $body
            )->post(
                $this->webhook->url,
            );

            if ($response->failed()) {
                $errorMessage = 'Webhook failed with status '.$response->status();

                throw new RuntimeException($errorMessage, $response->status());
            }

            $this->webhook->webhookAttempts()->create([
                'status' => WebhookStatus::Success,
                'http_code' => $response->status(),
                'response_time' => Timer::getTimeForMs($startTime),
                'error' => null,
                'occurred_at' => now(),
            ]);
        } catch (Throwable $exception) {
            $this->webhook->webhookAttempts()->create([
                'status' => WebhookStatus::Failed,
                'http_code' => $exception->getCode() > 0 ? $exception->getCode() : null,
                'response_time' => Timer::getTimeForMs($startTime),
                'error' => $exception->getMessage(),
                'occurred_at' => now(),
            ]);

            throw $exception;
        }
    }

    public function uniqueId(): string
    {
        return $this->idempotencyKey;
    }

    public function failed(Throwable $error): void
    {
        Log::channel('errorlog')->error('Webhook task failed', [
            'task_id' => $this->task->id,
            'webhook_id' => $this->webhook->id,
            'error_message' => $error->getMessage(),
        ]);
    }
}
