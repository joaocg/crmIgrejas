<?php

declare(strict_types=1);

namespace Tests\Feature\Tenancy;

use App\Models\Person;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GlobalTenantScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_queries_are_scoped_to_the_authenticated_tenant(): void
    {
        $tenant = $this->createTenant('default');
        $otherTenant = $this->createTenant('other');

        Person::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'first_name' => 'Joao',
            'last_name' => 'Coelho',
        ]);
        Person::withoutGlobalScopes()->create([
            'tenant_id' => $otherTenant->id,
            'first_name' => 'Other',
            'last_name' => 'Person',
        ]);

        $this->actingAs($this->createUser($tenant->id), 'sanctum');

        $this->assertSame(1, Person::query()->count());
        $this->assertSame('Joao', Person::query()->first()->first_name);
    }

    public function test_tenant_id_is_filled_automatically_on_create(): void
    {
        $tenant = $this->createTenant('default');
        $this->actingAs($this->createUser($tenant->id), 'sanctum');

        $person = Person::create([
            'first_name' => 'Maria',
            'last_name' => 'Silva',
        ]);

        $this->assertSame($tenant->id, $person->tenant_id);
    }

    public function test_explicit_override_wins_over_the_authenticated_user(): void
    {
        $tenant = $this->createTenant('default');
        $otherTenant = $this->createTenant('other');

        Person::withoutGlobalScopes()->create([
            'tenant_id' => $otherTenant->id,
            'first_name' => 'Other',
            'last_name' => 'Person',
        ]);

        $this->actingAs($this->createUser($tenant->id), 'sanctum');

        $count = app(TenantContext::class)->runAs(
            $otherTenant->id,
            fn (): int => Person::query()->count(),
        );

        $this->assertSame(1, $count);
        $this->assertSame(0, Person::query()->count());
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
