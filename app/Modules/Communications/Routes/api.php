<?php

declare(strict_types=1);

use App\Modules\Communications\Http\Controllers\WhatsAppIntegrationController;
use Illuminate\Support\Facades\Route;

Route::prefix('api')->middleware('api')->group(function (): void {
    Route::middleware('auth:sanctum')->get('communications', static fn () => response()->json([
        'module' => 'communications',
    ]));
    Route::middleware('auth:sanctum')->get('communications/whatsapp', [WhatsAppIntegrationController::class, 'show']);
    Route::middleware('auth:sanctum')->put('communications/whatsapp', [WhatsAppIntegrationController::class, 'update']);
});
