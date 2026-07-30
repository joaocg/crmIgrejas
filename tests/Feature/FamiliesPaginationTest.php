<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Family;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FamiliesPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_a_paginated_envelope(): void
    {
        $this->authenticate();

        for ($index = 1; $index <= 30; $index++) {
            Family::create(['name' => 'Family '.str_pad((string) $index, 2, '0', STR_PAD_LEFT)]);
        }

        $this->getJson('/api/families?per_page=10')
            ->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.total', 30)
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.last_page', 3);
    }

    public function test_index_filters_by_search_term(): void
    {
        $this->authenticate();

        Family::create(['name' => 'Coelho']);
        Family::create(['name' => 'Silva']);

        $this->getJson('/api/families?search=coel')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Coelho');
    }

    public function test_index_search_term_does_not_treat_underscore_as_a_wildcard(): void
    {
        $this->authenticate();

        Family::create(['name' => 'Coelho']);
        Family::create(['name' => 'Co_lho']);

        $this->getJson('/api/families?search=Co_lho')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Co_lho');
    }

    public function test_index_sorts_by_an_allowed_column_in_both_directions(): void
    {
        $this->authenticate();

        Family::create(['name' => 'Zebra']);
        Family::create(['name' => 'Alves']);

        $this->getJson('/api/families?sort=name')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Alves');

        $this->getJson('/api/families?sort=-name')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Zebra');
    }

    public function test_index_rejects_an_unknown_sort_column(): void
    {
        $this->authenticate();

        $this->getJson('/api/families?sort=tenant_id')
            ->assertStatus(422)
            ->assertJsonValidationErrors('sort');
    }

    public function test_index_caps_the_page_size(): void
    {
        $this->authenticate();

        $this->getJson('/api/families?per_page=5000')
            ->assertStatus(422)
            ->assertJsonValidationErrors('per_page');
    }

    public function test_timestamp_sorting_requires_the_private_data_ability(): void
    {
        $this->authenticate(['navigation.families' => true]);

        $this->getJson('/api/families?sort=created_at')
            ->assertStatus(422)
            ->assertJsonValidationErrors('sort');

        $this->getJson('/api/families?sort=-updated_at')
            ->assertStatus(422)
            ->assertJsonValidationErrors('sort');
    }

    public function test_timestamp_sorting_is_allowed_with_the_private_data_ability(): void
    {
        $this->authenticate([
            'navigation.families' => true,
            'families.private_data.view' => true,
        ]);

        Family::create(['name' => 'Alves']);

        $this->getJson('/api/families?sort=created_at')->assertOk();
        $this->getJson('/api/families?sort=-updated_at')->assertOk();
    }

    /**
     * @param  array<string, bool>  $permissions
     */
    private function authenticate(array $permissions = ['*' => true]): User
    {
        $tenant = new Tenant;
        $tenant->slug = 'default';
        $tenant->name = 'Default Church';
        $tenant->locale = 'pt_BR';
        $tenant->timezone = 'America/Fortaleza';
        $tenant->active = true;
        $tenant->save();

        $role = Role::create([
            'tenant_id' => $tenant->id,
            'slug' => 'operator',
            'name' => 'Operator',
            'permissions' => $permissions,
            'is_system' => false,
            'active' => true,
        ]);

        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role_id' => $role->id,
            'email' => 'operator+'.uniqid().'@church.local',
            'password' => 'password',
            'active' => true,
        ]);

        $this->actingAs($user, 'sanctum');

        return $user;
    }
}
