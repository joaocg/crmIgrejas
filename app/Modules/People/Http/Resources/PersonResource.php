<?php

declare(strict_types=1);

namespace App\Modules\People\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class PersonResource extends JsonResource
{
    /**
     * Legacy rule: src/v2/templates/people/personlist.php replaces address,
     * phones and email with "Private Data" when the user is not allowed to
     * see private data (User::isSeePrivacyDataEnabled).
     */
    public const PRIVATE_DATA_ABILITY = 'people.private_data.view';

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $showsPrivateData = $request->user()?->role?->allows(self::PRIVATE_DATA_ABILITY) ?? false;

        return [
            'id' => $this->id,
            'family_id' => $this->family_id,
            'title' => $this->title,
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'suffix' => $this->suffix,
            'birth_date' => $this->birth_date?->toDateString(),
            'membership_date' => $this->membership_date?->toDateString(),
            'gender' => $this->gender,
            'envelope_number' => $this->envelope_number,
            'newsletter_enabled' => (bool) $this->newsletter_enabled,
            'deactivated_at' => $this->deactivated_at?->toIso8601String(),
            'private_data_hidden' => ! $showsPrivateData,
            'family' => $this->whenLoaded('family', fn (): array => [
                'id' => $this->family->id,
                'name' => $this->family->name,
            ]),
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
        ];
    }
}
