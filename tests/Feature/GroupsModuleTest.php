<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Person;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupsModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_group_membership_routes_require_authentication(): void
    {
        $this->getJson('/api/groups')->assertUnauthorized();
        $this->postJson('/api/groups/1/members')->assertUnauthorized();
    }

    public function test_groups_and_memberships_are_scoped_to_the_authenticated_tenant(): void
    {
        $tenant = $this->makeTenant('default');
        $otherTenant = $this->makeTenant('other');

        $group = Group::create([
            'tenant_id' => $tenant->id,
            'name' => 'Intercession',
            'is_active' => true,
        ]);

        $otherGroup = Group::create([
            'tenant_id' => $otherTenant->id,
            'name' => 'Other',
            'is_active' => true,
        ]);

        $person = Person::create([
            'tenant_id' => $tenant->id,
            'first_name' => 'Joao',
            'last_name' => 'Coelho',
        ]);

        $otherPerson = Person::create([
            'tenant_id' => $otherTenant->id,
            'first_name' => 'Other',
            'last_name' => 'Person',
        ]);

        $this->actingAsTenantUser(tenant: $tenant);

        $this->getJson('/api/groups')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $group->id);

        $this->postJson('/api/groups', ['name' => 'New Group'])
            ->assertCreated()
            ->assertJsonPath('data.name', 'New Group');

        $this->postJson("/api/groups/{$group->id}/members", ['person_id' => $person->id])
            ->assertCreated();

        $this->assertDatabaseHas('group_memberships', [
            'group_id' => $group->id,
            'person_id' => $person->id,
            'tenant_id' => $tenant->id,
        ]);

        $this->postJson("/api/groups/{$group->id}/members", ['person_id' => $otherPerson->id])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['person_id']);

        $this->getJson("/api/groups/{$otherGroup->id}")->assertNotFound();
    }

    public function test_a_group_cannot_be_attached_to_another_tenants_role(): void
    {
        $tenant = $this->makeTenant('default');
        $otherTenant = $this->makeTenant('other');

        $otherRole = Role::create([
            'tenant_id' => $otherTenant->id,
            'slug' => 'foreign',
            'name' => 'Foreign',
            'permissions' => [],
            'is_system' => false,
            'active' => true,
        ]);

        $this->actingAsTenantUser(tenant: $tenant);

        $this->postJson('/api/groups', [
            'name' => 'Intercessao',
            'role_id' => $otherRole->id,
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('role_id');
    }

    public function test_a_membership_cannot_be_attached_to_another_tenants_role(): void
    {
        $tenant = $this->makeTenant('default');
        $otherTenant = $this->makeTenant('other');

        $otherRole = Role::create([
            'tenant_id' => $otherTenant->id,
            'slug' => 'foreign',
            'name' => 'Foreign',
            'permissions' => [],
            'is_system' => false,
            'active' => true,
        ]);

        $this->actingAsTenantUser(tenant: $tenant);

        $group = Group::create(['name' => 'Intercessao']);
        $person = Person::create(['first_name' => 'Joao', 'last_name' => 'Coelho']);

        $this->postJson("/api/groups/{$group->id}/members", [
            'person_id' => $person->id,
            'role_id' => $otherRole->id,
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('role_id');
    }

    public function test_the_members_and_count_keys_are_always_present(): void
    {
        $this->actingAsTenantUser();

        $group = Group::create(['name' => 'Intercessao']);

        $this->getJson('/api/groups')
            ->assertOk()
            ->assertJsonStructure(['data' => [['members', 'members_count']]])
            ->assertJsonPath('data.0.members', [])
            ->assertJsonPath('data.0.members_count', 0);

        $this->getJson("/api/groups/{$group->id}")
            ->assertOk()
            ->assertJsonStructure(['data' => ['members', 'members_count']])
            ->assertJsonPath('data.members', [])
            ->assertJsonPath('data.members_count', 0);

        $this->postJson('/api/groups', ['name' => 'Louvor'])
            ->assertCreated()
            ->assertJsonStructure(['data' => ['members', 'members_count']]);

        $this->patchJson("/api/groups/{$group->id}", ['name' => 'Adoracao'])
            ->assertOk()
            ->assertJsonStructure(['data' => ['members', 'members_count']]);
    }

    public function test_show_exposes_the_members_with_their_person(): void
    {
        $this->actingAsTenantUser();

        $group = Group::create(['name' => 'Intercessao']);
        $person = Person::create(['first_name' => 'Joao', 'last_name' => 'Coelho']);
        $group->memberships()->create(['person_id' => $person->id, 'is_manager' => true]);

        $this->getJson("/api/groups/{$group->id}")
            ->assertOk()
            ->assertJsonPath('data.members_count', 1)
            ->assertJsonPath('data.members.0.person.first_name', 'Joao')
            ->assertJsonPath('data.members.0.is_manager', true);
    }

    public function test_update_accepts_a_partial_payload_and_rejects_an_invalid_present_field(): void
    {
        $this->actingAsTenantUser();

        $group = Group::create([
            'name' => 'Intercessao',
            'description' => 'Reuniao semanal',
        ]);

        $this->patchJson("/api/groups/{$group->id}", ['name' => 'Adoracao'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Adoracao')
            ->assertJsonPath('data.description', 'Reuniao semanal');

        // description is nullable on update, name and the booleans are not.
        $this->patchJson("/api/groups/{$group->id}", ['description' => null])
            ->assertOk()
            ->assertJsonPath('data.description', null);

        $this->patchJson("/api/groups/{$group->id}", ['name' => null])
            ->assertStatus(422)
            ->assertJsonValidationErrors('name');

        $this->patchJson("/api/groups/{$group->id}", ['is_active' => null])
            ->assertStatus(422)
            ->assertJsonValidationErrors('is_active');
    }

    public function test_store_rejects_an_invalid_payload(): void
    {
        $this->actingAsTenantUser();

        $this->postJson('/api/groups', ['is_active' => 'maybe'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'is_active']);
    }

    public function test_destroy_removes_the_group(): void
    {
        $this->actingAsTenantUser();

        $group = Group::create(['name' => 'Intercessao']);

        $this->deleteJson("/api/groups/{$group->id}")->assertNoContent();

        $this->assertDatabaseMissing('groups', ['id' => $group->id]);
    }

    public function test_a_member_can_be_detached(): void
    {
        $this->actingAsTenantUser();

        $group = Group::create(['name' => 'Intercessao']);
        $person = Person::create(['first_name' => 'Joao', 'last_name' => 'Coelho']);
        $group->memberships()->create(['person_id' => $person->id]);

        $this->deleteJson("/api/groups/{$group->id}/members/{$person->id}")->assertNoContent();

        $this->assertDatabaseMissing('group_memberships', [
            'group_id' => $group->id,
            'person_id' => $person->id,
        ]);
    }
}
