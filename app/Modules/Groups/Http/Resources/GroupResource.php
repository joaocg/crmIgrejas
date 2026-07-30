<?php

declare(strict_types=1);

namespace App\Modules\Groups\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * No field of a group is private data in the legacy sense.
 *
 * Rule checked explicitly rather than assumed: neither
 * src/v2/templates/group/grouplist.php nor src/skin/js/group/GroupList.js
 * contains an isSeePrivacyDataEnabled() branch, and the only two occurrences
 * anywhere under src/v2/templates/group/ are groupview.php:409 (a *person's*
 * group-specific property rows) and groupview.php:524 (whether the members
 * table inside a single group is showable). Both guard person data reached
 * through a group, not the group's own columns. So — unlike PersonResource
 * and FamilyResource — there is no Group::PRIVATE_DATA_ABILITY and nothing
 * here is masked.
 *
 * `members` mirrors the legacy group view's member table
 * (src/EcclesiaCRM/APIControllers/PeopleGroupController.php:442-447, which
 * joins the person and drops people with a per_datedeactivated). It is emitted
 * through whenLoaded(), and `members_count` through whenCounted(), so both
 * keys vanish entirely rather than reading null if a call site forgets to
 * hydrate them — which is why every call site loads Group::API_RELATIONS and
 * Group::API_COUNTS.
 *
 * Deliberate omission: the legacy list's third column, "Group Cart Status"
 * (src/skin/js/group/GroupList.js:100-123), has no counterpart here because
 * the cart module has not been migrated. Restore it when the cart lands.
 */
final class GroupResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'role_id' => $this->role_id,
            'name' => $this->name,
            'type' => $this->type,
            'description' => $this->description,
            'has_special_properties' => (bool) $this->has_special_properties,
            'is_active' => (bool) $this->is_active,
            'include_in_email_export' => (bool) $this->include_in_email_export,
            'members_count' => $this->whenCounted('members'),
            'members' => $this->whenLoaded('memberships', fn (): array => $this->memberships
                ->map(fn ($membership): array => [
                    'id' => $membership->id,
                    'person_id' => $membership->person_id,
                    'role_id' => $membership->role_id,
                    'joined_at' => $membership->joined_at?->toIso8601String(),
                    'left_at' => $membership->left_at?->toIso8601String(),
                    'is_manager' => (bool) $membership->is_manager,
                    'person' => $membership->person === null ? null : [
                        'id' => $membership->person->id,
                        'first_name' => $membership->person->first_name,
                        'last_name' => $membership->person->last_name,
                    ],
                ])
                ->all()),
        ];
    }
}
