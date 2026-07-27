<?php

declare(strict_types=1);

namespace App\Modules\Groups\Actions;

use App\Models\Group;

final class DeleteGroupAction
{
    public function execute(Group $group): void
    {
        $group->delete();
    }
}
