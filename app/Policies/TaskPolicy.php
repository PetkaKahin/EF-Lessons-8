<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user, Project $project): bool
    {
        return $this->accessOnlyProjectOwnerAndMembers($user, $project);
    }

    public function view(User $user, Project $project): bool
    {
        return $this->accessOnlyProjectOwnerAndMembers($user, $project);
    }

    public function create(User $user, Project $project): bool
    {
        return $this->accessOnlyProjectOwner($user, $project);
    }

    public function update(User $user, Project $project): bool
    {
        return $this->accessOnlyProjectOwner($user, $project);
    }

    public function delete(User $user, Project $project): bool
    {
        return $this->accessOnlyProjectOwner($user, $project);
    }

    private function accessOnlyProjectOwner(User $user, Project $project): bool
    {
        return $user->id === $project->owner_id;
    }

    private function accessOnlyProjectOwnerAndMembers(User $user, Project $project): bool
    {
        if ($this->accessOnlyProjectOwner($user, $project)) {
            return true;
        }

        return $project->members()->where('user_id', $user->id)->exists();
    }
}
