<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Feature modules register their own routes through the manifest-driven module loader.
Route::middleware('auth:sanctum')->get('/me', function (Request $request) {
    return $request->user();
});
