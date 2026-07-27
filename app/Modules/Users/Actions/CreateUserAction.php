<?php

declare(strict_types=1);

namespace App\Modules\Users\Actions;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

final class CreateUserAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data): User
    {
        return User::query()->create([
            'tenant_id' => Arr::get($data, 'tenant_id'),
            'role_id' => Arr::get($data, 'role_id'),
            'name' => (string) Arr::get($data, 'name'),
            'email' => (string) Arr::get($data, 'email'),
            'password' => Hash::make((string) Arr::get($data, 'password')),
            'locale' => Arr::get($data, 'locale', 'pt_BR'),
            'active' => (bool) Arr::get($data, 'active', true),
        ]);
    }
}
