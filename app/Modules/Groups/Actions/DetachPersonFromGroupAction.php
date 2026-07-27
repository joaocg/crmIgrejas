<?php

declare(strict_types=1);

namespace App\Modules\Groups\Actions;

use App\Models\Group;
use App\Models\Person;

final class DetachPersonFromGroupAction
{
    public function execute(Group $group, Person $person): void
    {
        $group->memberships()->where('person_id', $person->id)->delete();
    }
}
