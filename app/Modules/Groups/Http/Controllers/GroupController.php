<?php

declare(strict_types=1);

namespace App\Modules\Groups\Http\Controllers;

use App\Models\Group;
use App\Modules\Groups\Actions\CreateGroupAction;
use App\Modules\Groups\Actions\DeleteGroupAction;
use App\Modules\Groups\Actions\ListGroupsAction;
use App\Modules\Groups\Actions\UpdateGroupAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class GroupController
{
    public function index(Request $request, ListGroupsAction $action): JsonResponse
    {
        return response()->json($action->execute((int) $request->user()->tenant_id));
    }

    public function store(Request $request, CreateGroupAction $action): JsonResponse
    {
        $validated = $request->validate([
            'role_id' => [
                'nullable',
                'integer',
                Rule::exists('roles', 'id')->where(fn ($query) => $query->where('tenant_id', $request->user()->tenant_id)),
            ],
            'type' => ['nullable', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'has_special_properties' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'include_in_email_export' => ['nullable', 'boolean'],
        ]);

        return response()->json($action->execute((int) $request->user()->tenant_id, $validated), 201);
    }

    public function show(Request $request, Group $group): JsonResponse
    {
        $this->authorizeTenantAccess($request, $group);

        return response()->json($group->load(['memberships.person']));
    }

    public function update(Request $request, Group $group, UpdateGroupAction $action): JsonResponse
    {
        $this->authorizeTenantAccess($request, $group);

        $validated = $request->validate([
            'role_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('roles', 'id')->where(fn ($query) => $query->where('tenant_id', $request->user()->tenant_id)),
            ],
            'type' => ['sometimes', 'nullable', 'string', 'max:50'],
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'has_special_properties' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'include_in_email_export' => ['sometimes', 'boolean'],
        ]);

        return response()->json($action->execute($group, $validated));
    }

    public function destroy(Request $request, Group $group, DeleteGroupAction $action): JsonResponse
    {
        $this->authorizeTenantAccess($request, $group);

        $action->execute($group);

        return response()->json([], 204);
    }

    private function authorizeTenantAccess(Request $request, Group $group): void
    {
        abort_unless((int) $group->tenant_id === (int) $request->user()->tenant_id, 404);
    }
}
