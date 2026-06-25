<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\webhook\StoreWebhookRequest;
use App\Http\Requests\webhook\UpdateWebhookRequest;
use App\Http\Resources\WebhookResource;
use App\Models\Project;
use App\Models\Webhook;

class WebhookController extends Controller
{
    public function index(Project $project)
    {
        $this->authorize('viewAny', [Webhook::class, $project]);

        return WebhookResource::collection($project->webhooks()->paginate(10));
    }

    public function show(Project $project, Webhook $webhook)
    {
        $this->authorize('view', $webhook);

        return WebhookResource::make($webhook);
    }

    public function store(StoreWebhookRequest $request, Project $project)
    {
        $webhook = $project->webhook()->make($request->validated());
        $webhook->owner()->associate($request->user());
        $webhook->save();

        return WebhookResource::make($webhook);
    }

    public function update(UpdateWebhookRequest $request, Project $project, Webhook $webhook)
    {
        $webhook->update($request->validated());

        return WebhookResource::make($webhook);
    }

    public function destroy(Project $project, Webhook $webhook)
    {
        $this->authorize('delete', $webhook);

        $webhook->delete();

        return response()->noContent();
    }
}
