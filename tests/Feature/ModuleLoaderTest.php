<?php

namespace Tests\Feature;

use Tests\TestCase;

class ModuleLoaderTest extends TestCase
{
    public function test_module_registry_discovers_enabled_modules(): void
    {
        $modules = collect(app(\App\Support\Modules\ModuleRegistry::class)->all())->keys()->all();

        $this->assertContains('Core', $modules);
        $this->assertContains('Users', $modules);
        $this->assertContains('People', $modules);
        $this->assertContains('Families', $modules);
        $this->assertContains('Groups', $modules);
        $this->assertContains('Events', $modules);
        $this->assertContains('Finance', $modules);
        $this->assertContains('Care', $modules);
        $this->assertContains('Communications', $modules);
        $this->assertContains('Calendar', $modules);
        $this->assertContains('Kiosk', $modules);
    }
}
