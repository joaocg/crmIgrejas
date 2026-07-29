<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LocalePreferenceTest extends TestCase
{
    use RefreshDatabase;

    private function createTenantAndUser(string $locale = 'pt_BR'): User
    {
        $tenantId = DB::table('tenants')->insertGetId([
            'slug' => 'default',
            'name' => 'Default Church',
            'locale' => 'pt_BR',
            'timezone' => 'America/Fortaleza',
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return User::factory()->create([
            'tenant_id' => $tenantId,
            'role_id' => null,
            'name' => 'Admin',
            'email' => 'admin@church.local',
            'locale' => $locale,
            'password' => 'password',
            'active' => true,
        ]);
    }

    public function test_guest_uses_pt_br_default_locale(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('lang="pt-BR"', false)
            ->assertSee('window.__APP_LOCALE__ = "pt-BR";', false);
    }

    public function test_authenticated_user_locale_overrides_the_default(): void
    {
        $user = $this->createTenantAndUser('en');

        $this->actingAs($user)
            ->get('/')
            ->assertOk()
            ->assertSee('lang="en"', false)
            ->assertSee('window.__APP_LOCALE__ = "en";', false);
    }
}
