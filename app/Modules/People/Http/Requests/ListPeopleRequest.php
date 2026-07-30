<?php

declare(strict_types=1);

namespace App\Modules\People\Http\Requests;

use App\Models\Person;
use App\Support\Http\Requests\IndexRequest;

final class ListPeopleRequest extends IndexRequest
{
    /**
     * Legacy rule: src/v2/templates/people/personlist.php:46-53 renders eight
     * columns, and lines 82-99 put six of them — Address, Home Phone, Cell
     * Phone, email, Created and Edited — inside the
     * isSeePrivacyDataEnabled() branch, replacing all six with "Private Data"
     * otherwise. Only Name (last_name) and First Name are visible to everyone.
     *
     * Address/phones/email are not offered as sort keys at all: they are
     * masked for unprivileged users and, for privileged ones, they live on
     * related tables the list does not join for ordering. Created and Edited
     * are offered, but only to users who could actually read them in the
     * legacy — sorting by a timestamp column the legacy replaced with the
     * literal string "Private Data" would order the list by data the user was
     * never allowed to see.
     *
     * @return array<int, string>
     */
    protected function sortableColumns(): array
    {
        $columns = ['first_name', 'last_name'];

        if ($this->user()?->role?->allows(Person::PRIVATE_DATA_ABILITY) ?? false) {
            $columns[] = 'created_at';
            $columns[] = 'updated_at';
        }

        return $columns;
    }

    protected function defaultSort(): string
    {
        return 'last_name';
    }
}
