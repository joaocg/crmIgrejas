<?php

namespace Tests\Feature;

use App\Models\Family;
use App\Models\Person;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FamiliesModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_families_index_requires_authentication(): void
    {
        $this->getJson('/api/families')->assertUnauthorized();
    }

    public function test_families_crud_is_scoped_to_the_authenticated_tenant(): void
    {
        $tenant = $this->createTenant('default');
        $otherTenant = $this->createTenant('other');
        $user = $this->createUser($tenant->id);

        $family = Family::create([
            'tenant_id' => $tenant->id,
            'name' => 'Family One',
        ]);

        $otherFamily = Family::create([
            'tenant_id' => $otherTenant->id,
            'name' => 'Other Family',
        ]);

        Person::create([
            'tenant_id' => $tenant->id,
            'family_id' => $family->id,
            'first_name' => 'Joao',
            'last_name' => 'Coelho',
        ]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/families');

        $response
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $family->id);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/families', [
                'name' => 'New Family',
            ])
            ->assertCreated()
            ->assertJsonPath('tenant_id', $tenant->id);

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/families/{$otherFamily->id}")
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
