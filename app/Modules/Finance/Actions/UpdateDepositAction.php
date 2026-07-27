<?php

declare(strict_types=1);

namespace App\Modules\Finance\Actions;

use App\Models\Deposit;

final class UpdateDepositAction
{
    public function execute(Deposit $deposit, array $data): Deposit
    {
        $deposit->fill($data);
        $deposit->save();

        return $deposit->refresh();
    }
}
