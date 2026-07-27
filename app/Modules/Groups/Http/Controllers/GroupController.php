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

final class GroupController
{
    public function index(ListGroupsAction $action): JsonResponse
    {
        return response()->json($action->execute());
    }

    public function store(Request $request, CreateGroupAction $action): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id' => ['required', 'integer', 'exists:tenants,id'],
            'role_id' => ['nullable', 'integer', 'exists:roles,id'],
            'type' => ['nullable', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'has_special_properties' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'include_in_email_export' => ['nullable', 'boolean'],
        ]);

        return response()->json($action->execute($validated), 201);
    }

    public function show(Group $group): JsonResponse
    {
        return response()->json($group->load(['memberships.person']));
    }

    public function update(Request $request, Group $group, UpdateGroupAction $action): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id' => ['sometimes', 'integer', 'exists:tenants,id'],
            'role_id' => ['sometimes', 'nullable', 'integer', 'exists:roles,id'],
            'type' => ['sometimes', 'nullable', 'string', 'max:50'],
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'has_special_properties' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'include_in_email_export' => ['sometimes', 'boolean'],
        ]);

        return response()->json($action->execute($group, $validated));
    }

    public function destroy(Group $group, DeleteGroupAction $action): JsonResponse
    {
        $action->execute($group);

        return response()->json([], 204);
    }
}
