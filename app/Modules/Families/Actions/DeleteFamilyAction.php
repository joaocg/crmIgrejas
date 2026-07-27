<?php

declare(strict_types=1);

namespace App\Modules\Families\Actions;

use App\Models\Family;

final class DeleteFamilyAction
{
    public function execute(Family $family): void
    {
        $family->delete();
    }
}
