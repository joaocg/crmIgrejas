<?php

namespace Tests\Feature;

use Tests\TestCase;

class RepertoireSpaTest extends TestCase
{
    public function test_repertoire_routes_serve_the_spa_shell(): void
    {
        $this->get('/repertoire')->assertOk()->assertSee('<div id="app"></div>', false);
    }
}
