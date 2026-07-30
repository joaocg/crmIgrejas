<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Family;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FamiliesAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_listing_requires_the_navigation_ability(): void
    {
        $this->authenticateWith(['navigation.finance' => true]);

        $this->getJson('/api/families')->assertForbidden();
    }

    public function test_creating_requires_the_create_ability(): void
    {
        $this->authenticateWith(['navigation.families' => true]);

        $this->postJson('/api/families', ['name' => 'Nova'])->assertForbidden();
    }

    public function test_creating_is_allowed_with_the_create_ability(): void
    {
        $this->authenticateWith([
            'navigation.families' => true,
            'families.create' => true,
        ]);

        $this->postJson('/api/families', ['name' => 'Nova'])->assertCreated();
    }

    public function test_deleting_requires_the_delete_ability(): void
    {
        $this->authenticateWith(['navigation.families' => true]);

        $family = Family::create(['name' => 'Coelho']);

        $this->deleteJson("/api/families/{$family->id}")->assertForbidden();
    }

    public function test_the_wildcard_permission_allows_everything(): void
    {
        $this->authenticateWith(['*' => true]);

        $this->getJson('/api/families')->assertOk();
    }

    public function test_a_user_with_no_role_at_all_is_denied(): void
    {
        $tenant = new Tenant();
        $tenant->slug = 'default';
        $tenant->name = 'Default Church';
        $tenant->locale = 'pt_BR';
        $tenant->timezone = 'America/Fortaleza';
        $tenant->active = true;
        $tenant->save();

        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role_id' => null,
            'email' => 'norole+'.uniqid().'@church.local',
            'password' => 'password',
            'active' => true,
        ]);

        $this->actingAs($user, 'sanctum');

        $this->getJson('/api/families')->assertForbidden();
    }

    /**
     * @param  array<string, bool>  $permissions
     */
    private function authenticateWith(array $permissions): User
    {
        $tenant = new Tenant();
        $tenant->slug = 'default';
        $tenant->name = 'Default Church';
        $tenant->locale = 'pt_BR';
        $tenant->timezone = 'America/Fortaleza';
        $tenant->active = true;
        $tenant->save();

        $role = Role::create([
            'tenant_id' => $tenant->id,
            'slug' => 'operator',
            'name' => 'Operator',
            'permissions' => $permissions,
            'is_system' => false,
            'active' => true,
        ]);

        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role_id' => $role->id,
            'email' => 'operator+'.uniqid().'@church.local',
            'password' => 'password',
            'active' => true,
        ]);

        $this->actingAs($user, 'sanctum');

        return $user;
    }
}
