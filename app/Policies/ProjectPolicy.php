<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(): bool
    {
        return true;
    }

    public function view(User $user, Project $project): bool
    {
        return $this->accessOnlyOwnerAndMembers($user, $project);
    }

    public function create(): bool
    {
        return true;
    }

    public function update(User $user, Project $project): bool
    {
        return $this->accessOnlyOwner($user, $project);
    }

    public function delete(User $user, Project $project): bool
    {
        return $this->accessOnlyOwner($user, $project);
    }

    private function accessOnlyOwner(User $user, Project $project): bool
    {
        return $user->id === $project->owner_id;
    }

    private function accessOnlyOwnerAndMembers(User $user, Project $project): bool
    {
        if ($this->accessOnlyOwner($user, $project)) {
            return true;
        }

        return $project->members()->where('user_id', $user->id)->exists();
    }
}
