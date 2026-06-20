<?php

declare(strict_types=1);

use App\Http\Controllers\api\CommentController;
use App\Http\Controllers\api\ProjectController;
use App\Http\Controllers\api\TaskController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('projects', ProjectController::class);
    Route::apiResource('projects.tasks', TaskController::class)->scoped();
    Route::apiResource('projects.tasks.comments', CommentController::class)->scoped();
});
