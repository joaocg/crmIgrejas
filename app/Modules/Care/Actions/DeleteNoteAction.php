<?php

declare(strict_types=1);

namespace App\Modules\Care\Actions;

use App\Models\Note;

final class DeleteNoteAction
{
    public function execute(Note $note): void
    {
        $note->delete();
    }
}
