<?php

use App\Http\Controllers\Api\NavigationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth:sanctum')->get('/navigation', NavigationController::class);

Route::fallback(function () {
    return view('welcome');
});
