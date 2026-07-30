<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Group extends Model
{
    use BelongsToTenant, HasFactory;

    /**
     * Single source of truth for the relations GroupResource reads via
     * whenLoaded(). Every call site that hydrates a Group for that resource
     * (list query, store/show/update loads) must eager-load exactly this set,
     * or a relation silently disappears from the JSON instead of appearing as
     * null/[] — see
     * GroupsModuleTest::test_the_members_and_count_keys_are_always_present for
     * the regression this guards. Mirrors Person::API_RELATIONS and
     * Family::API_RELATIONS.
     *
     * activeMemberships, not memberships: the legacy member table at
     * src/EcclesiaCRM/APIControllers/PeopleGroupController.php:442-447 joins
     * the person and drops anyone with a per_datedeactivated
     * ("// GDRP, when a person is completely deactivated"), so a deactivated
     * person is absent from the roster, not just from the count. Loading the
     * unfiltered relation here would both leak those names and make
     * members_count disagree with count(members) in the same payload.
     *
     * @var array<int, string>
     */
    public const API_RELATIONS = ['activeMemberships.person'];

    /**
     * Companion to API_RELATIONS for the aggregate the resource reads via
     * whenCounted(), which has the same disappearing-key behaviour. Pass to
     * withCount() on the list query and loadCount() on the single-model
     * endpoints.
     *
     * Legacy rule: src/EcclesiaCRM/model/EcclesiaCRM/GroupQuery.php:36 adds
     * `COUNT(person_per.per_ID) AS memberCount` to *every* Group query via
     * preSelect(), and line 34 restricts that count with
     * `person_per.per_datedeactivated is NULL`. That is the number
     * src/skin/js/group/GroupList.js:93 renders in the "Members" column, so
     * the count here excludes deactivated people the same way — see
     * activeMemberships().
     *
     * @var array<int, string>
     */
    public const API_COUNTS = ['activeMemberships as members_count'];

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

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(GroupMembership::class);
    }

    /**
     * Memberships whose person is not deactivated.
     *
     * Legacy rule: src/EcclesiaCRM/model/EcclesiaCRM/GroupQuery.php:34-36
     * joins person_per, filters `per_datedeactivated is NULL` and only then
     * counts, so a deactivated person never contributes to the group list's
     * member count. src/v2/templates/group/grouplist.php:42-43 repeats the
     * same pair of lines for the "Group Types Overview" tiles.
     */
    public function activeMemberships(): HasMany
    {
        return $this->memberships()->whereHas(
            'person',
            fn (Builder $query): Builder => $query->whereNull('deactivated_at')
        );
    }
}
