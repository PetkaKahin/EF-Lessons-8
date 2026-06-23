<?php

declare(strict_types=1);

namespace App\Http\Controllers\api;

use App\Actions\Task\UpdateTask;
use App\Events\Task\TaskCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\Task\IndexTaskRequest;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Throwable;

class TaskController extends Controller
{
    public function __construct(
        private readonly UpdateTask $updateTask,
    ) {}

    public function index(IndexTaskRequest $request, Project $project): AnonymousResourceCollection
    {
        $this->authorize('viewAny', [Task::class, $project]);

        $tasks = $project->tasks()
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('priority'), fn ($query) => $query->where('priority', $request->priority))
            ->when($request->filled('due_date_from'), fn ($query) => $query->where('due_date', '>', $request->due_date_from))
            ->when($request->filled('due_date_to'), fn ($query) => $query->where('due_date', '<', $request->due_date_to))
            ->when($request->filled('search'), fn ($query) => $query->where('title', 'like', '%'.$request->search.'%'))
            ->paginate(10);

        return TaskResource::collection($tasks);
    }

    public function show(Project $project, Task $task): TaskResource
    {
        $this->authorize('view', [Task::class, $project]);

        return TaskResource::make($task);
    }

    public function store(StoreTaskRequest $request, Project $project): TaskResource
    {
        $task = $project->tasks()->create($request->validated());

        TaskCreated::dispatch($task);

        return TaskResource::make($task);
    }

    /**
     * @throws Throwable
     */
    public function update(UpdateTaskRequest $request, Project $project, Task $task): TaskResource
    {
        $task = $this->updateTask->handle($task, $request->validated());

        return TaskResource::make($task);
    }

    public function destroy(Project $project, Task $task): Response
    {
        $this->authorize('delete', [Task::class, $project]);

        $task->delete();

        return response()->noContent();
    }
}
