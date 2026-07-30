<?php

declare(strict_types=1);

namespace App\Modules\Groups\Http\Controllers;

use App\Models\Group;
use App\Models\GroupMembership;
use App\Models\Person;
use App\Modules\Groups\Actions\AttachPersonToGroupAction;
use App\Modules\Groups\Actions\DetachPersonFromGroupAction;
use App\Modules\Groups\Http\Requests\StoreGroupMembershipRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

/**
 * Both endpoints authorize `update` on the group.
 *
 * Legacy note: src/EcclesiaCRM/APIControllers/PeopleGroupController.php:559
 * (addPersonToGroup) and :496 (removePersonFromGroup) carry NO permission
 * check of their own — a legacy gap. The only gate is on the screen that calls
 * them: src/EcclesiaCRM/VIEWControllers/VIEWGroupController.php:92-101 sets
 * `$_SESSION['bManageGroups']` from the per-group manager row or from
 * `isManageGroupsEnabled()` before rendering groupview.php. Porting them under
 * the group's own `update` ability is the more restrictive reading of that
 * gate; the per-group manager disjunct is unportable for the reason documented
 * on App\Policies\GroupPolicy.
 */
final class GroupMembershipController
{
    public function store(
        StoreGroupMembershipRequest $request,
        Group $group,
        AttachPersonToGroupAction $action
    ): JsonResponse {
        Gate::authorize('update', $group);

        $membership = $action->execute($group, $request->validated());

        return response()->json($this->present($membership), 201);
    }

    public function destroy(Group $group, Person $person, DetachPersonFromGroupAction $action): JsonResponse
    {
        Gate::authorize('update', $group);

        $action->execute($group, $person);

        return response()->json([], 204);
    }

    /**
     * @return array<string, mixed>
     */
    private function present(GroupMembership $membership): array
    {
        return [
            'id' => $membership->id,
            'group_id' => $membership->group_id,
            'person_id' => $membership->person_id,
            'role_id' => $membership->role_id,
            'joined_at' => $membership->joined_at?->toIso8601String(),
            'left_at' => $membership->left_at?->toIso8601String(),
            'is_manager' => (bool) $membership->is_manager,
        ];
    }
}
