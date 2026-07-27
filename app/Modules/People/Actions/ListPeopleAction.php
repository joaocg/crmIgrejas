<?php

declare(strict_types=1);

namespace App\Modules\People\Actions;

use App\Models\Person;

final class ListPeopleAction
{
    public function execute(): array
    {
        return Person::query()
            ->with(['family', 'address'])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get()
            ->all();
    }
}
