<?php

declare(strict_types=1);

namespace App\Modules\Events\Actions;

use App\Models\Event;

final class ListEventsAction
{
    public function execute(int $tenantId): array
    {
        return Event::query()
            ->where('tenant_id', $tenantId)
            ->with(['group'])
            ->orderByDesc('starts_at')
            ->get()
            ->all();
    }
}
