<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Group;
use App\Models\Person;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventsModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_event_attendance_routes_require_authentication(): void
    {
        $this->getJson('/api/events')->assertUnauthorized();
        $this->postJson('/api/events/1/attendance')->assertUnauthorized();
    }

    public function test_events_and_attendance_are_scoped_to_the_authenticated_tenant(): void
    {
        $tenant = $this->createTenant('default');
        $otherTenant = $this->createTenant('other');
        $user = $this->createUser($tenant->id);
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
        $event = Event::create([
            'tenant_id' => $tenant->id,
            'group_id' => $group->id,
            'title' => 'Prayer Night',
            'is_active' => true,
        ]);
        $otherEvent = Event::create([
            'tenant_id' => $otherTenant->id,
            'group_id' => $otherGroup->id,
            'title' => 'Other Event',
            'is_active' => true,
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/events')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $event->id);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/events', [
                'group_id' => $group->id,
                'title' => 'Youth Night',
            ])
            ->assertCreated()
            ->assertJsonPath('tenant_id', $tenant->id);

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/events/{$event->id}/attendance", [
                'person_id' => $person->id,
            ])
            ->assertCreated()
            ->assertJsonPath('tenant_id', $tenant->id);

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/events/{$event->id}/attendance", [
                'person_id' => $otherPerson->id,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['person_id']);

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/events/{$otherEvent->id}")
            ->assertNotFound();
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
