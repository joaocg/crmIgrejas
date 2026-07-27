<?php

declare(strict_types=1);

namespace App\Modules\Finance\Actions;

use App\Models\DonationFund;

final class ListDonationFundsAction
{
    public function execute(): array
    {
        return DonationFund::query()
            ->orderBy('name')
            ->get()
            ->all();
    }
}
