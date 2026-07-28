<?php

declare(strict_types=1);

namespace App\Modules\Events\Http\Controllers;

use App\Models\Event;
use App\Modules\Events\Actions\MarkAttendanceAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class EventAttendanceController
{
    public function store(Request $request, Event $event, MarkAttendanceAction $action): JsonResponse
    {
        $this->authorizeTenantAccess($request, $event);

        $validated = $request->validate([
            'person_id' => [
                'required',
                'integer',
                Rule::exists('persons', 'id')->where(fn ($query) => $query->where('tenant_id', $request->user()->tenant_id)),
            ],
            'checked_in_at' => ['nullable', 'date'],
            'checked_out_at' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'max:32'],
        ]);

        return response()->json($action->execute($event, (int) $request->user()->tenant_id, $validated), 201);
    }

    private function authorizeTenantAccess(Request $request, Event $event): void
    {
        abort_unless((int) $event->tenant_id === (int) $request->user()->tenant_id, 404);
    }
}
