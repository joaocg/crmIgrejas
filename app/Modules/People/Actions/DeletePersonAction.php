<?php

declare(strict_types=1);

namespace App\Modules\People\Actions;

use App\Models\Person;

final class DeletePersonAction
{
    public function execute(Person $person): void
    {
        $person->delete();
    }
}
