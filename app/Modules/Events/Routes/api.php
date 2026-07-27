<?php

declare(strict_types=1);

use App\Modules\Events\Http\Controllers\EventAttendanceController;
use App\Modules\Events\Http\Controllers\EventController;
use Illuminate\Support\Facades\Route;

Route::prefix('api')->middleware('api')->group(function (): void {
    Route::middleware('auth:sanctum')->group(function (): void {
        Route::apiResource('events', EventController::class);
        Route::post('events/{event}/attendance', [EventAttendanceController::class, 'store']);
    });
});
