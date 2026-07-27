<?php

declare(strict_types=1);

namespace App\Modules\Events\Actions;

use App\Models\Event;

final class CreateEventAction
{
    public function execute(array $data): Event
    {
        return Event::create($data);
    }
}
