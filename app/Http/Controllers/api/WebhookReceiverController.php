<?php

declare(strict_types=1);

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class WebhookReceiverController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $randomNumber = rand(0, 1);

        if ($randomNumber === 0) {
            throw new RuntimeException('Error');
        }

        return response()->json(['ok' => true], 200);
    }
}
