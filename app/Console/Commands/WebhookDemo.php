<?php

namespace App\Console\Commands;

use App\Actions\Task\UpdateTask;
use App\Enums\TaskStatus;
use App\Models\User;
use Illuminate\Console\Command;

class WebhookDemo extends Command
{
    /**
     * @var string
     */
    protected $signature = 'webhook:demo';

    /**
     * @var string
     */
    protected $description = 'Command description';

    public function handle(): int
    {
        $user = User::query()->first();

        if (! $user) {
            return $this->notRunSeed();
        }

        $project = $user->ownedProjects()->first();

        if (! $project) {
            return $this->notRunSeed();
        }

        $task = $project->tasks()->first();

        if (! $task) {
            return $this->notRunSeed();
        }

        $webhook = $project->webhook()->first();

        if (! $webhook) {
            $webhook = $project->webhook()->make([
                'url' => 'http://webhook-receiver:8081/api/webhook/simulate',
                'secret' => 'test-secret',
                'enabled' => true,
            ]);
            $webhook->owner()->associate($user);
            $webhook->save();
        }

        if (! $webhook->enabled) {
            $webhook->update(['enabled' => true]);
        }

        if ($task->status === TaskStatus::Done) {
            new UpdateTask()->handle($task, [
                'status' => TaskStatus::InProgress,
            ]);
        } else {
            new UpdateTask()->handle($task, [
                'status' => TaskStatus::Done,
            ]);
        }

        $this->info('Dispatch вызван');
        $this->info('Можно смотреть таблицу webhook_attempts');

        return self::SUCCESS;
    }

    public function notRunSeed()
    {
        $this->warn('Сначала выполни: make seed');

        return self::FAILURE;
    }
}
