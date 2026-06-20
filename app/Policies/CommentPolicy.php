<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\Project;
use App\Models\User;

class CommentPolicy
{
    public function viewAny(User $user, Project $project): bool
    {
        return $this->accessOnlyProjectMembersAndOwner($user, $project);
    }

    public function view(User $user, Project $project): bool
    {
        return $this->accessOnlyProjectMembersAndOwner($user, $project);
    }

    public function create(User $user, Project $project): bool
    {
        return $this->accessOnlyProjectMembersAndOwner($user, $project);
    }

    public function update(User $user, Project $project, Comment $comment): bool
    {
        return $this->accessOnlyMemberAndOwner($user, $project, $comment);
    }

    public function delete(User $user, Project $project, Comment $comment): bool
    {
        return $this->accessOnlyProjectOwner($user, $project);
    }

    public function accessOnlyProjectMembersAndOwner(User $user, Project $project): bool
    {
        if ($project->members()->where('user_id', $user->id)->exists()) {
            return true;
        }

        return $this->accessOnlyProjectOwner($user, $project);
    }

    public function accessOnlyMemberAndOwner(User $user, Project $project, Comment $comment): bool
    {
        if ($this->accessOnlyProjectOwner($user, $project)) {
            return true;
        }

        return $this->accessOnlyProjectMembersAndOwner($user, $project)
            && $user->id === $comment->user_id;
    }

    public function accessOnlyProjectOwner(User $user, Project $project): bool
    {
        return $user->id === $project->owner_id;
    }
}
