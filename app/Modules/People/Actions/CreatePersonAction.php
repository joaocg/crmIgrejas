<?php

declare(strict_types=1);

namespace App\Modules\People\Actions;

use App\Models\Person;

final class CreatePersonAction
{
    public function execute(int $tenantId, array $data): Person
    {
        return Person::create([
            ...$data,
            'tenant_id' => $tenantId,
        ]);
    }
}
