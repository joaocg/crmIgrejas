<?php

declare(strict_types=1);

namespace App\Modules\Care\Actions;

use App\Models\PastoralCareRecord;

final class DeletePastoralCareRecordAction
{
    public function execute(PastoralCareRecord $pastoralCareRecord): void
    {
        $pastoralCareRecord->delete();
    }
}
