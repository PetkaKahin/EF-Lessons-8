<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Laravel\Sanctum\Sanctum;

describe('Доступы к комментариям', function () {
    it('не позволяет не участнику трогать свой старый комментарий', function () {
        $owner = User::factory()->create();
        $author = User::factory()->create();
        Sanctum::actingAs($author);

        $project = createProjectFor($owner);
        $task = createTaskFor($project);
        $comment = createCommentFor($task, $author);

        $this->patchJson('/api/projects/'.$project->id.'/tasks/'.$task->id.'/comments/'.$comment->id, [
            'body' => 'Blocked update',
        ])->assertForbidden();

        $this->deleteJson('/api/projects/'.$project->id.'/tasks/'.$task->id.'/comments/'.$comment->id)
            ->assertForbidden();
    });
});
