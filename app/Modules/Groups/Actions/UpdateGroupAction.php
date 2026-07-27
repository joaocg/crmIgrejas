<?php

declare(strict_types=1);

namespace App\Modules\Groups\Actions;

use App\Models\Group;

final class UpdateGroupAction
{
    public function execute(Group $group, array $data): Group
    {
        $group->fill($data);
        $group->save();

        return $group->refresh();
    }
}
