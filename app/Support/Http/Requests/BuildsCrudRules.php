<?php

declare(strict_types=1);

namespace App\Support\Http\Requests;

/**
 * Shared mechanism for deriving a Store/Update rule pair from a single set
 * of field constraints, so a module only has to declare each field's
 * type/format rules once.
 *
 * A "store" request marks the given fields as required and everything else
 * as nullable. An "update" request marks every field as sometimes-present,
 * and nullable unless it is listed as non-nullable (fields that are
 * required on create, or fields whose semantics reject an explicit null,
 * stay non-nullable on update too).
 */
trait BuildsCrudRules
{
    /**
     * @param  array<string, array<int, mixed>>  $fieldRules  Field => type/format constraints (no presence modifiers).
     * @param  array<int, string>  $required  Fields that must be present and non-null.
     * @return array<string, array<int, mixed>>
     */
    protected function forCreate(array $fieldRules, array $required = []): array
    {
        $rules = [];

        foreach ($fieldRules as $field => $constraints) {
            $rules[$field] = in_array($field, $required, true)
                ? ['required', ...$constraints]
                : ['nullable', ...$constraints];
        }

        return $rules;
    }

    /**
     * @param  array<string, array<int, mixed>>  $fieldRules  Field => type/format constraints (no presence modifiers).
     * @param  array<int, string>  $nonNullable  Fields that, when present, must not be null.
     * @return array<string, array<int, mixed>>
     */
    protected function forUpdate(array $fieldRules, array $nonNullable = []): array
    {
        $rules = [];

        foreach ($fieldRules as $field => $constraints) {
            $rules[$field] = in_array($field, $nonNullable, true)
                ? ['sometimes', ...$constraints]
                : ['sometimes', 'nullable', ...$constraints];
        }

        return $rules;
    }
}
