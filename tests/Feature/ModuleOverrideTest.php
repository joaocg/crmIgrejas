<?php

namespace Tests\Feature;

use Tests\TestCase;

class ModuleOverrideTest extends TestCase
{
    public function test_church_override_is_used_when_present(): void
    {
        $this->assertTrue(app(\App\Support\Modules\ModuleLoader::class)->hasOverride('Users', 'church_slug'));
    }
}
