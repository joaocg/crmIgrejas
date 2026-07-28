<?php

declare(strict_types=1);

namespace App\Modules\Care\Actions;

use App\Models\Note;

final class UpdateNoteAction
{
    public function execute(Note $note, array $data): Note
    {
        unset($data['tenant_id']);
        $note->fill($data);
        $note->save();

        return $note->refresh();
    }
}
