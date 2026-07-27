<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::prefix('api')->middleware('api')->group(function (): void {
    Route::middleware('auth:sanctum')->get('kiosk', static fn () => response()->json([
        'module' => 'kiosk',
    ]));
});
