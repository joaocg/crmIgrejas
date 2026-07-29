<?php

declare(strict_types=1);

namespace App\Modules\People\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdatePersonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'family_id' => ['sometimes', 'nullable', 'integer', Rule::exists('families', 'id')],
            'address_id' => ['sometimes', 'nullable', 'integer', Rule::exists('addresses', 'id')],
            'title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'first_name' => ['sometimes', 'string', 'max:255'],
            'middle_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'last_name' => ['sometimes', 'string', 'max:255'],
            'suffix' => ['sometimes', 'nullable', 'string', 'max:255'],
            'birth_date' => ['sometimes', 'nullable', 'date'],
            'membership_date' => ['sometimes', 'nullable', 'date'],
            'gender' => ['sometimes', 'nullable', 'integer'],
            'envelope_number' => ['sometimes', 'nullable', 'integer'],
            'newsletter_enabled' => ['sometimes', 'boolean'],
            'deactivated_at' => ['sometimes', 'nullable', 'date'],
        ];
    }
}
