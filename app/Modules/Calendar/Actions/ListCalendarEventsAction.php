<?php

declare(strict_types=1);

namespace App\Modules\Calendar\Actions;

use App\Models\Event;

final class ListCalendarEventsAction
{
    public function execute(int $tenantId): array
    {
        return Event::query()
            ->where('tenant_id', $tenantId)
            ->with(['group'])
            ->orderByDesc('starts_at')
            ->limit(20)
            ->get()
            ->all();
    }
}
