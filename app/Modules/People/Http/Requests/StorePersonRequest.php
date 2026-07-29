<?php

declare(strict_types=1);

namespace App\Modules\People\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StorePersonRequest extends FormRequest
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
            'family_id' => ['nullable', 'integer', Rule::exists('families', 'id')],
            'address_id' => ['nullable', 'integer', Rule::exists('addresses', 'id')],
            'title' => ['nullable', 'string', 'max:255'],
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'suffix' => ['nullable', 'string', 'max:255'],
            'birth_date' => ['nullable', 'date'],
            'membership_date' => ['nullable', 'date'],
            'gender' => ['nullable', 'integer'],
            'envelope_number' => ['nullable', 'integer'],
            'newsletter_enabled' => ['nullable', 'boolean'],
            'deactivated_at' => ['nullable', 'date'],
        ];
    }
}
