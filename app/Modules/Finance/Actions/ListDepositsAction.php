<?php

declare(strict_types=1);

namespace App\Modules\Finance\Actions;

use App\Models\Deposit;

final class ListDepositsAction
{
    public function execute(): array
    {
        return Deposit::query()
            ->with(['fund', 'enteredBy'])
            ->orderByDesc('date')
            ->get()
            ->all();
    }
}
