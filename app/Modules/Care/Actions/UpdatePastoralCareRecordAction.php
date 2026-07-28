<?php

declare(strict_types=1);

namespace App\Modules\Care\Actions;

use App\Models\PastoralCareRecord;

final class UpdatePastoralCareRecordAction
{
    public function execute(PastoralCareRecord $pastoralCareRecord, array $data): PastoralCareRecord
    {
        unset($data['tenant_id']);
        $pastoralCareRecord->fill($data);
        $pastoralCareRecord->save();

        return $pastoralCareRecord->refresh();
    }
}
