<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TokenLoginTest extends TestCase
{
    use RefreshDatabase;

    private function createTenantAndUser(array $attributes = []): User
    {
        $tenantId = DB::table('tenants')->insertGetId([
            'slug' => 'default',
            'name' => 'Default Church',
            'locale' => 'pt_BR',
            'timezone' => 'America/Fortaleza',
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return User::factory()->create(array_merge([
            'tenant_id' => $tenantId,
            'role_id' => null,
            'email' => 'admin@church.local',
            'password' => 'password',
        ], $attributes));
    }

    public function test_user_can_login_and_receive_a_token(): void
    {
        $user = $this->createTenantAndUser();

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure([
                'token',
                'token_type',
                'user' => ['id', 'name', 'email', 'locale'],
            ]);

        $this->assertSame('Bearer', $response->json('token_type'));
    }

    public function test_me_endpoint_accepts_bearer_tokens(): void
    {
        $user = $this->createTenantAndUser();
        $token = $user->createToken('spa')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('email', $user->email);
    }

    public function test_user_can_logout_and_revoke_current_token(): void
    {
        $user = $this->createTenantAndUser();
        $token = $user->createToken('spa')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/auth/logout')
            ->assertNoContent();
    }
}
