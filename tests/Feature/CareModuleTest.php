<?php

namespace Tests\Feature;

use App\Models\Family;
use App\Models\Note;
use App\Models\PastoralCareRecord;
use App\Models\Person;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CareModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_care_routes_require_authentication(): void
    {
        $this->getJson('/api/notes')->assertUnauthorized();
        $this->getJson('/api/pastoral-care')->assertUnauthorized();
    }

    public function test_notes_and_pastoral_care_are_scoped_to_the_authenticated_tenant(): void
    {
        $tenant = $this->createTenant('default');
        $otherTenant = $this->createTenant('other');
        $user = $this->createUser($tenant->id);
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
        $family = Family::create([
            'tenant_id' => $tenant->id,
            'name' => 'Family One',
        ]);
        $otherFamily = Family::create([
            'tenant_id' => $otherTenant->id,
            'name' => 'Other Family',
        ]);
        $note = Note::create([
            'tenant_id' => $tenant->id,
            'person_id' => $person->id,
            'title' => 'Visit',
        ]);
        $otherNote = Note::create([
            'tenant_id' => $otherTenant->id,
            'person_id' => $otherPerson->id,
            'title' => 'Other visit',
        ]);
        $care = PastoralCareRecord::create([
            'tenant_id' => $tenant->id,
            'person_id' => $person->id,
            'body' => 'Prayed together',
        ]);
        $otherCare = PastoralCareRecord::create([
            'tenant_id' => $otherTenant->id,
            'person_id' => $otherPerson->id,
            'body' => 'Other care',
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/notes')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $note->id);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/notes', [
                'person_id' => $person->id,
                'title' => 'New note',
            ])
            ->assertCreated()
            ->assertJsonPath('tenant_id', $tenant->id);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/pastoral-care')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $care->id);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/pastoral-care', [
                'person_id' => $person->id,
                'body' => 'New care record',
            ])
            ->assertCreated()
            ->assertJsonPath('tenant_id', $tenant->id);

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/notes/{$otherNote->id}")
            ->assertNotFound();

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/pastoral-care/{$otherCare->id}")
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
