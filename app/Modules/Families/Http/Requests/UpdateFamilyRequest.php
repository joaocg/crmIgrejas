<?php

declare(strict_types=1);

namespace App\Modules\Families\Http\Requests;

final class UpdateFamilyRequest extends FamilyRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return $this->forUpdate($this->typeRules(), nonNullable: ['name', 'newsletter_enabled', 'canvass_allowed']);
    }
}
