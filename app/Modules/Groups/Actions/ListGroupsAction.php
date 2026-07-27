<?php

declare(strict_types=1);

namespace App\Modules\Groups\Actions;

use App\Models\Group;

final class ListGroupsAction
{
    public function execute(): array
    {
        return Group::query()
            ->withCount('memberships')
            ->orderBy('name')
            ->get()
            ->all();
    }
}
