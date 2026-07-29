<?php

namespace Tests\Feature;

use App\Models\Family;
use App\Models\Person;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeopleModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_people_index_requires_authentication(): void
    {
        $this->getJson('/api/people')->assertUnauthorized();
    }

    public function test_people_crud_is_scoped_to_the_authenticated_tenant(): void
    {
        $tenant = $this->createTenant('default');
        $otherTenant = $this->createTenant('other');
        $user = $this->createUser($tenant->id);
        $family = Family::create([
            'tenant_id' => $tenant->id,
            'name' => 'Family One',
        ]);
        $otherFamily = Family::create([
            'tenant_id' => $otherTenant->id,
            'name' => 'Other Family',
        ]);

        $person = Person::create([
            'tenant_id' => $tenant->id,
            'family_id' => $family->id,
            'first_name' => 'Joao',
            'last_name' => 'Coelho',
        ]);
        $person->contacts()->create([
            'tenant_id' => $tenant->id,
            'type' => 'email',
            'label' => 'Email',
            'value' => 'joao@example.com',
            'is_primary' => true,
        ]);

        $otherPerson = Person::create([
            'tenant_id' => $otherTenant->id,
            'family_id' => $otherFamily->id,
            'first_name' => 'Other',
            'last_name' => 'Person',
        ]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/people');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $person->id);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/people', [
                'family_id' => $family->id,
                'first_name' => 'Maria',
                'last_name' => 'Silva',
            ])
            ->assertCreated()
            ->assertJsonPath('data.first_name', 'Maria');

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/people/{$otherPerson->id}")
            ->assertNotFound();

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/people/{$person->id}")
            ->assertOk()
            ->assertJsonPath('data.contacts.0.value', 'joao@example.com');
    }

    public function test_update_accepts_a_partial_payload_and_rejects_an_invalid_present_field(): void
    {
        $tenant = $this->createTenant('default');
        $user = $this->createUser($tenant->id);
        $person = Person::create([
            'tenant_id' => $tenant->id,
            'first_name' => 'Joao',
            'last_name' => 'Coelho',
        ]);

        $this->actingAs($user, 'sanctum')
            ->patchJson("/api/people/{$person->id}", ['title' => 'Pastor'])
            ->assertOk()
            ->assertJsonPath('data.title', 'Pastor')
            ->assertJsonPath('data.first_name', 'Joao')
            ->assertJsonPath('data.last_name', 'Coelho');

        $this->actingAs($user, 'sanctum')
            ->patchJson("/api/people/{$person->id}", ['gender' => 'not-an-integer'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('gender');
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
