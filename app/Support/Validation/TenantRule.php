<?php

declare(strict_types=1);

namespace App\Support\Validation;

use App\Support\Tenancy\TenantContext;
use Illuminate\Validation\Rules\Exists;
use RuntimeException;

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
    /**
     * @throws RuntimeException when there is no tenant in context. Laravel
     *                          would otherwise stringify the null into
     *                          `where tenant_id = ''`, which matches nothing
     *                          and surfaces as an unexplainable 422 on a
     *                          perfectly valid id.
     */
    public static function exists(string $table, string $column = 'id'): Exists
    {
        $tenantId = app(TenantContext::class)->id();

        if ($tenantId === null) {
            throw new RuntimeException(
                "Cannot build a tenant-scoped exists rule for [{$table}]: no tenant in context. "
                .'Validation using TenantRule must run inside an authenticated request or a '
                .'TenantContext::runAs() envelope.'
            );
        }

        return (new Exists($table, $column))->where('tenant_id', $tenantId);
    }
}
