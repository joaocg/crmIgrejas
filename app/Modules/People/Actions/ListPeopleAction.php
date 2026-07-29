<?php

declare(strict_types=1);

namespace App\Modules\People\Actions;

use App\Models\Person;
use App\Modules\People\Http\Requests\ListPeopleRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class ListPeopleAction
{
    public function execute(ListPeopleRequest $request): LengthAwarePaginator
    {
        return Person::query()
            ->with(['family', 'address'])
            ->when(
                $request->searchTerm(),
                fn (Builder $query, string $term): Builder => $query->where(
                    fn (Builder $inner): Builder => $inner
                        ->where('first_name', 'like', '%'.$term.'%')
                        ->orWhere('last_name', 'like', '%'.$term.'%')
                ),
            )
            ->orderBy($request->sortColumn(), $request->sortDirection())
            ->orderBy('id')
            ->paginate($request->perPage())
            ->withQueryString();
    }
}
