<?php

declare(strict_types=1);

namespace App\Support\Validation;

use App\Support\Tenancy\TenantContext;
use Illuminate\Validation\Rules\Exists;

/**
 * Tenant-aware replacements for Laravel's validation rules that hit the
 * database directly.
 *
 * `Rule::exists()` builds its own query builder, so it bypasses the global
 * TenantScope that BelongsToTenant installs on Eloquent models. Without the
 * explicit tenant filter added here, a request could point a foreign key
 * (`family_id`, `address_id`, ...) at another tenant's row and pass
 * validation — the scope only hides those rows from reads, it does not stop
 * a write from referencing them.
 */
final class TenantRule
{
    public static function exists(string $table, string $column = 'id'): Exists
    {
        return (new Exists($table, $column))
            ->where('tenant_id', app(TenantContext::class)->id());
    }
}
