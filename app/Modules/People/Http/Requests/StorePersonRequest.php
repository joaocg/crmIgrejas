<?php

declare(strict_types=1);

namespace App\Modules\People\Http\Requests;

final class StorePersonRequest extends PersonRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return $this->forCreate($this->typeRules(), required: ['first_name', 'last_name']);
    }
}
