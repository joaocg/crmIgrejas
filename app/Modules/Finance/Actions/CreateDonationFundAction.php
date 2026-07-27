<?php

declare(strict_types=1);

namespace App\Modules\Finance\Actions;

use App\Models\DonationFund;

final class CreateDonationFundAction
{
    public function execute(array $data): DonationFund
    {
        return DonationFund::create($data);
    }
}
