<?php

declare(strict_types=1);

namespace App\Modules\Families\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class FamilyResource extends JsonResource
{
    /**
     * Legacy rule: src/v2/templates/people/familylist.php:104-121 replaces
     * Address, Home Phone, Cell Phone and email with "Private Data" when the
     * user is not allowed to see private data (User::isSeePrivacyDataEnabled),
     * the same gate personlist.php uses for People. familyview.php:26 shows
     * Work Phone is guarded by the same $can_see_privatedata check, even
     * though the list table itself has no Work Phone column — so all four
     * legacy private fields (email, home_phone, work_phone, mobile_phone)
     * are masked here, mirroring PersonResource::PRIVATE_DATA_ABILITY.
     *
     * Deliberate divergence: familyview.php:26 is
     * `$can_see_privatedata = ($iCurrentUserFamID == $iFamilyID || ...)`, so
     * the legacy also lets a user see their OWN family's private data without
     * the permission. Only the second disjunct is ported — the new `users`
     * table has no family_id/person_id, so there is nothing to compare
     * against yet. The divergence is more restrictive, not less. Restore the
     * self-access branch when users gain a person link.
     *
     * `contacts` carries the same phone/email values in normalized form
     * (LegacyDataImporter::syncContact writes both representations from the
     * same legacy columns), so it is masked by the same gate — otherwise the
     * masking on the direct columns would be trivially bypassable.
     */
    public const PRIVATE_DATA_ABILITY = 'families.private_data.view';

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $showsPrivateData = $request->user()?->role?->allows(self::PRIVATE_DATA_ABILITY) ?? false;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'wedding_date' => $this->wedding_date?->toDateString(),
            'envelope_number' => $this->envelope_number,
            'newsletter_enabled' => (bool) $this->newsletter_enabled,
            'canvass_allowed' => (bool) $this->canvass_allowed,
            'deactivated_at' => $this->deactivated_at?->toIso8601String(),
            'private_data_hidden' => ! $showsPrivateData,
            'email' => $showsPrivateData ? $this->email : null,
            'home_phone' => $showsPrivateData ? $this->home_phone : null,
            'work_phone' => $showsPrivateData ? $this->work_phone : null,
            'mobile_phone' => $showsPrivateData ? $this->mobile_phone : null,
            'address' => $showsPrivateData
                ? $this->whenLoaded('address', fn (): ?array => $this->address === null ? null : [
                    'id' => $this->address->id,
                    'line1' => $this->address->line1,
                    'line2' => $this->address->line2,
                    'city' => $this->address->city,
                    'state' => $this->address->state,
                    'postal_code' => $this->address->postal_code,
                ])
                : null,
            'contacts' => $showsPrivateData
                ? $this->whenLoaded('contacts', fn (): array => $this->contacts
                    ->map(fn ($contact): array => [
                        'id' => $contact->id,
                        'type' => $contact->type,
                        'label' => $contact->label,
                        'value' => $contact->value,
                        'is_primary' => (bool) $contact->is_primary,
                    ])
                    ->all())
                : [],
            'people' => $this->whenLoaded('people', fn (): array => $this->people
                ->map(fn ($person): array => [
                    'id' => $person->id,
                    'first_name' => $person->first_name,
                    'last_name' => $person->last_name,
                ])
                ->all()),
        ];
    }
}
