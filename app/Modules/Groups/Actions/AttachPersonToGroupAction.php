<?php

declare(strict_types=1);

namespace App\Modules\Groups\Actions;

use App\Models\Group;
use App\Models\GroupMembership;

final class AttachPersonToGroupAction
{
    /**
     * Legacy rule: src/EcclesiaCRM/APIControllers/PeopleGroupController.php:571-574
     * resolves the person's row in the group with
     * `Person2group2roleP2g2rQuery::create()->filterByGroupId()->filterByPersonId()->findOneOrCreate()`,
     * i.e. re-adding somebody who is already in the group updates their row
     * instead of creating a second one. updateOrCreate on the same
     * (group_id, person_id) pair reproduces that, and matches the
     * `unique(['group_id', 'person_id'])` index the new schema carries
     * (database/migrations/2026_07_26_000005_create_group_and_activity_tables.php:35).
     *
     * @param  array<string, mixed>  $data
     */
    public function execute(Group $group, array $data): GroupMembership
    {
        return GroupMembership::updateOrCreate(
            [
                'group_id' => $group->id,
                'person_id' => $data['person_id'],
            ],
            [
                'role_id' => $data['role_id'] ?? null,
                'joined_at' => $data['joined_at'] ?? null,
                'left_at' => $data['left_at'] ?? null,
                'is_manager' => $data['is_manager'] ?? false,
            ]
        );
    }
}
