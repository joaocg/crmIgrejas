<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantScopingTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_has_a_current_tenant_relation(): void
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

        $this->assertTrue($user->currentTenant->is($tenant));
    }

    public function test_module_registry_discovers_core_domain_modules(): void
    {
        $modules = collect(app(\App\Support\Modules\ModuleRegistry::class)->all())->keys()->all();

        $this->assertContains('People', $modules);
        $this->assertContains('Families', $modules);
        $this->assertContains('Groups', $modules);
        $this->assertContains('Events', $modules);
        $this->assertContains('Finance', $modules);
        $this->assertContains('Care', $modules);
        $this->assertContains('Communications', $modules);
    }
}
