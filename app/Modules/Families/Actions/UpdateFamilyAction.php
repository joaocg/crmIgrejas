<?php

declare(strict_types=1);

namespace App\Modules\Families\Actions;

use App\Models\Family;

final class UpdateFamilyAction
{
    public function execute(Family $family, array $data): Family
    {
        $family->fill($data);
        $family->save();

        return $family->refresh();
    }
}
