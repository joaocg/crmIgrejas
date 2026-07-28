<?php

declare(strict_types=1);

namespace App\Modules\Families\Actions;

use App\Models\Family;

final class ListFamiliesAction
{
    public function execute(int $tenantId): array
    {
        return Family::query()
            ->where('tenant_id', $tenantId)
            ->with(['address', 'people'])
            ->orderBy('name')
            ->get()
            ->all();
    }
}
