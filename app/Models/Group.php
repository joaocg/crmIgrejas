<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'role_id',
        'type',
        'name',
        'description',
        'has_special_properties',
        'is_active',
        'include_in_email_export',
    ];

    protected function casts(): array
    {
        return [
            'has_special_properties' => 'boolean',
            'is_active' => 'boolean',
            'include_in_email_export' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(GroupMembership::class);
    }
}
