<?php

declare(strict_types=1);

use App\Modules\Users\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('api')->middleware('api')->group(function (): void {
    Route::middleware('auth:sanctum')->apiResource('users', UserController::class);
});
