<?php

declare(strict_types=1);

use App\Modules\Kiosk\Http\Controllers\KioskController;
use Illuminate\Support\Facades\Route;

Route::prefix('api')->middleware('api')->group(function (): void {
    Route::middleware('auth:sanctum')->get('kiosk', [KioskController::class, 'index']);
});
