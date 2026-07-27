<?php

declare(strict_types=1);

namespace App\Modules\Finance\Actions;

use App\Models\Pledge;

final class DeletePledgeAction
{
    public function execute(Pledge $pledge): void
    {
        $pledge->delete();
    }
}
