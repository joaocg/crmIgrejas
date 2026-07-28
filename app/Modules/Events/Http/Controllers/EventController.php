<?php

declare(strict_types=1);

namespace App\Modules\Events\Http\Controllers;

use App\Models\Event;
use App\Modules\Events\Actions\CreateEventAction;
use App\Modules\Events\Actions\DeleteEventAction;
use App\Modules\Events\Actions\ListEventsAction;
use App\Modules\Events\Actions\UpdateEventAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class EventController
{
    public function index(Request $request, ListEventsAction $action): JsonResponse
    {
        return response()->json($action->execute((int) $request->user()->tenant_id));
    }

    public function store(Request $request, CreateEventAction $action): JsonResponse
    {
        $validated = $request->validate([
            'group_id' => [
                'nullable',
                'integer',
                Rule::exists('groups', 'id')->where(fn ($query) => $query->where('tenant_id', $request->user()->tenant_id)),
            ],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'body' => ['nullable', 'string'],
            'location' => ['nullable', 'string', 'max:255'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
            'all_day' => ['nullable', 'boolean'],
            'calendar_uid' => ['nullable', 'string', 'max:255'],
            'calendar_url' => ['nullable', 'string', 'max:255'],
            'source' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        return response()->json($action->execute((int) $request->user()->tenant_id, $validated), 201);
    }

    public function show(Request $request, Event $event): JsonResponse
    {
        $this->authorizeTenantAccess($request, $event);

        return response()->json($event->load('attendances.person'));
    }

    public function update(Request $request, Event $event, UpdateEventAction $action): JsonResponse
    {
        $this->authorizeTenantAccess($request, $event);

        $validated = $request->validate([
            'group_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('groups', 'id')->where(fn ($query) => $query->where('tenant_id', $request->user()->tenant_id)),
            ],
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'body' => ['sometimes', 'nullable', 'string'],
            'location' => ['sometimes', 'nullable', 'string', 'max:255'],
            'starts_at' => ['sometimes', 'nullable', 'date'],
            'ends_at' => ['sometimes', 'nullable', 'date'],
            'all_day' => ['sometimes', 'boolean'],
            'calendar_uid' => ['sometimes', 'nullable', 'string', 'max:255'],
            'calendar_url' => ['sometimes', 'nullable', 'string', 'max:255'],
            'source' => ['sometimes', 'nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        return response()->json($action->execute($event, $validated));
    }

    public function destroy(Request $request, Event $event, DeleteEventAction $action): JsonResponse
    {
        $this->authorizeTenantAccess($request, $event);

        $action->execute($event);

        return response()->json([], 204);
    }

    private function authorizeTenantAccess(Request $request, Event $event): void
    {
        abort_unless((int) $event->tenant_id === (int) $request->user()->tenant_id, 404);
    }
}
