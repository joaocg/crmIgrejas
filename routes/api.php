<?php

use App\Http\Controllers\Api\AuthTokenController;
use App\Http\Controllers\Api\NavigationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [AuthTokenController::class, 'store']);
Route::middleware('auth:sanctum')->post('/auth/logout', [AuthTokenController::class, 'destroy']);
Route::middleware('auth:sanctum')->get('/navigation', NavigationController::class);

// Feature modules register their own routes through the manifest-driven module loader.
Route::middleware('auth:sanctum')->get('/me', function (Request $request) {
    return $request->user();
});
