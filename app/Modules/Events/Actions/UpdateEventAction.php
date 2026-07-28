<?php

declare(strict_types=1);

namespace App\Modules\Events\Actions;

use App\Models\Event;

final class UpdateEventAction
{
    public function execute(Event $event, array $data): Event
    {
        unset($data['tenant_id']);
        $event->fill($data);
        $event->save();

        return $event->refresh();
    }
}
