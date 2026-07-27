<?php

declare(strict_types=1);

namespace App\Modules\Families\Actions;

use App\Models\Family;

final class ListFamiliesAction
{
    public function execute(): array
    {
        return Family::query()
            ->with(['address', 'people'])
            ->orderBy('name')
            ->get()
            ->all();
    }
}
