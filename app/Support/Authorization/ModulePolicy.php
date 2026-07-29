<?php

declare(strict_types=1);

namespace App\Support\Authorization;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

abstract class ModulePolicy
{
    abstract protected function abilityPrefix(): string;

    abstract protected function navigationAbility(): string;

    public function viewAny(User $user): bool
    {
        return $this->allows($user, $this->navigationAbility());
    }

    public function view(User $user, Model $model): bool
    {
        return $this->allows($user, $this->navigationAbility())
            && $this->sameTenant($user, $model);
    }

    public function create(User $user): bool
    {
        return $this->allows($user, $this->abilityPrefix().'.create');
    }

    public function update(User $user, Model $model): bool
    {
        return $this->allows($user, $this->abilityPrefix().'.update')
            && $this->sameTenant($user, $model);
    }

    public function delete(User $user, Model $model): bool
    {
        return $this->allows($user, $this->abilityPrefix().'.delete')
            && $this->sameTenant($user, $model);
    }

    protected function allows(User $user, string $ability): bool
    {
        return $user->role?->allows($ability) ?? false;
    }

    protected function sameTenant(User $user, Model $model): bool
    {
        return (int) $model->getAttribute('tenant_id') === (int) $user->tenant_id;
    }
}
