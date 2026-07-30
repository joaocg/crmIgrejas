<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Person;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupsPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_a_paginated_envelope(): void
    {
        $this->actingAsTenantUser();

        for ($index = 1; $index <= 30; $index++) {
            Group::create(['name' => 'Grupo '.str_pad((string) $index, 2, '0', STR_PAD_LEFT)]);
        }

        $this->getJson('/api/groups?per_page=10')
            ->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.total', 30)
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.last_page', 3);
    }

    public function test_index_filters_by_search_term_on_the_name(): void
    {
        $this->actingAsTenantUser();

        Group::create(['name' => 'Intercessao']);
        Group::create(['name' => 'Louvor']);

        $this->getJson('/api/groups?search=inter')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Intercessao');
    }

    public function test_index_filters_by_search_term_on_the_type(): void
    {
        $this->actingAsTenantUser();

        Group::create(['name' => 'Intercessao', 'type' => 'ministry']);
        Group::create(['name' => 'Louvor', 'type' => 'small-group']);

        $this->getJson('/api/groups?search=small')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Louvor');
    }

    public function test_index_search_term_does_not_treat_underscore_as_a_wildcard(): void
    {
        $this->actingAsTenantUser();

        Group::create(['name' => 'Louvor']);
        Group::create(['name' => 'Lo_vor']);

        $this->getJson('/api/groups?search=Lo_vor')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Lo_vor');
    }

    public function test_index_sorts_by_name_in_both_directions(): void
    {
        $this->actingAsTenantUser();

        Group::create(['name' => 'Zeladoria']);
        Group::create(['name' => 'Adoracao']);

        $this->getJson('/api/groups?sort=name')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Adoracao');

        $this->getJson('/api/groups?sort=-name')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Zeladoria');
    }

    public function test_index_sorts_by_type(): void
    {
        $this->actingAsTenantUser();

        Group::create(['name' => 'Adoracao', 'type' => 'ministry']);
        Group::create(['name' => 'Zeladoria', 'type' => 'committee']);

        $this->getJson('/api/groups?sort=type')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Zeladoria');
    }

    public function test_index_sorts_by_the_member_count(): void
    {
        $this->actingAsTenantUser();

        $empty = Group::create(['name' => 'Adoracao']);
        $populated = Group::create(['name' => 'Zeladoria']);

        $person = Person::create(['first_name' => 'Joao', 'last_name' => 'Coelho']);
        $populated->memberships()->create(['person_id' => $person->id]);

        $this->getJson('/api/groups?sort=-members_count')
            ->assertOk()
            ->assertJsonPath('data.0.id', $populated->id)
            ->assertJsonPath('data.0.members_count', 1)
            ->assertJsonPath('data.1.id', $empty->id)
            ->assertJsonPath('data.1.members_count', 0);
    }

    /**
     * The roster drops deactivated people too, not just the count — the
     * legacy member table at PeopleGroupController.php:442-447 filters on
     * per_datedeactivated before listing. members_count must equal
     * count(members) in the same payload.
     */
    public function test_the_member_roster_excludes_deactivated_people(): void
    {
        $this->actingAsTenantUser();

        $group = Group::create(['name' => 'Adoracao']);

        $active = Person::create(['first_name' => 'Joao', 'last_name' => 'Coelho']);
        $deactivated = Person::create([
            'first_name' => 'Maria',
            'last_name' => 'Silva',
            'deactivated_at' => '2020-01-01 00:00:00',
        ]);

        $group->memberships()->create(['person_id' => $active->id]);
        $group->memberships()->create(['person_id' => $deactivated->id]);

        $this->getJson('/api/groups')
            ->assertOk()
            ->assertJsonCount(1, 'data.0.members')
            ->assertJsonPath('data.0.members.0.person.first_name', 'Joao');

        $this->getJson("/api/groups/{$group->id}")
            ->assertOk()
            ->assertJsonCount(1, 'data.members')
            ->assertJsonPath('data.members_count', 1);
    }

    public function test_the_member_count_excludes_deactivated_people(): void
    {
        $this->actingAsTenantUser();

        $group = Group::create(['name' => 'Adoracao']);

        $active = Person::create(['first_name' => 'Joao', 'last_name' => 'Coelho']);
        $deactivated = Person::create([
            'first_name' => 'Maria',
            'last_name' => 'Silva',
            'deactivated_at' => '2020-01-01 00:00:00',
        ]);

        $group->memberships()->create(['person_id' => $active->id]);
        $group->memberships()->create(['person_id' => $deactivated->id]);

        $this->getJson('/api/groups')
            ->assertOk()
            ->assertJsonPath('data.0.members_count', 1);
    }

    public function test_index_rejects_an_unknown_sort_column(): void
    {
        $this->actingAsTenantUser();

        $this->getJson('/api/groups?sort=tenant_id')
            ->assertStatus(422)
            ->assertJsonValidationErrors('sort');
    }

    public function test_index_rejects_sorting_by_a_column_the_legacy_list_never_showed(): void
    {
        $this->actingAsTenantUser();

        $this->getJson('/api/groups?sort=created_at')
            ->assertStatus(422)
            ->assertJsonValidationErrors('sort');

        $this->getJson('/api/groups?sort=-updated_at')
            ->assertStatus(422)
            ->assertJsonValidationErrors('sort');
    }

    public function test_index_caps_the_page_size(): void
    {
        $this->actingAsTenantUser();

        $this->getJson('/api/groups?per_page=5000')
            ->assertStatus(422)
            ->assertJsonValidationErrors('per_page');
    }
}
