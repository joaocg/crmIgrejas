<?php

namespace Tests\Feature;

use Tests\TestCase;

class UsersModuleTest extends TestCase
{
    public function test_users_module_registers_api_routes(): void
    {
        $this->getJson('/api/users')->assertStatus(401);
    }
}
