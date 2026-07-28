<?php

declare(strict_types=1);

namespace App\Modules\Groups\Actions;

use App\Models\Group;

final class ListGroupsAction
{
    public function execute(int $tenantId): array
    {
        return Group::query()
            ->where('tenant_id', $tenantId)
            ->withCount('memberships')
            ->orderBy('name')
            ->get()
            ->all();
    }
}
