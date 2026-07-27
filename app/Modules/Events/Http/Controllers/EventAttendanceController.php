<?php

declare(strict_types=1);

namespace App\Modules\Events\Http\Controllers;

use App\Models\Event;
use App\Modules\Events\Actions\MarkAttendanceAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class EventAttendanceController
{
    public function store(Request $request, Event $event, MarkAttendanceAction $action): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id' => ['required', 'integer', 'exists:tenants,id'],
            'person_id' => ['required', 'integer', 'exists:persons,id'],
            'checked_in_at' => ['nullable', 'date'],
            'checked_out_at' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'max:32'],
        ]);

        return response()->json($action->execute($event, $validated), 201);
    }
}
