<?php

declare(strict_types=1);

namespace App\Modules\Families\Actions;

use App\Models\Family;

final class CreateFamilyAction
{
    public function execute(int $tenantId, array $data): Family
    {
        return Family::create([
            ...$data,
            'tenant_id' => $tenantId,
        ]);
    }
}
