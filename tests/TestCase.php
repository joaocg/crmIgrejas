<?php

namespace Tests;

use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Distinguishes the roles/users a single test creates, so two calls in the
     * same test do not collide on `roles.(tenant_id, slug)` or `users.email`.
     */
    private int $tenantUserSequence = 0;

    /**
     * Creates a tenant (unless one is given), a role carrying $permissions and
     * a user bound to both, then authenticates as that user on the `sanctum`
     * guard — the fixture every module's feature tests need before they can
     * touch an /api route.
     *
     * Pass an explicit $tenant to put a user in an existing tenant, e.g. to
     * build a cross-tenant scenario.
     *
     * @param  array<string, bool>  $permissions  Role permission map; the default grants everything.
     */
    protected function actingAsTenantUser(array $permissions = ['*' => true], ?Tenant $tenant = null): User
    {
        $tenant ??= $this->makeTenant();
        $sequence = ++$this->tenantUserSequence;

        $role = Role::create([
            'tenant_id' => $tenant->id,
            'slug' => 'operator-'.$sequence,
            'name' => 'Operator '.$sequence,
            'permissions' => $permissions,
            'is_system' => false,
            'active' => true,
        ]);

        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role_id' => $role->id,
            'email' => 'operator'.$sequence.'@church.local',
            'password' => 'password',
            'active' => true,
        ]);

        $this->actingAs($user, 'sanctum');

        return $user;
    }

    /**
     * Named makeTenant() rather than createTenant() on purpose: a dozen
     * feature tests still declare their own `private function createTenant()`,
     * and PHP forbids a subclass from narrowing an inherited protected method
     * to private.
     */
    protected function makeTenant(string $slug = 'default'): Tenant
    {
        $tenant = new Tenant;
        $tenant->slug = $slug;
        $tenant->name = ucfirst($slug).' Church';
        $tenant->locale = 'pt_BR';
        $tenant->timezone = 'America/Fortaleza';
        $tenant->active = true;
        $tenant->save();

        return $tenant;
    }
}
