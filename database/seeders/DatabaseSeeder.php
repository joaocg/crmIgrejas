<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::table('tenants')->updateOrInsert(
            ['slug' => 'default'],
            [
                'name' => 'Default Church',
                'locale' => 'pt_BR',
                'timezone' => 'America/Fortaleza',
                'active' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $tenantId = DB::table('tenants')->where('slug', 'default')->value('id');

        DB::table('roles')->updateOrInsert(
            ['tenant_id' => $tenantId, 'slug' => 'admin'],
            [
                'name' => 'Admin',
                'description' => 'System administrator',
                'permissions' => json_encode(['*' => true]),
                'is_system' => true,
                'active' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $roleId = DB::table('roles')->where('tenant_id', $tenantId)->where('slug', 'admin')->value('id');

        DB::table('users')->updateOrInsert(
            ['email' => 'admin@localhost'],
            [
                'tenant_id' => $tenantId,
                'role_id' => $roleId,
                'name' => 'Admin',
                'locale' => 'pt_BR',
                'active' => true,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'remember_token' => null,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }
}
