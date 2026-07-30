<?php

declare(strict_types=1);

namespace App\Modules\Families\Http\Requests;

use App\Modules\Families\Http\Resources\FamilyResource;
use App\Support\Http\Requests\IndexRequest;

final class ListFamiliesRequest extends IndexRequest
{
    /**
     * Legacy rule: src/v2/templates/people/familylist.php:63-69 renders seven
     * columns, and lines 104-121 put six of them — Address, Home Phone, Cell
     * Phone, email, Created and Edited — inside the
     * isSeePrivacyDataEnabled() branch, replacing all six with "Private Data"
     * otherwise. Only Name is visible to everyone.
     *
     * The table carries class="data-table", which src/skin/js/DataTables.js
     * initialises with default options, so the legacy did let a user sort by
     * every column — but for an unprivileged user those six columns held the
     * literal string "Private Data", so the sort leaked nothing. Ordering by
     * the real created_at/updated_at is therefore gated on the same ability;
     * an unprivileged ?sort=created_at gets the standard 422 for an unknown
     * sort column. Wedding Date is never a column here at all.
     *
     * Address/phones/email are not offered as sort keys in either branch:
     * masked for unprivileged users, and for privileged ones the phone and
     * email values also exist on the related contacts table, so a sort would
     * have to pick one representation over the other.
     *
     * Mirrors ListPeopleRequest, whose legacy screen has the identical split.
     *
     * @return array<int, string>
     */
    protected function sortableColumns(): array
    {
        $columns = ['name'];

        if ($this->user()?->role?->allows(FamilyResource::PRIVATE_DATA_ABILITY) ?? false) {
            $columns[] = 'created_at';
            $columns[] = 'updated_at';
        }

        return $columns;
    }

    protected function defaultSort(): string
    {
        return 'name';
    }
}
