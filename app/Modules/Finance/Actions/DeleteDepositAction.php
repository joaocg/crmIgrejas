<?php

declare(strict_types=1);

namespace App\Modules\Finance\Actions;

use App\Models\Deposit;

final class DeleteDepositAction
{
    public function execute(Deposit $deposit): void
    {
        $deposit->delete();
    }
}
