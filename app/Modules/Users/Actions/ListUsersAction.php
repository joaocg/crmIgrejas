<?php

declare(strict_types=1);

namespace App\Modules\Users\Actions;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListUsersAction
{
    public function execute(): LengthAwarePaginator
    {
        return User::query()
            ->orderBy('name')
            ->paginate(25);
    }
}
