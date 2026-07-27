<?php

declare(strict_types=1);

namespace App\Modules\Finance\Actions;

use App\Models\DonationFund;

final class UpdateDonationFundAction
{
    public function execute(DonationFund $donationFund, array $data): DonationFund
    {
        $donationFund->fill($data);
        $donationFund->save();

        return $donationFund->refresh();
    }
}
