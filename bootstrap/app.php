<?php

use App\Http\Middleware\RecordMetricsMiddleware;
use App\Http\Middleware\RequestIdMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append([
            RequestIdMiddleware::class,
        ]);
        $middleware->append([
            RecordMetricsMiddleware::class,
        ]);

        $middleware->alias([
            'metrics' => RecordMetricsMiddleware::class,
            'requestId' => RequestIdMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
