<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Family;
use App\Models\Person;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FamiliesModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_families_index_requires_authentication(): void
    {
        $this->getJson('/api/families')->assertUnauthorized();
    }

    public function test_families_crud_is_scoped_to_the_authenticated_tenant(): void
    {
        $tenant = $this->createTenant('default');
        $otherTenant = $this->createTenant('other');
        $user = $this->createUser($tenant->id);

        $family = Family::create([
            'tenant_id' => $tenant->id,
            'name' => 'Family One',
        ]);
        $family->contacts()->create([
            'tenant_id' => $tenant->id,
            'type' => 'mobile_phone',
            'label' => 'Mobile phone',
            'value' => '(85) 98888-0000',
            'is_primary' => true,
        ]);

        $otherFamily = Family::create([
            'tenant_id' => $otherTenant->id,
            'name' => 'Other Family',
        ]);

        Person::create([
            'tenant_id' => $tenant->id,
            'family_id' => $family->id,
            'first_name' => 'Joao',
            'last_name' => 'Coelho',
        ]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/families');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $family->id);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/families', [
                'name' => 'New Family',
            ])
            ->assertCreated()
            ->assertJsonPath('data.name', 'New Family');

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/families/{$otherFamily->id}")
            ->assertNotFound();

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/families/{$family->id}")
            ->assertOk()
            ->assertJsonPath('data.contacts.0.value', '(85) 98888-0000');
    }

    public function test_update_accepts_a_partial_payload_and_rejects_an_invalid_present_field(): void
    {
        $tenant = $this->createTenant('default');
        $user = $this->createUser($tenant->id);

        $family = Family::create([
            'tenant_id' => $tenant->id,
            'name' => 'Coelho',
            'wedding_date' => '2010-05-01',
        ]);

        $this->actingAs($user, 'sanctum')
            ->patchJson("/api/families/{$family->id}", ['name' => 'Coelho Silva'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Coelho Silva')
            ->assertJsonPath('data.wedding_date', '2010-05-01');

        // wedding_date is nullable on update, name is not.
        $this->actingAs($user, 'sanctum')
            ->patchJson("/api/families/{$family->id}", ['wedding_date' => null])
            ->assertOk()
            ->assertJsonPath('data.wedding_date', null);

        $this->actingAs($user, 'sanctum')
            ->patchJson("/api/families/{$family->id}", ['name' => null])
            ->assertStatus(422)
            ->assertJsonValidationErrors('name');
    }

    public function test_destroy_removes_the_family(): void
    {
        $tenant = $this->createTenant('default');
        $user = $this->createUser($tenant->id);

        $family = Family::create([
            'tenant_id' => $tenant->id,
            'name' => 'Coelho',
        ]);

        $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/families/{$family->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('families', ['id' => $family->id]);
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
