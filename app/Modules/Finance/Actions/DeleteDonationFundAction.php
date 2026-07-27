<?php

declare(strict_types=1);

namespace App\Modules\Finance\Actions;

use App\Models\DonationFund;

final class DeleteDonationFundAction
{
    public function execute(DonationFund $donationFund): void
    {
        $donationFund->delete();
    }
}
