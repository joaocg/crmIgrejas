<?php

namespace Tests\Feature;

use Tests\TestCase;

class KioskSpaTest extends TestCase
{
    public function test_kiosk_routes_serve_the_spa_shell(): void
    {
        $this->get('/kiosk')->assertOk()->assertSee('<div id="app"></div>', false);
    }
}
