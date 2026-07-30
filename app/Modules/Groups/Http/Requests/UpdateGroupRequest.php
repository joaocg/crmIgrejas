<?php

declare(strict_types=1);

namespace App\Modules\Groups\Http\Requests;

final class UpdateGroupRequest extends GroupRequest
{
    /**
     * The three booleans are non-nullable on update because the columns are
     * `NOT NULL DEFAULT ...` (database/migrations/
     * 2026_07_26_000005_create_group_and_activity_tables.php:18-20) and the
     * legacy toggles that write them only ever send "true"/"false" —
     * src/EcclesiaCRM/APIControllers/PeopleGroupController.php:684 and :701
     * reject anything else with a 500.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return $this->forUpdate($this->typeRules(), nonNullable: [
            'name',
            'has_special_properties',
            'is_active',
            'include_in_email_export',
        ]);
    }
}
