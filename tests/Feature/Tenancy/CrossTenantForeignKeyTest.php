<?php

declare(strict_types=1);

namespace Tests\Feature\Tenancy;

use App\Models\Address;
use App\Models\Family;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The global TenantScope only hides other tenants' rows from reads. It does
 * not stop a write from pointing a foreign key at one, because
 * `Rule::exists()` builds its own query builder outside Eloquent. These
 * tests pin the App\Support\Validation\TenantRule::exists() replacement that
 * closes that hole.
 */
class CrossTenantForeignKeyTest extends TestCase
{
    use RefreshDatabase;

    public function test_person_cannot_be_attached_to_another_tenants_family(): void
    {
        $tenant = $this->makeTenant('default');
        $otherTenant = $this->makeTenant('other');

        $otherFamily = Family::withoutGlobalScopes()->create([
            'tenant_id' => $otherTenant->id,
            'name' => 'Other Family',
        ]);

        $this->actingAsTenantUser(tenant: $tenant);

        $this->postJson('/api/people', [
            'first_name' => 'Joao',
            'last_name' => 'Coelho',
            'family_id' => $otherFamily->id,
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('family_id');
    }

    public function test_family_cannot_be_attached_to_another_tenants_address(): void
    {
        $tenant = $this->makeTenant('default');
        $otherTenant = $this->makeTenant('other');

        $otherAddress = Address::withoutGlobalScopes()->create([
            'tenant_id' => $otherTenant->id,
            'line1' => 'Rua Dois, 200',
            'city' => 'Recife',
        ]);

        $this->actingAsTenantUser(tenant: $tenant);

        $this->postJson('/api/families', [
            'name' => 'Coelho',
            'address_id' => $otherAddress->id,
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('address_id');
    }

    public function test_own_tenant_foreign_keys_are_still_accepted(): void
    {
        $tenant = $this->makeTenant('default');

        $this->actingAsTenantUser(tenant: $tenant);

        $address = Address::create([
            'line1' => 'Rua Um, 100',
            'city' => 'Fortaleza',
        ]);

        $this->postJson('/api/families', [
            'name' => 'Coelho',
            'address_id' => $address->id,
        ])->assertCreated();
    }
}
