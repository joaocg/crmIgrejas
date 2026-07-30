<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Family;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FamiliesPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_a_paginated_envelope(): void
    {
        $this->actingAsTenantUser();

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
        $this->actingAsTenantUser();

        Family::create(['name' => 'Coelho']);
        Family::create(['name' => 'Silva']);

        $this->getJson('/api/families?search=coel')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Coelho');
    }

    public function test_index_search_term_does_not_treat_underscore_as_a_wildcard(): void
    {
        $this->actingAsTenantUser();

        Family::create(['name' => 'Coelho']);
        Family::create(['name' => 'Co_lho']);

        $this->getJson('/api/families?search=Co_lho')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Co_lho');
    }

    public function test_index_sorts_by_an_allowed_column_in_both_directions(): void
    {
        $this->actingAsTenantUser();

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
        $this->actingAsTenantUser();

        $this->getJson('/api/families?sort=tenant_id')
            ->assertStatus(422)
            ->assertJsonValidationErrors('sort');
    }

    public function test_index_caps_the_page_size(): void
    {
        $this->actingAsTenantUser();

        $this->getJson('/api/families?per_page=5000')
            ->assertStatus(422)
            ->assertJsonValidationErrors('per_page');
    }

    public function test_timestamp_sorting_requires_the_private_data_ability(): void
    {
        $this->actingAsTenantUser(['navigation.families' => true]);

        $this->getJson('/api/families?sort=created_at')
            ->assertStatus(422)
            ->assertJsonValidationErrors('sort');

        $this->getJson('/api/families?sort=-updated_at')
            ->assertStatus(422)
            ->assertJsonValidationErrors('sort');
    }

    public function test_timestamp_sorting_is_allowed_with_the_private_data_ability(): void
    {
        $this->actingAsTenantUser([
            'navigation.families' => true,
            'families.private_data.view' => true,
        ]);

        Family::create(['name' => 'Alves']);

        $this->getJson('/api/families?sort=created_at')->assertOk();
        $this->getJson('/api/families?sort=-updated_at')->assertOk();
    }
}
