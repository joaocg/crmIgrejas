<?php

declare(strict_types=1);

namespace App\Support\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class IndexRequest extends FormRequest
{
    public const MAX_PER_PAGE = 100;

    public const DEFAULT_PER_PAGE = 25;

    /**
     * @return array<int, string>
     */
    abstract protected function sortableColumns(): array;

    abstract protected function defaultSort(): string;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:'.self::MAX_PER_PAGE],
            'sort' => ['sometimes', 'string', Rule::in($this->allowedSortValues())],
            'search' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }

    public function perPage(): int
    {
        return (int) $this->integer('per_page', self::DEFAULT_PER_PAGE);
    }

    public function searchTerm(): ?string
    {
        $term = trim((string) $this->query('search', ''));

        return $term === '' ? null : $term;
    }

    public function sortColumn(): string
    {
        return ltrim($this->sortValue(), '-');
    }

    public function sortDirection(): string
    {
        return str_starts_with($this->sortValue(), '-') ? 'desc' : 'asc';
    }

    private function sortValue(): string
    {
        $sort = (string) $this->query('sort', '');

        return $sort === '' ? $this->defaultSort() : $sort;
    }

    /**
     * @return array<int, string>
     */
    private function allowedSortValues(): array
    {
        $values = [];

        foreach ($this->sortableColumns() as $column) {
            $values[] = $column;
            $values[] = '-'.$column;
        }

        return $values;
    }
}
