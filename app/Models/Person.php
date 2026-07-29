<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Person extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'persons';

    protected $fillable = [
        'tenant_id',
        'family_id',
        'address_id',
        'title',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'birth_date',
        'membership_date',
        'gender',
        'envelope_number',
        'newsletter_enabled',
        'deactivated_at',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'membership_date' => 'date',
            'newsletter_enabled' => 'boolean',
            'deactivated_at' => 'datetime',
        ];
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }
}
