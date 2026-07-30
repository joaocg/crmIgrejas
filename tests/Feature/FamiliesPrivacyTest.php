<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Family;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FamiliesPrivacyTest extends TestCase
{
    use RefreshDatabase;

    public function test_private_data_is_masked_without_the_permission(): void
    {
        $family = $this->familyForUserWithPermissions(['navigation.families' => true]);

        $this->getJson("/api/families/{$family->id}")
            ->assertOk()
            ->assertJsonPath('data.private_data_hidden', true)
            ->assertJsonPath('data.email', null)
            ->assertJsonPath('data.home_phone', null)
            ->assertJsonPath('data.work_phone', null)
            ->assertJsonPath('data.mobile_phone', null)
            ->assertJsonPath('data.address', null);
    }

    public function test_private_data_is_visible_with_the_permission(): void
    {
        $family = $this->familyForUserWithPermissions([
            'navigation.families' => true,
            'families.private_data.view' => true,
        ]);

        $this->getJson("/api/families/{$family->id}")
            ->assertOk()
            ->assertJsonPath('data.private_data_hidden', false)
            ->assertJsonPath('data.email', 'family@example.com')
            ->assertJsonPath('data.home_phone', '(85) 3000-0000')
            ->assertJsonPath('data.address.city', 'Fortaleza');
    }

    public function test_contacts_are_masked_without_the_permission(): void
    {
        $family = $this->familyForUserWithPermissions(['navigation.families' => true]);
        $family->contacts()->create([
            'type' => 'home_phone',
            'label' => 'Home phone',
            'value' => '(85) 3000-0000',
            'is_primary' => true,
        ]);

        $this->getJson("/api/families/{$family->id}")
            ->assertOk()
            ->assertJsonPath('data.contacts', []);
    }

    public function test_contacts_are_visible_with_the_permission(): void
    {
        $family = $this->familyForUserWithPermissions([
            'navigation.families' => true,
            'families.private_data.view' => true,
        ]);
        $family->contacts()->create([
            'type' => 'home_phone',
            'label' => 'Home phone',
            'value' => '(85) 3000-0000',
            'is_primary' => true,
        ]);

        $this->getJson("/api/families/{$family->id}")
            ->assertOk()
            ->assertJsonPath('data.contacts.0.value', '(85) 3000-0000');
    }

    public function test_store_rejects_an_invalid_payload(): void
    {
        $this->familyForUserWithPermissions([
            'navigation.families' => true,
            'families.create' => true,
        ]);

        $this->postJson('/api/families', ['wedding_date' => 'not-a-date'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'wedding_date']);
    }

    public function test_index_address_key_is_present_and_null_without_the_permission(): void
    {
        $this->familyForUserWithPermissions(['navigation.families' => true]);

        $this->getJson('/api/families')
            ->assertOk()
            ->assertJsonStructure(['data' => [['address']]])
            ->assertJsonPath('data.0.address', null);
    }

    public function test_index_address_key_is_present_and_populated_with_the_permission(): void
    {
        $this->familyForUserWithPermissions([
            'navigation.families' => true,
            'families.private_data.view' => true,
        ]);

        $this->getJson('/api/families')
            ->assertOk()
            ->assertJsonStructure(['data' => [['address']]])
            ->assertJsonPath('data.0.address.city', 'Fortaleza');
    }

    /**
     * @param  array<string, bool>  $permissions
     */
    private function familyForUserWithPermissions(array $permissions): Family
    {
        $this->actingAsTenantUser($permissions);

        $address = Address::create([
            'line1' => 'Rua Um, 100',
            'city' => 'Fortaleza',
        ]);

        return Family::create([
            'address_id' => $address->id,
            'name' => 'Coelho',
            'email' => 'family@example.com',
            'home_phone' => '(85) 3000-0000',
        ]);
    }
}
