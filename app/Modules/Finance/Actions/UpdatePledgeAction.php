<?php

declare(strict_types=1);

namespace App\Modules\Finance\Actions;

use App\Models\Pledge;

final class UpdatePledgeAction
{
    public function execute(Pledge $pledge, array $data): Pledge
    {
        $pledge->fill($data);
        $pledge->save();

        return $pledge->refresh();
    }
}
