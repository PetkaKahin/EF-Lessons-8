<?php

declare(strict_types=1);

use App\Http\Controllers\api\CommentController;
use App\Http\Controllers\api\LoginController;
use App\Http\Controllers\api\ProjectController;
use App\Http\Controllers\api\TaskController;
use Illuminate\Support\Facades\Route;

Route::pattern('project', '[0-9]+');
Route::pattern('task', '[0-9]+');
Route::pattern('comment', '[0-9]+');

Route::post('login', LoginController::class);

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('projects', ProjectController::class);
    Route::apiResource('projects.tasks', TaskController::class)->scoped();
    Route::apiResource('projects.tasks.comments', CommentController::class)->scoped();
});
