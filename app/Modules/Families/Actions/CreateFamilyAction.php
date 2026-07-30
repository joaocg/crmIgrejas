<?php

declare(strict_types=1);

namespace App\Modules\Families\Actions;

use App\Models\Family;

final class CreateFamilyAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data): Family
    {
        return Family::create($data);
    }
}
