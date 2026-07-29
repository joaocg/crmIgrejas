<?php

declare(strict_types=1);

namespace App\Modules\People\Actions;

use App\Models\Person;
use App\Modules\People\Http\Requests\ListPeopleRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class ListPeopleAction
{
    private const LIKE_ESCAPE_CHARACTER = '\\';

    public function execute(ListPeopleRequest $request): LengthAwarePaginator
    {
        $sortColumn = $request->sortColumn();

        $query = Person::query()
            ->with(Person::API_RELATIONS)
            ->when(
                $request->searchTerm(),
                fn (Builder $query, string $term): Builder => $query->where(
                    fn (Builder $inner): Builder => $inner
                        ->whereRaw(
                            'first_name LIKE ? ESCAPE ?',
                            ['%'.$this->escapeLikeTerm($term).'%', self::LIKE_ESCAPE_CHARACTER]
                        )
                        ->orWhereRaw(
                            'last_name LIKE ? ESCAPE ?',
                            ['%'.$this->escapeLikeTerm($term).'%', self::LIKE_ESCAPE_CHARACTER]
                        )
                ),
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

    private function escapeLikeTerm(string $term): string
    {
        return strtr($term, [
            self::LIKE_ESCAPE_CHARACTER => self::LIKE_ESCAPE_CHARACTER.self::LIKE_ESCAPE_CHARACTER,
            '%' => self::LIKE_ESCAPE_CHARACTER.'%',
            '_' => self::LIKE_ESCAPE_CHARACTER.'_',
        ]);
    }
}
