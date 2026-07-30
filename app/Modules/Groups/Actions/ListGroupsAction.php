<?php

declare(strict_types=1);

namespace App\Modules\Groups\Actions;

use App\Models\Group;
use App\Modules\Groups\Http\Requests\ListGroupsRequest;
use App\Support\Database\LikeTermEscaper;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class ListGroupsAction
{
    /**
     * Legacy rule: the group list is a client-side DataTable whose global
     * search box (src/skin/js/group/GroupList.js:140-150, plus the type
     * dropdown at src/v2/templates/group/grouplist.php:134-140 which drives
     * that same search) matches only the columns marked searchable — "Group
     * Name" (GroupList.js:74-75, searchable by DataTables default) and "Group
     * Type" (GroupList.js:126-129, `searchable: true`). "Members" and "Group
     * Cart Status" are explicitly `searchable: false` (GroupList.js:94, :103).
     * The server-side search therefore covers `name` and `type`, nothing else.
     *
     * LIKE ? ESCAPE ? with LikeTermEscaper keeps a `%` or `_` typed into that
     * box a literal character instead of a wildcard.
     */
    public function execute(ListGroupsRequest $request): LengthAwarePaginator
    {
        return Group::query()
            ->with(Group::API_RELATIONS)
            ->withCount(Group::API_COUNTS)
            ->when(
                $request->searchTerm(),
                fn (Builder $query, string $term): Builder => $query->where(
                    function (Builder $query) use ($term): void {
                        $pattern = '%'.LikeTermEscaper::escape($term).'%';

                        $query
                            ->whereRaw('name LIKE ? ESCAPE ?', [$pattern, LikeTermEscaper::ESCAPE_CHARACTER])
                            ->orWhereRaw('type LIKE ? ESCAPE ?', [$pattern, LikeTermEscaper::ESCAPE_CHARACTER]);
                    }
                ),
            )
            ->orderBy($request->sortColumn(), $request->sortDirection())
            ->orderBy('id')
            ->paginate($request->perPage())
            ->withQueryString();
    }
}
