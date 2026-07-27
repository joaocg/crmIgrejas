<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseNormalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_core_tables_have_expected_columns(): void
    {
        $this->assertTrue(Schema::hasColumns('tenants', ['slug', 'locale', 'timezone', 'active']));
        $this->assertTrue(Schema::hasColumns('roles', ['tenant_id', 'slug', 'name', 'permissions']));
        $this->assertTrue(Schema::hasColumns('users', ['tenant_id', 'role_id', 'locale', 'active']));
        $this->assertTrue(Schema::hasColumns('addresses', ['tenant_id', 'line1', 'city', 'country']));
        $this->assertTrue(Schema::hasColumns('families', ['tenant_id', 'address_id', 'name']));
        $this->assertTrue(Schema::hasColumns('persons', ['tenant_id', 'family_id', 'address_id', 'first_name', 'last_name']));
        $this->assertTrue(Schema::hasColumns('module_definitions', ['slug', 'name', 'is_core', 'is_enabled']));
        $this->assertTrue(Schema::hasColumns('module_settings', ['tenant_id', 'module_definition_id', 'key', 'value']));
        $this->assertTrue(Schema::hasColumns('groups', ['tenant_id', 'role_id', 'name', 'is_active']));
        $this->assertTrue(Schema::hasColumns('events', ['tenant_id', 'group_id', 'title', 'starts_at']));
    }

    public function test_database_seeder_creates_default_tenant_and_admin_user(): void
    {
        $this->seed();

        $this->assertDatabaseHas('tenants', ['slug' => 'default']);
        $this->assertDatabaseHas('users', ['email' => 'admin@localhost']);
        $this->assertDatabaseHas('roles', ['slug' => 'admin']);
    }
}
