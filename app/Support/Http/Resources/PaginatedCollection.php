<?php

declare(strict_types=1);

namespace App\Support\Http\Resources;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class PaginatedCollection
{
    /**
     * @return array{data: array<int, mixed>, meta: array<string, int>}
     */
    public static function envelope(LengthAwarePaginator $paginator): array
    {
        return [
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ];
    }
}
