<?php

declare(strict_types=1);

namespace App\Modules\People\Actions;

use App\Models\Person;
use App\Modules\People\Http\Requests\ListPeopleRequest;
use App\Support\Database\LikeTermEscaper;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class ListPeopleAction
{
    public function execute(ListPeopleRequest $request): LengthAwarePaginator
    {
        $sortColumn = $request->sortColumn();

        $query = Person::query()
            ->with(Person::API_RELATIONS)
            ->when(
                $request->searchTerm(),
                function (Builder $query, string $term): Builder {
                    $pattern = '%'.LikeTermEscaper::escape($term).'%';

                    return $query->where(
                        fn (Builder $inner): Builder => $inner
                            ->whereRaw('first_name LIKE ? ESCAPE ?', [$pattern, LikeTermEscaper::ESCAPE_CHARACTER])
                            ->orWhereRaw('last_name LIKE ? ESCAPE ?', [$pattern, LikeTermEscaper::ESCAPE_CHARACTER])
                    );
                },
            )
            ->orderBy($sortColumn, $request->sortDirection());

        if ($sortColumn !== 'first_name') {
            $query->orderBy('first_name');
        }

        return $query
            ->orderBy('id')
            ->paginate($request->perPage())
            ->withQueryString();
    }
}
