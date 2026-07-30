<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Person;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeoplePaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_a_paginated_envelope(): void
    {
        $user = $this->actingAsTenantUser();

        for ($index = 1; $index <= 30; $index++) {
            Person::create([
                'first_name' => 'Person'.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
                'last_name' => 'Sobrenome',
            ]);
        }

        $this->getJson('/api/people?per_page=10')
            ->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.total', 30)
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.last_page', 3);

        $this->assertSame($user->tenant_id, Person::query()->first()->tenant_id);
    }

    public function test_index_filters_by_search_term(): void
    {
        $this->actingAsTenantUser();

        Person::create(['first_name' => 'Joao', 'last_name' => 'Coelho']);
        Person::create(['first_name' => 'Maria', 'last_name' => 'Silva']);

        $this->getJson('/api/people?search=coelho')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.first_name', 'Joao');
    }

    public function test_index_search_term_does_not_treat_underscore_as_a_wildcard(): void
    {
        $this->actingAsTenantUser();

        Person::create(['first_name' => 'Joao', 'last_name' => 'Coelho']);
        Person::create(['first_name' => 'Jo_o', 'last_name' => 'Wildcard']);

        $this->getJson('/api/people?search=Jo_o')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.first_name', 'Jo_o');
    }

    public function test_index_sorts_by_an_allowed_column_in_both_directions(): void
    {
        $this->actingAsTenantUser();

        Person::create(['first_name' => 'Ana', 'last_name' => 'Zebra']);
        Person::create(['first_name' => 'Bruno', 'last_name' => 'Alves']);

        $this->getJson('/api/people?sort=last_name')
            ->assertOk()
            ->assertJsonPath('data.0.last_name', 'Alves');

        $this->getJson('/api/people?sort=-last_name')
            ->assertOk()
            ->assertJsonPath('data.0.last_name', 'Zebra');
    }

    public function test_index_rejects_an_unknown_sort_column(): void
    {
        $this->actingAsTenantUser();

        $this->getJson('/api/people?sort=password')
            ->assertStatus(422)
            ->assertJsonValidationErrors('sort');
    }

    public function test_index_caps_the_page_size(): void
    {
        $this->actingAsTenantUser();

        $this->getJson('/api/people?per_page=5000')
            ->assertStatus(422)
            ->assertJsonValidationErrors('per_page');
    }

    public function test_timestamp_sorting_requires_the_private_data_ability(): void
    {
        $this->actingAsTenantUser(['navigation.people' => true]);

        $this->getJson('/api/people?sort=created_at')
            ->assertStatus(422)
            ->assertJsonValidationErrors('sort');

        $this->getJson('/api/people?sort=-updated_at')
            ->assertStatus(422)
            ->assertJsonValidationErrors('sort');
    }

    public function test_timestamp_sorting_is_allowed_with_the_private_data_ability(): void
    {
        $this->actingAsTenantUser([
            'navigation.people' => true,
            'people.private_data.view' => true,
        ]);

        Person::create(['first_name' => 'Joao', 'last_name' => 'Coelho']);

        $this->getJson('/api/people?sort=created_at')->assertOk();
        $this->getJson('/api/people?sort=-updated_at')->assertOk();
    }
}
