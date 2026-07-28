<?php

declare(strict_types=1);

namespace App\Modules\People\Actions;

use App\Models\Person;

final class ListPeopleAction
{
    public function execute(int $tenantId): array
    {
        return Person::query()
            ->where('tenant_id', $tenantId)
            ->with(['family', 'address'])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get()
            ->all();
    }
}
