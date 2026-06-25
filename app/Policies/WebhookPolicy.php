<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use App\Models\Webhook;

class WebhookPolicy
{
    public function viewAny(User $user, Project $project): bool
    {
        if ($user->id === $project->owner_id) {
            return true;
        }
        if ($project->members()->whereKey($user->id)->exists()) {
            return true;
        }

        return false;
    }

    public function view(User $user, Webhook $webhook): bool
    {
        return $this->webhookBelongsToOwner($user, $webhook);
    }

    public function create(User $user, Project $project): bool
    {
        if ($user->id !== $project->owner_id) {
            return false;
        }

        // может быть только 1
        if ($project->webhook()->exists()) {
            return false;
        }

        return true;
    }

    public function update(User $user, Webhook $webhook): bool
    {
        return $this->webhookBelongsToOwner($user, $webhook);
    }

    public function delete(User $user, Webhook $webhook): bool
    {
        return $this->webhookBelongsToOwner($user, $webhook);
    }

    public function webhookBelongsToOwner(User $user, Webhook $webhook): bool
    {
        return $user->id === $webhook->owner_id;
    }
}
