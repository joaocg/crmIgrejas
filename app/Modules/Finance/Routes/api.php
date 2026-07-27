<?php

declare(strict_types=1);

use App\Modules\Finance\Http\Controllers\DepositController;
use App\Modules\Finance\Http\Controllers\DonationFundController;
use App\Modules\Finance\Http\Controllers\PledgeController;
use Illuminate\Support\Facades\Route;

Route::prefix('api')->middleware('api')->group(function (): void {
    Route::middleware('auth:sanctum')->group(function (): void {
        Route::apiResource('donation-funds', DonationFundController::class);
        Route::apiResource('deposits', DepositController::class);
        Route::apiResource('pledges', PledgeController::class);
    });
});
