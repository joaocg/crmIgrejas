<?php

declare(strict_types=1);

namespace App\Modules\Groups\Http\Requests;

final class StoreGroupRequest extends GroupRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return $this->forCreate($this->typeRules(), required: ['name']);
    }
}
