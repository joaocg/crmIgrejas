<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Note extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'person_id',
        'family_id',
        'title',
        'body',
        'type',
        'info',
        'is_private',
        'edited_by_user_id',
        'edited_at',
    ];

    protected function casts(): array
    {
        return [
            'is_private' => 'boolean',
            'edited_at' => 'datetime',
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

    public function editedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edited_by_user_id');
    }
}
