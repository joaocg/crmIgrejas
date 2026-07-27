<?php

declare(strict_types=1);

namespace App\Modules\Groups\Http\Controllers;

use App\Models\Group;
use App\Models\GroupMembership;
use App\Models\Person;
use App\Modules\Groups\Actions\AttachPersonToGroupAction;
use App\Modules\Groups\Actions\DetachPersonFromGroupAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class GroupMembershipController
{
    public function store(Request $request, Group $group, AttachPersonToGroupAction $action): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id' => ['required', 'integer', 'exists:tenants,id'],
            'person_id' => ['required', 'integer', 'exists:persons,id'],
            'role_id' => ['nullable', 'integer', 'exists:roles,id'],
            'joined_at' => ['nullable', 'date'],
            'left_at' => ['nullable', 'date'],
            'is_manager' => ['nullable', 'boolean'],
        ]);

        return response()->json($action->execute($group, $validated), 201);
    }

    public function destroy(Group $group, Person $person, DetachPersonFromGroupAction $action): JsonResponse
    {
        $action->execute($group, $person);

        return response()->json([], 204);
    }
}
