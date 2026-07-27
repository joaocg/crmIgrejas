<?php

declare(strict_types=1);

namespace App\Modules\Care\Actions;

use App\Models\PastoralCareRecord;

final class ListPastoralCareRecordsAction
{
    public function execute(): array
    {
        return PastoralCareRecord::query()
            ->with(['person', 'family', 'pastor'])
            ->latest('recorded_at')
            ->get()
            ->all();
    }
}
