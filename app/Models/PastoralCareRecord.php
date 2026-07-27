<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PastoralCareRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'person_id',
        'family_id',
        'pastor_user_id',
        'pastor_name',
        'type',
        'visible',
        'body',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'visible' => 'boolean',
            'recorded_at' => 'datetime',
        ];
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function pastor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pastor_user_id');
    }
}
