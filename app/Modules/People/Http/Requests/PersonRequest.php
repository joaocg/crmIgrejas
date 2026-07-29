<?php

declare(strict_types=1);

namespace App\Modules\People\Http\Requests;

use App\Support\Http\Requests\BuildsCrudRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Shared field constraints for StorePersonRequest and UpdatePersonRequest.
 *
 * Each concrete request declares only how presence is handled (required on
 * create vs. sometimes-optional on update) by combining typeRules() with
 * the forCreate()/forUpdate() helpers from BuildsCrudRules.
 */
abstract class PersonRequest extends FormRequest
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
            'family_id' => ['integer', Rule::exists('families', 'id')],
            'address_id' => ['integer', Rule::exists('addresses', 'id')],
            'title' => ['string', 'max:255'],
            'first_name' => ['string', 'max:255'],
            'middle_name' => ['string', 'max:255'],
            'last_name' => ['string', 'max:255'],
            'suffix' => ['string', 'max:255'],
            'birth_date' => ['date'],
            'membership_date' => ['date'],
            'gender' => ['integer'],
            'envelope_number' => ['integer'],
            'newsletter_enabled' => ['boolean'],
            'deactivated_at' => ['date'],
        ];
    }
}
