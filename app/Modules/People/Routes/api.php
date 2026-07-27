<?php

declare(strict_types=1);

use App\Modules\People\Http\Controllers\PersonController;
use Illuminate\Support\Facades\Route;

Route::prefix('api')->middleware('api')->group(function (): void {
    Route::middleware('auth:sanctum')->apiResource('people', PersonController::class);
});
