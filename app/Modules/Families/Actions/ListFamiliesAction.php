<?php

declare(strict_types=1);

namespace App\Modules\Families\Actions;

use App\Models\Family;
use App\Modules\Families\Http\Requests\ListFamiliesRequest;
use App\Support\Database\LikeTermEscaper;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class ListFamiliesAction
{
    public function execute(ListFamiliesRequest $request): LengthAwarePaginator
    {
        return Family::query()
            ->with(Family::API_RELATIONS)
            ->when(
                $request->searchTerm(),
                fn (Builder $query, string $term): Builder => $query->whereRaw(
                    'name LIKE ? ESCAPE ?',
                    ['%'.LikeTermEscaper::escape($term).'%', LikeTermEscaper::ESCAPE_CHARACTER]
                ),
            )
            ->orderBy($request->sortColumn(), $request->sortDirection())
            ->orderBy('id')
            ->paginate($request->perPage())
            ->withQueryString();
    }
}
