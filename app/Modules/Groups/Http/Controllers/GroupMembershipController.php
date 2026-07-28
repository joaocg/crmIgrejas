<?php

declare(strict_types=1);

namespace App\Modules\Groups\Http\Controllers;

use App\Models\Group;
use App\Models\Person;
use App\Modules\Groups\Actions\AttachPersonToGroupAction;
use App\Modules\Groups\Actions\DetachPersonFromGroupAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class GroupMembershipController
{
    public function store(Request $request, Group $group, AttachPersonToGroupAction $action): JsonResponse
    {
        $this->authorizeTenantAccess($request, $group);

        $validated = $request->validate([
            'person_id' => [
                'required',
                'integer',
                Rule::exists('persons', 'id')->where(fn ($query) => $query->where('tenant_id', $request->user()->tenant_id)),
            ],
            'role_id' => [
                'nullable',
                'integer',
                Rule::exists('roles', 'id')->where(fn ($query) => $query->where('tenant_id', $request->user()->tenant_id)),
            ],
            'joined_at' => ['nullable', 'date'],
            'left_at' => ['nullable', 'date'],
            'is_manager' => ['nullable', 'boolean'],
        ]);

        return response()->json($action->execute($group, (int) $request->user()->tenant_id, $validated), 201);
    }

    public function destroy(Request $request, Group $group, Person $person, DetachPersonFromGroupAction $action): JsonResponse
    {
        $this->authorizeTenantAccess($request, $group);
        abort_unless((int) $person->tenant_id === (int) $request->user()->tenant_id, 404);

        $action->execute($group, $person);

        return response()->json([], 204);
    }

    private function authorizeTenantAccess(Request $request, Group $group): void
    {
        abort_unless((int) $group->tenant_id === (int) $request->user()->tenant_id, 404);
    }
}
