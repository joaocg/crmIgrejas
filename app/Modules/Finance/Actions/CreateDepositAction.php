<?php

declare(strict_types=1);

namespace App\Modules\Finance\Actions;

use App\Models\Deposit;

final class CreateDepositAction
{
    public function execute(array $data): Deposit
    {
        return Deposit::create($data);
    }
}
