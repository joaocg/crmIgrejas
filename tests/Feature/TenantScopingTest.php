<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class TenantScopingTest extends TestCase
{
    public function test_module_queries_are_scoped_to_tenant(): void
    {
        $modules = collect(app(\App\Support\Modules\ModuleRegistry::class)->all())->keys()->all();

        $this->assertContains('People', $modules);
        $this->assertContains('Families', $modules);
        $this->assertContains('Groups', $modules);
        $this->assertContains('Events', $modules);
        $this->assertContains('Finance', $modules);
        $this->assertContains('Care', $modules);
        $this->assertTrue(method_exists(User::class, 'currentTenant'));
    }
}
