<?php

namespace Tests\Feature;

use App\Models\Deposit;
use App\Models\DonationFund;
use App\Models\Family;
use App\Models\Pledge;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_finance_routes_require_authentication(): void
    {
        $this->getJson('/api/donation-funds')->assertUnauthorized();
        $this->getJson('/api/deposits')->assertUnauthorized();
        $this->getJson('/api/pledges')->assertUnauthorized();
    }

    public function test_finance_entities_are_scoped_to_the_authenticated_tenant(): void
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
        $fund = DonationFund::create([
            'tenant_id' => $tenant->id,
            'name' => 'General Fund',
            'active' => true,
        ]);
        $otherFund = DonationFund::create([
            'tenant_id' => $otherTenant->id,
            'name' => 'Other Fund',
            'active' => true,
        ]);
        $deposit = Deposit::create([
            'tenant_id' => $tenant->id,
            'fund_id' => $fund->id,
            'date' => '2026-07-28',
            'entered_by_user_id' => $user->id,
        ]);
        $otherDeposit = Deposit::create([
            'tenant_id' => $otherTenant->id,
            'fund_id' => $otherFund->id,
            'date' => '2026-07-28',
        ]);
        $pledge = Pledge::create([
            'tenant_id' => $tenant->id,
            'family_id' => $family->id,
            'fund_id' => $fund->id,
            'amount' => 100,
        ]);
        $otherPledge = Pledge::create([
            'tenant_id' => $otherTenant->id,
            'family_id' => $otherFamily->id,
            'fund_id' => $otherFund->id,
            'amount' => 50,
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/donation-funds')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $fund->id);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/donation-funds', [
                'name' => 'Missions',
            ])
            ->assertCreated()
            ->assertJsonPath('tenant_id', $tenant->id);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/deposits')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $deposit->id);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/deposits', [
                'fund_id' => $fund->id,
                'date' => '2026-07-28',
            ])
            ->assertCreated()
            ->assertJsonPath('tenant_id', $tenant->id);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/pledges')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $pledge->id);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/pledges', [
                'family_id' => $family->id,
                'amount' => 120,
            ])
            ->assertCreated()
            ->assertJsonPath('tenant_id', $tenant->id);

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/donation-funds/{$otherFund->id}")
            ->assertNotFound();

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/deposits/{$otherDeposit->id}")
            ->assertNotFound();

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/pledges/{$otherPledge->id}")
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
