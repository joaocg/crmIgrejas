<?php

declare(strict_types=1);

namespace App\Modules\People\Http\Requests;

use App\Support\Http\Requests\IndexRequest;

final class ListPeopleRequest extends IndexRequest
{
    /**
     * @return array<int, string>
     */
    protected function sortableColumns(): array
    {
        return ['first_name', 'last_name', 'birth_date', 'membership_date', 'created_at'];
    }

    protected function defaultSort(): string
    {
        return 'last_name';
    }
}
