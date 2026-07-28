<?php

declare(strict_types=1);

namespace App\Modules\Kiosk\Http\Controllers;

use App\Models\Event;
use App\Models\Family;
use App\Models\Group;
use App\Models\Person;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class KioskController
{
    public function index(Request $request): JsonResponse
    {
        $tenantId = (int) $request->user()->tenant_id;

        return response()->json([
            'module' => 'kiosk',
            'tenant_id' => $tenantId,
            'summary' => [
                'people' => Person::query()->where('tenant_id', $tenantId)->count(),
                'families' => Family::query()->where('tenant_id', $tenantId)->count(),
                'groups' => Group::query()->where('tenant_id', $tenantId)->count(),
                'events' => Event::query()->where('tenant_id', $tenantId)->count(),
            ],
        ]);
    }
}
