<?php

declare(strict_types=1);

namespace App\Modules\Finance\Actions;

use App\Models\Pledge;

final class ListPledgesAction
{
    public function execute(): array
    {
        return Pledge::query()
            ->with(['family', 'fund', 'deposit'])
            ->orderByDesc('pledged_on')
            ->get()
            ->all();
    }
}
