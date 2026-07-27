<?php

declare(strict_types=1);

use App\Modules\Care\Http\Controllers\NoteController;
use App\Modules\Care\Http\Controllers\PastoralCareController;
use Illuminate\Support\Facades\Route;

Route::prefix('api')->middleware('api')->group(function (): void {
    Route::middleware('auth:sanctum')->group(function (): void {
        Route::apiResource('notes', NoteController::class);
        Route::apiResource('pastoral-care', PastoralCareController::class);
    });
});
