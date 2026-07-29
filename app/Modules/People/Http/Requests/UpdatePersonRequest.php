<?php

declare(strict_types=1);

namespace App\Modules\People\Http\Requests;

final class UpdatePersonRequest extends PersonRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return $this->forUpdate($this->typeRules(), nonNullable: ['first_name', 'last_name', 'newsletter_enabled']);
    }
}
