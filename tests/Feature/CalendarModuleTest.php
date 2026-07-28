<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Group;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalendarModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_calendar_route_requires_authentication(): void
    {
        $this->getJson('/api/calendar')->assertUnauthorized();
    }

    public function test_calendar_route_returns_tenant_events(): void
    {
        $tenant = $this->createTenant('default');
        $otherTenant = $this->createTenant('other');
        $user = $this->createUser($tenant->id);
        $group = Group::create([
            'tenant_id' => $tenant->id,
            'name' => 'Calendar Group',
            'is_active' => true,
        ]);
        $otherGroup = Group::create([
            'tenant_id' => $otherTenant->id,
            'name' => 'Other Group',
            'is_active' => true,
        ]);
        $event = Event::create([
            'tenant_id' => $tenant->id,
            'group_id' => $group->id,
            'title' => 'Prayer Night',
            'starts_at' => now()->addDay(),
            'is_active' => true,
        ]);
        $otherEvent = Event::create([
            'tenant_id' => $otherTenant->id,
            'group_id' => $otherGroup->id,
            'title' => 'Other Event',
            'starts_at' => now()->addDay(),
            'is_active' => true,
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/calendar')
            ->assertOk()
            ->assertJsonPath('module', 'calendar')
            ->assertJsonPath('events.0.id', $event->id)
            ->assertJsonMissing(['id' => $otherEvent->id]);
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
