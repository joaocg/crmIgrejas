<?php

declare(strict_types=1);

namespace App\Modules\Communications\Http\Controllers;

use App\Modules\Communications\Actions\GetWhatsAppIntegrationAction;
use App\Modules\Communications\Actions\SaveWhatsAppIntegrationAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class WhatsAppIntegrationController
{
    public function show(Request $request, GetWhatsAppIntegrationAction $action): JsonResponse
    {
        return response()->json($action->execute((int) $request->user()->tenant_id));
    }

    public function update(Request $request, SaveWhatsAppIntegrationAction $action): JsonResponse
    {
        $validated = $request->validate([
            'provider' => ['required', 'string', 'in:waha,meta'],
            'enabled' => ['nullable', 'boolean'],
            'settings' => ['nullable', 'array'],
        ]);

        return response()->json($action->execute((int) $request->user()->tenant_id, $validated));
    }
}
