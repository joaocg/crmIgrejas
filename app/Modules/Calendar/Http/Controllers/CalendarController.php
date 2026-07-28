<?php

declare(strict_types=1);

namespace App\Modules\Calendar\Http\Controllers;

use App\Modules\Calendar\Actions\ListCalendarEventsAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CalendarController
{
    public function index(Request $request, ListCalendarEventsAction $action): JsonResponse
    {
        return response()->json([
            'module' => 'calendar',
            'events' => $action->execute((int) $request->user()->tenant_id),
        ]);
    }
}
