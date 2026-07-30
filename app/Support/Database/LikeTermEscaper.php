<?php

declare(strict_types=1);

namespace App\Support\Database;

/**
 * Escapes SQL LIKE wildcard characters (`%`, `_`) in a user-supplied search
 * term so it is matched literally instead of as a pattern — e.g. searching
 * "a_a" must not match "aXa". Pair the escaped term with the
 * `LIKE ? ESCAPE ?` construct, passing self::ESCAPE_CHARACTER as the escape
 * parameter. Works on both SQLite (test suite) and MySQL 8.4 (production).
 *
 * Extracted from the original ListPeopleAction implementation so every
 * module's search reuses the same escaping instead of redefining it.
 */
final class LikeTermEscaper
{
    public const ESCAPE_CHARACTER = '\\';

    public static function escape(string $term): string
    {
        return strtr($term, [
            self::ESCAPE_CHARACTER => self::ESCAPE_CHARACTER.self::ESCAPE_CHARACTER,
            '%' => self::ESCAPE_CHARACTER.'%',
            '_' => self::ESCAPE_CHARACTER.'_',
        ]);
    }
}
