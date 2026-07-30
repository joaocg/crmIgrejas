<?php

declare(strict_types=1);

namespace App\Policies;

use App\Support\Authorization\ModulePolicy;

final class FamilyPolicy extends ModulePolicy
{
    protected function abilityPrefix(): string
    {
        return 'families';
    }

    protected function navigationAbility(): string
    {
        return 'navigation.families';
    }
}
