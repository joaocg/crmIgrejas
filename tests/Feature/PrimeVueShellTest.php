<?php

namespace Tests\Feature;

use Tests\TestCase;

class PrimeVueShellTest extends TestCase
{
    public function test_dashboard_and_login_routes_serve_the_spa_shell(): void
    {
        $this->get('/dashboard')->assertOk()->assertSee('<div id="app"></div>', false);
        $this->get('/login')->assertOk()->assertSee('<div id="app"></div>', false);
        $this->get('/people')->assertOk()->assertSee('<div id="app"></div>', false);
        $this->get('/families')->assertOk()->assertSee('<div id="app"></div>', false);
    }
}
