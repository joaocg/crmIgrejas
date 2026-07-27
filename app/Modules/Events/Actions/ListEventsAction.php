<?php

declare(strict_types=1);

namespace App\Modules\Events\Actions;

use App\Models\Event;

final class ListEventsAction
{
    public function execute(): array
    {
        return Event::query()
            ->with(['group'])
            ->orderByDesc('starts_at')
            ->get()
            ->all();
    }
}
