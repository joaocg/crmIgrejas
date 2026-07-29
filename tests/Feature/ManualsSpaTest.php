<?php

namespace Tests\Feature;

use Tests\TestCase;

class ManualsSpaTest extends TestCase
{
    public function test_manuals_routes_serve_the_spa_shell(): void
    {
        $this->get('/manuals')->assertOk()->assertSee('<div id="app"></div>', false);
    }
}
