<?php

namespace Tests\Feature;

use Tests\TestCase;

class ModuleLoaderTest extends TestCase
{
    public function test_module_registry_discovers_enabled_modules(): void
    {
        $this->assertNotEmpty(app(\App\Support\Modules\ModuleRegistry::class)->all());
    }
}
