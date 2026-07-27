<?php

declare(strict_types=1);

use App\Modules\Groups\Http\Controllers\GroupController;
use App\Modules\Groups\Http\Controllers\GroupMembershipController;
use Illuminate\Support\Facades\Route;

Route::prefix('api')->middleware('api')->group(function (): void {
    Route::middleware('auth:sanctum')->group(function (): void {
        Route::apiResource('groups', GroupController::class);
        Route::post('groups/{group}/members', [GroupMembershipController::class, 'store']);
        Route::delete('groups/{group}/members/{person}', [GroupMembershipController::class, 'destroy']);
    });
});
