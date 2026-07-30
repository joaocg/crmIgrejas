<?php

declare(strict_types=1);

namespace App\Modules\Families\Http\Requests;

final class StoreFamilyRequest extends FamilyRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return $this->forCreate($this->typeRules(), required: ['name']);
    }
}
