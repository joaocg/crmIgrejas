<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Family;
use App\Models\Group;
use App\Models\Person;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KioskModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_kiosk_route_requires_authentication(): void
    {
        $this->getJson('/api/kiosk')->assertUnauthorized();
    }

    public function test_kiosk_route_returns_tenant_summary(): void
    {
        $tenant = $this->createTenant('default');
        $otherTenant = $this->createTenant('other');
        $user = $this->createUser($tenant->id);

        Person::create([
            'tenant_id' => $tenant->id,
            'first_name' => 'Joao',
            'last_name' => 'Coelho',
        ]);

        Family::create([
            'tenant_id' => $tenant->id,
            'name' => 'Family One',
        ]);

        Group::create([
            'tenant_id' => $tenant->id,
            'name' => 'Group One',
            'is_active' => true,
        ]);

        Event::create([
            'tenant_id' => $tenant->id,
            'title' => 'Event One',
            'starts_at' => now()->addDay(),
            'is_active' => true,
        ]);

        Person::create([
            'tenant_id' => $otherTenant->id,
            'first_name' => 'Other',
            'last_name' => 'Person',
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/kiosk')
            ->assertOk()
            ->assertJsonPath('module', 'kiosk')
            ->assertJsonPath('summary.people', 1)
            ->assertJsonPath('summary.families', 1)
            ->assertJsonPath('summary.groups', 1)
            ->assertJsonPath('summary.events', 1);
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
