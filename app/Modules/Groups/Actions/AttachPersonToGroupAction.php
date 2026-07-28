<?php

declare(strict_types=1);

namespace App\Modules\Groups\Actions;

use App\Models\Group;
use App\Models\GroupMembership;

final class AttachPersonToGroupAction
{
    public function execute(Group $group, int $tenantId, array $data): GroupMembership
    {
        return GroupMembership::updateOrCreate(
            [
                'group_id' => $group->id,
                'person_id' => $data['person_id'],
            ],
            [
                'tenant_id' => $tenantId,
                'role_id' => $data['role_id'] ?? null,
                'joined_at' => $data['joined_at'] ?? null,
                'left_at' => $data['left_at'] ?? null,
                'is_manager' => $data['is_manager'] ?? false,
            ]
        );
    }
}
