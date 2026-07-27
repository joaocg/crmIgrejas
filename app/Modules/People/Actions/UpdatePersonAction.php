<?php

declare(strict_types=1);

namespace App\Modules\People\Actions;

use App\Models\Person;

final class UpdatePersonAction
{
    public function execute(Person $person, array $data): Person
    {
        $person->fill($data);
        $person->save();

        return $person->refresh();
    }
}
