<?php

declare(strict_types=1);

namespace App\Modules\Events\Actions;

use App\Models\Event;

final class DeleteEventAction
{
    public function execute(Event $event): void
    {
        $event->delete();
    }
}
