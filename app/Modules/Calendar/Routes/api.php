<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::prefix('api')->middleware('api')->group(function (): void {
    Route::middleware('auth:sanctum')->get('calendar', static fn () => response()->json([
        'module' => 'calendar',
    ]));
});
