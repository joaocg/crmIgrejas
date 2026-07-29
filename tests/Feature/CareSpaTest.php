<?php

namespace Tests\Feature;

use Tests\TestCase;

class CareSpaTest extends TestCase
{
    public function test_care_routes_serve_the_spa_shell(): void
    {
        $this->get('/care')->assertOk()->assertSee('<div id="app"></div>', false);
        $this->get('/care/notes/create')->assertOk()->assertSee('<div id="app"></div>', false);
        $this->get('/care/pastoral-care/create')->assertOk()->assertSee('<div id="app"></div>', false);
    }
}
