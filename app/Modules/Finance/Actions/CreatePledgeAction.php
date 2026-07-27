<?php

declare(strict_types=1);

namespace App\Modules\Finance\Actions;

use App\Models\Pledge;

final class CreatePledgeAction
{
    public function execute(array $data): Pledge
    {
        return Pledge::create($data);
    }
}
