<?php

declare(strict_types=1);

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Comment\StoreCommentRequest;
use App\Http\Requests\Comment\UpdateCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class CommentController extends Controller
{
    public function index(Project $project, Task $task): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Comment::class);

        $comments = $task->comments()->paginate(10);

        return CommentResource::collection($comments);
    }

    public function show(Project $project, Task $task, Comment $comment): CommentResource
    {
        $this->authorize('view', $comment);

        return CommentResource::make($comment);
    }

    public function store(StoreCommentRequest $request, Project $project, Task $task): CommentResource
    {
        $comment = $task->comments()->create([
            ...$request->validated(),
            'user_id' => $request->user()->id,
        ]);

        return CommentResource::make($comment);
    }

    public function update(UpdateCommentRequest $request, Project $project, Task $task, Comment $comment): CommentResource
    {
        $comment->update($request->validated());

        return CommentResource::make($comment);
    }

    public function destroy(Project $project, Task $task, Comment $comment): Response
    {
        $this->authorize('delete', $comment);

        $comment->delete();

        return response()->noContent();
    }
}
