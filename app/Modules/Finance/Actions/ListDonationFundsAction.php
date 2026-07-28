<?php

declare(strict_types=1);

namespace App\Modules\Finance\Actions;

use App\Models\DonationFund;

final class ListDonationFundsAction
{
    public function execute(int $tenantId): array
    {
        return DonationFund::query()
            ->where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get()
            ->all();
    }
}
