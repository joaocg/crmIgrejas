<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'line1',
        'line2',
        'city',
        'state',
        'postal_code',
        'country',
        'latitude',
        'longitude',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function families(): HasMany
    {
        return $this->hasMany(Family::class);
    }

    public function people(): HasMany
    {
        return $this->hasMany(Person::class);
    }
}
