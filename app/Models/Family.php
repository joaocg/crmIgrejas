<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Family extends Model
{
    use BelongsToTenant, HasFactory;

    /**
     * Single source of truth for the relations FamilyResource reads via
     * whenLoaded(). Every call site that hydrates a Family for that resource
     * (list query, store/show/update loads) must eager-load exactly this
     * set, or a relation silently disappears from the JSON instead of
     * appearing as null/[] — see FamiliesPrivacyTest for the regression this
     * guards. Mirrors Person::API_RELATIONS.
     *
     * @var array<int, string>
     */
    public const API_RELATIONS = ['address', 'people', 'contacts'];

    protected $fillable = [
        'tenant_id',
        'address_id',
        'name',
        'wedding_date',
        'email',
        'home_phone',
        'work_phone',
        'mobile_phone',
        'envelope_number',
        'newsletter_enabled',
        'canvass_allowed',
        'deactivated_at',
    ];

    protected function casts(): array
    {
        return [
            'wedding_date' => 'date',
            'newsletter_enabled' => 'boolean',
            'canvass_allowed' => 'boolean',
            'deactivated_at' => 'datetime',
        ];
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function people(): HasMany
    {
        return $this->hasMany(Person::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }
}
