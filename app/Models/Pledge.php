<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Pledge extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'family_id',
        'fund_id',
        'deposit_id',
        'fiscal_year',
        'pledged_on',
        'amount',
        'schedule',
        'payment_method',
        'check_number',
        'status',
        'notes',
        'non_deductible_amount',
        'payment_type',
    ];

    protected function casts(): array
    {
        return [
            'pledged_on' => 'date',
            'amount' => 'decimal:2',
            'non_deductible_amount' => 'decimal:2',
        ];
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function fund(): BelongsTo
    {
        return $this->belongsTo(DonationFund::class, 'fund_id');
    }

    public function deposit(): BelongsTo
    {
        return $this->belongsTo(Deposit::class);
    }
}
