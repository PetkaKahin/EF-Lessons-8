<?php

use App\Models\RequestMetric;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

Route::get('/ready', function () {
    try {
        DB::connection()->getPdo();
        Redis::connection()->ping();

        return response()->json(['status' => 'ok']);
    } catch (Throwable) {
        return response()->json(['status' => 'error'], 503);
    }
});

Route::get('/metrics', static function () {
    return view('metrics', [
        'requestsTotal' => RequestMetric::query()->count(),
        'responseTimeSum' => (float) RequestMetric::query()->sum('response_time_ms'),
        'responseTimeAvg' => (float) RequestMetric::query()->avg('response_time_ms'),
        'responseTimeMax' => (float) RequestMetric::query()->max('response_time_ms'),
        'recentRequests' => RequestMetric::query()
            ->latest()
            ->limit(10)
            ->get(),
    ]);
});
