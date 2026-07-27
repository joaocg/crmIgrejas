<?php

declare(strict_types=1);

namespace App\Modules\Care\Actions;

use App\Models\PastoralCareRecord;

final class CreatePastoralCareRecordAction
{
    public function execute(array $data): PastoralCareRecord
    {
        return PastoralCareRecord::create($data);
    }
}
