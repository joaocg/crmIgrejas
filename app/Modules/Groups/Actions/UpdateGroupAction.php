<?php

declare(strict_types=1);

namespace App\Modules\Groups\Actions;

use App\Models\Group;

final class UpdateGroupAction
{
    /**
     * The previous implementation stripped `tenant_id` from $data by hand.
     * That guard is now structural: callers pass UpdateGroupRequest::validated(),
     * which only ever contains keys the rule set declares, and `tenant_id` is
     * not one of them — so the SPA's habit of posting `tenant_id: 1`
     * (resources/js/pages/modules/groups/GroupEditPage.vue:40-45) cannot reach
     * the model.
     *
     * @param  array<string, mixed>  $data
     */
    public function execute(Group $group, array $data): Group
    {
        $group->fill($data)->save();

        return $group;
    }
}
