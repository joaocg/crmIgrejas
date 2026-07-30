<?php

declare(strict_types=1);

namespace App\Modules\Groups\Actions;

use App\Models\Group;

final class CreateGroupAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data): Group
    {
        return Group::create($data);
    }
}
