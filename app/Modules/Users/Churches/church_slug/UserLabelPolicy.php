<?php

declare(strict_types=1);

namespace App\Modules\Users\Churches\church_slug;

final class UserLabelPolicy
{
    public function userLabel(): string
    {
        return 'User';
    }
}
