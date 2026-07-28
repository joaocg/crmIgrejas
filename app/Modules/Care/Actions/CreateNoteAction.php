<?php

declare(strict_types=1);

namespace App\Modules\Care\Actions;

use App\Models\Note;

final class CreateNoteAction
{
    public function execute(int $tenantId, array $data): Note
    {
        return Note::create([
            ...$data,
            'tenant_id' => $tenantId,
        ]);
    }
}
