<?php

declare(strict_types=1);

namespace App\Modules\Events\Actions;

use App\Models\Event;
use App\Models\EventAttendance;

final class MarkAttendanceAction
{
    public function execute(Event $event, int $tenantId, array $data): EventAttendance
    {
        return EventAttendance::updateOrCreate(
            [
                'event_id' => $event->id,
                'person_id' => $data['person_id'],
            ],
            [
                'tenant_id' => $tenantId,
                'checked_in_at' => $data['checked_in_at'] ?? null,
                'checked_out_at' => $data['checked_out_at'] ?? null,
                'status' => $data['status'] ?? null,
            ]
        );
    }
}
