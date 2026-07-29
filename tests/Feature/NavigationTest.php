<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_navigation_route_requires_authentication(): void
    {
        $this->getJson('/api/navigation')->assertUnauthorized();
    }

    public function test_navigation_route_returns_menu_sections_for_authenticated_users(): void
    {
        $tenant = Tenant::create([
            'slug' => 'default',
            'name' => 'Default Church',
            'locale' => 'pt_BR',
            'timezone' => 'America/Fortaleza',
            'active' => true,
        ]);

        $role = Role::create([
            'tenant_id' => $tenant->id,
            'slug' => 'admin',
            'name' => 'Admin',
            'description' => 'Administrator',
            'permissions' => ['*' => true],
            'is_system' => true,
            'active' => true,
        ]);

        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role_id' => $role->id,
            'email' => 'admin@church.local',
            'password' => 'password',
            'active' => true,
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/navigation')
            ->assertOk()
            ->assertJsonPath('sections.0.key', 'main')
            ->assertJsonPath('sections.0.items.0.route', '/dashboard')
            ->assertJsonPath('sections.1.key', 'tools')
            ->assertJsonPath('sections.1.items.1.route', '/care');
    }

    public function test_navigation_is_filtered_by_role_permissions(): void
    {
        $tenant = Tenant::create([
            'slug' => 'restricted',
            'name' => 'Restricted Church',
            'locale' => 'pt_BR',
            'timezone' => 'America/Fortaleza',
            'active' => true,
        ]);

        $role = Role::create([
            'tenant_id' => $tenant->id,
            'slug' => 'viewer',
            'name' => 'Viewer',
            'description' => 'Limited access',
            'permissions' => [
                'navigation.people' => true,
                'navigation.groups' => true,
            ],
            'is_system' => false,
            'active' => true,
        ]);

        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role_id' => $role->id,
            'email' => 'viewer@church.local',
            'password' => 'password',
            'active' => true,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/navigation')
            ->assertOk();

        $response->assertJsonMissing([
            'key' => 'users',
        ]);

        $response->assertJsonPath('sections.0.items.0.key', 'dashboard');
        $response->assertJsonPath('sections.0.items.1.key', 'people');
        $response->assertJsonPath('sections.0.items.2.key', 'groups');
    }
}
