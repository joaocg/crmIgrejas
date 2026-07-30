<?php

declare(strict_types=1);

namespace Tests\Feature\Tenancy;

use App\Models\Address;
use App\Models\Family;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The global TenantScope only hides other tenants' rows from reads. It does
 * not stop a write from pointing a foreign key at one, because
 * `Rule::exists()` builds its own query builder outside Eloquent. These
 * tests pin the App\Support\Validation\TenantRule::exists() replacement that
 * closes that hole.
 */
class CrossTenantForeignKeyTest extends TestCase
{
    use RefreshDatabase;

    public function test_person_cannot_be_attached_to_another_tenants_family(): void
    {
        $tenant = $this->createTenant('default');
        $otherTenant = $this->createTenant('other');

        $otherFamily = Family::withoutGlobalScopes()->create([
            'tenant_id' => $otherTenant->id,
            'name' => 'Other Family',
        ]);

        $this->actingAs($this->createUser($tenant->id), 'sanctum');

        $this->postJson('/api/people', [
            'first_name' => 'Joao',
            'last_name' => 'Coelho',
            'family_id' => $otherFamily->id,
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('family_id');
    }

    public function test_family_cannot_be_attached_to_another_tenants_address(): void
    {
        $tenant = $this->createTenant('default');
        $otherTenant = $this->createTenant('other');

        $otherAddress = Address::withoutGlobalScopes()->create([
            'tenant_id' => $otherTenant->id,
            'line1' => 'Rua Dois, 200',
            'city' => 'Recife',
        ]);

        $this->actingAs($this->createUser($tenant->id), 'sanctum');

        $this->postJson('/api/families', [
            'name' => 'Coelho',
            'address_id' => $otherAddress->id,
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('address_id');
    }

    public function test_own_tenant_foreign_keys_are_still_accepted(): void
    {
        $tenant = $this->createTenant('default');

        $this->actingAs($this->createUser($tenant->id), 'sanctum');

        $address = Address::create([
            'line1' => 'Rua Um, 100',
            'city' => 'Fortaleza',
        ]);

        $this->postJson('/api/families', [
            'name' => 'Coelho',
            'address_id' => $address->id,
        ])->assertCreated();
    }

    private function createTenant(string $slug): Tenant
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

    private function createUser(int $tenantId): User
    {
        $role = Role::create([
            'tenant_id' => $tenantId,
            'slug' => 'admin',
            'name' => 'Admin',
            'permissions' => ['*' => true],
            'is_system' => false,
            'active' => true,
        ]);

        return User::factory()->create([
            'tenant_id' => $tenantId,
            'role_id' => $role->id,
            'email' => 'admin+'.uniqid().'@church.local',
            'password' => 'password',
            'active' => true,
        ]);
    }
}
