<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Deposit extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'fund_id',
        'date',
        'comment',
        'closed',
        'type',
        'entered_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'closed' => 'boolean',
        ];
    }

    public function fund(): BelongsTo
    {
        return $this->belongsTo(DonationFund::class, 'fund_id');
    }

    public function enteredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entered_by_user_id');
    }

    public function pledges(): HasMany
    {
        return $this->hasMany(Pledge::class);
    }
}
