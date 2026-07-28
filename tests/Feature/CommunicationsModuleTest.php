<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunicationsModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_communications_route_requires_authentication(): void
    {
        $this->getJson('/api/communications')->assertUnauthorized();
    }

    public function test_whatsapp_integration_can_be_saved_per_tenant(): void
    {
        $tenant = $this->createTenant('default');
        $user = $this->createUser($tenant->id);

        $this->actingAs($user, 'sanctum')
            ->putJson('/api/communications/whatsapp', [
                'provider' => 'waha',
                'enabled' => true,
                'settings' => [
                    'base_url' => 'http://waha:3000',
                    'instance' => 'church',
                ],
            ])
            ->assertOk()
            ->assertJsonPath('provider', 'waha')
            ->assertJsonPath('enabled', true);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/communications/whatsapp')
            ->assertOk()
            ->assertJsonPath('provider', 'waha')
            ->assertJsonPath('settings.instance', 'church');
    }

    private function createTenant(string $slug): Tenant
    {
        $tenant = new Tenant();
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
        return User::factory()->create([
            'tenant_id' => $tenantId,
            'email' => 'admin+'.uniqid().'@church.local',
            'password' => 'password',
            'active' => true,
        ]);
    }
}
