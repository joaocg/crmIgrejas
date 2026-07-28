<?php

declare(strict_types=1);

namespace App\Modules\Care\Actions;

use App\Models\PastoralCareRecord;

final class ListPastoralCareRecordsAction
{
    public function execute(int $tenantId): array
    {
        return PastoralCareRecord::query()
            ->where('tenant_id', $tenantId)
            ->with(['person', 'family', 'pastor'])
            ->latest('recorded_at')
            ->get()
            ->all();
    }
}
