<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Person;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupsAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_listing_requires_the_navigation_ability(): void
    {
        $this->actingAsTenantUser(['navigation.finance' => true]);

        $this->getJson('/api/groups')->assertForbidden();
    }

    public function test_creating_requires_the_create_ability(): void
    {
        $this->actingAsTenantUser(['navigation.groups' => true]);

        $this->postJson('/api/groups', ['name' => 'Intercessao'])->assertForbidden();
    }

    public function test_creating_is_allowed_with_the_create_ability(): void
    {
        $this->actingAsTenantUser([
            'navigation.groups' => true,
            'groups.create' => true,
        ]);

        $this->postJson('/api/groups', ['name' => 'Intercessao'])->assertCreated();
    }

    public function test_updating_requires_the_update_ability(): void
    {
        $this->actingAsTenantUser(['navigation.groups' => true]);

        $group = Group::create(['name' => 'Intercessao']);

        $this->patchJson("/api/groups/{$group->id}", ['name' => 'Louvor'])->assertForbidden();
    }

    public function test_deleting_requires_the_delete_ability(): void
    {
        $this->actingAsTenantUser(['navigation.groups' => true]);

        $group = Group::create(['name' => 'Intercessao']);

        $this->deleteJson("/api/groups/{$group->id}")->assertForbidden();
    }

    public function test_attaching_a_member_requires_the_update_ability(): void
    {
        $this->actingAsTenantUser(['navigation.groups' => true]);

        $group = Group::create(['name' => 'Intercessao']);
        $person = Person::create(['first_name' => 'Joao', 'last_name' => 'Coelho']);

        $this->postJson("/api/groups/{$group->id}/members", ['person_id' => $person->id])
            ->assertForbidden();
    }

    public function test_detaching_a_member_requires_the_update_ability(): void
    {
        $this->actingAsTenantUser(['navigation.groups' => true]);

        $group = Group::create(['name' => 'Intercessao']);
        $person = Person::create(['first_name' => 'Joao', 'last_name' => 'Coelho']);

        $this->deleteJson("/api/groups/{$group->id}/members/{$person->id}")
            ->assertForbidden();
    }

    public function test_the_wildcard_permission_allows_everything(): void
    {
        $this->actingAsTenantUser(['*' => true]);

        $this->getJson('/api/groups')->assertOk();
    }

    public function test_a_user_with_no_role_at_all_is_denied(): void
    {
        $tenant = $this->makeTenant();

        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role_id' => null,
            'email' => 'norole@church.local',
            'password' => 'password',
            'active' => true,
        ]);

        $this->actingAs($user, 'sanctum');

        $this->getJson('/api/groups')->assertForbidden();
    }
}
