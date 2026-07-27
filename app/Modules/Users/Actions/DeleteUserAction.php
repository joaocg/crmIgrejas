<?php

declare(strict_types=1);

namespace App\Modules\Users\Actions;

use App\Models\User;

final class DeleteUserAction
{
    public function execute(User $user): void
    {
        $user->delete();
    }
}
