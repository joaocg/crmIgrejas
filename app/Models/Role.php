<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'description',
        'permissions',
        'is_system',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'is_system' => 'boolean',
            'active' => 'boolean',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function allows(string $ability): bool
    {
        $permissions = $this->permissions ?? [];

        if (! is_array($permissions)) {
            return false;
        }

        if (($permissions['*'] ?? false) === true) {
            return true;
        }

        return ($permissions[$ability] ?? false) === true;
    }
}
