<?php

declare(strict_types=1);

namespace App\Modules\Events\Actions;

use App\Models\Event;

final class CreateEventAction
{
    public function execute(int $tenantId, array $data): Event
    {
        return Event::create([
            ...$data,
            'tenant_id' => $tenantId,
        ]);
    }
}
