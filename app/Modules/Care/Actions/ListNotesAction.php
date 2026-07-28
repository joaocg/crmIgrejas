<?php

declare(strict_types=1);

namespace App\Modules\Care\Actions;

use App\Models\Note;

final class ListNotesAction
{
    public function execute(int $tenantId): array
    {
        return Note::query()
            ->where('tenant_id', $tenantId)
            ->with(['person', 'family', 'editedBy'])
            ->latest('edited_at')
            ->get()
            ->all();
    }
}
