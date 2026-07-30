<?php

declare(strict_types=1);

namespace App\Modules\Families\Http\Requests;

use App\Support\Http\Requests\BuildsCrudRules;
use App\Support\Validation\TenantRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Shared field constraints for StoreFamilyRequest and UpdateFamilyRequest.
 *
 * Each concrete request declares only how presence is handled (required on
 * create vs. sometimes-optional on update) by combining typeRules() with
 * the forCreate()/forUpdate() helpers from BuildsCrudRules. Mirrors
 * App\Modules\People\Http\Requests\PersonRequest.
 */
abstract class FamilyRequest extends FormRequest
{
    use BuildsCrudRules;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * Field => type/format constraints, without presence modifiers
     * (required/nullable/sometimes).
     *
     * @return array<string, array<int, mixed>>
     */
    protected function typeRules(): array
    {
        return [
            'address_id' => ['integer', TenantRule::exists('addresses')],
            'name' => ['string', 'max:255'],
            'wedding_date' => ['date'],
            'email' => ['email', 'max:255'],
            'home_phone' => ['string', 'max:30'],
            'work_phone' => ['string', 'max:30'],
            'mobile_phone' => ['string', 'max:30'],
            'envelope_number' => ['integer'],
            'newsletter_enabled' => ['boolean'],
            'canvass_allowed' => ['boolean'],
            'deactivated_at' => ['date'],
        ];
    }
}
