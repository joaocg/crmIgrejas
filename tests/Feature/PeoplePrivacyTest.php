<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Person;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeoplePrivacyTest extends TestCase
{
    use RefreshDatabase;

    public function test_private_data_is_masked_without_the_permission(): void
    {
        $person = $this->personForUserWithPermissions(['navigation.people' => true]);

        $this->getJson("/api/people/{$person->id}")
            ->assertOk()
            ->assertJsonPath('data.private_data_hidden', true)
            ->assertJsonPath('data.address', null)
            ->assertJsonPath('data.contacts', []);
    }

    public function test_private_data_is_visible_with_the_permission(): void
    {
        $person = $this->personForUserWithPermissions([
            'navigation.people' => true,
            'people.private_data.view' => true,
        ]);

        $this->getJson("/api/people/{$person->id}")
            ->assertOk()
            ->assertJsonPath('data.private_data_hidden', false)
            ->assertJsonPath('data.contacts.0.value', 'joao@example.com');
    }

    public function test_store_rejects_an_invalid_payload(): void
    {
        $this->personForUserWithPermissions(['navigation.people' => true]);

        $this->postJson('/api/people', ['last_name' => 'Silva'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('first_name');
    }

    public function test_index_contacts_key_is_present_and_empty_without_the_permission(): void
    {
        $this->personForUserWithPermissions(['navigation.people' => true]);

        $this->getJson('/api/people')
            ->assertOk()
            ->assertJsonStructure(['data' => [['contacts']]])
            ->assertJsonPath('data.0.contacts', []);
    }

    public function test_index_contacts_key_is_present_and_populated_with_the_permission(): void
    {
        $this->personForUserWithPermissions([
            'navigation.people' => true,
            'people.private_data.view' => true,
        ]);

        $this->getJson('/api/people')
            ->assertOk()
            ->assertJsonStructure(['data' => [['contacts']]])
            ->assertJsonPath('data.0.contacts.0.value', 'joao@example.com');
    }

    /**
     * @param  array<string, bool>  $permissions
     */
    private function personForUserWithPermissions(array $permissions): Person
    {
        $tenant = new Tenant;
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

        $address = Address::create([
            'tenant_id' => $tenant->id,
            'line1' => 'Rua Um, 100',
            'city' => 'Fortaleza',
        ]);

        $person = Person::create([
            'address_id' => $address->id,
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

        return $person;
    }
}
