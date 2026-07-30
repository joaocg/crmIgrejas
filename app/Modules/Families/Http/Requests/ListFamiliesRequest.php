<?php

declare(strict_types=1);

namespace App\Modules\Families\Http\Requests;

use App\Support\Http\Requests\IndexRequest;

final class ListFamiliesRequest extends IndexRequest
{
    /**
     * Legacy rule: src/v2/templates/people/familylist.php only displays and
     * DataTable-sorts Name, Created and Edited columns for families
     * (Address/Home Phone/Cell Phone/email are masked private data, not
     * sortable columns; Wedding Date is never shown in the list at all).
     * Mirrors the owner decision that trimmed ListPeopleRequest to columns
     * the legacy person list actually displayed/sorted.
     *
     * @return array<int, string>
     */
    protected function sortableColumns(): array
    {
        return ['name', 'created_at', 'updated_at'];
    }

    protected function defaultSort(): string
    {
        return 'name';
    }
}
