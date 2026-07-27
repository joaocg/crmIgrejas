<?php

declare(strict_types=1);

namespace App\Modules\Users\Actions;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

final class UpdateUserAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(User $user, array $data): User
    {
        $user->fill([
            'tenant_id' => Arr::exists($data, 'tenant_id') ? Arr::get($data, 'tenant_id') : $user->tenant_id,
            'role_id' => Arr::exists($data, 'role_id') ? Arr::get($data, 'role_id') : $user->role_id,
            'name' => Arr::get($data, 'name', $user->name),
            'email' => Arr::get($data, 'email', $user->email),
            'password' => Arr::has($data, 'password') ? Hash::make((string) Arr::get($data, 'password')) : $user->password,
            'locale' => Arr::get($data, 'locale', $user->locale),
            'active' => Arr::exists($data, 'active') ? (bool) Arr::get($data, 'active') : $user->active,
        ]);

        $user->save();

        return $user->refresh();
    }
}
