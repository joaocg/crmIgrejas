<?php

namespace Tests\Feature;

use Tests\TestCase;

class KioskModuleTest extends TestCase
{
    public function test_kiosk_route_requires_authentication(): void
    {
        $this->getJson('/api/kiosk')->assertUnauthorized();
    }
}
