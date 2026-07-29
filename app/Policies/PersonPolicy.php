<?php

declare(strict_types=1);

namespace App\Policies;

use App\Support\Authorization\ModulePolicy;

final class PersonPolicy extends ModulePolicy
{
    protected function abilityPrefix(): string
    {
        return 'people';
    }

    protected function navigationAbility(): string
    {
        return 'navigation.people';
    }
}
