<?php

namespace Tests\Feature;

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

        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'email' => 'admin@church.local',
            'password' => 'password',
            'active' => true,
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/navigation')
            ->assertOk()
            ->assertJsonPath('sections.0.key', 'main')
            ->assertJsonPath('sections.0.items.0.route', '/dashboard')
            ->assertJsonPath('sections.1.key', 'tools');
    }
}
