<?php

namespace Tests\Feature;

use Tests\TestCase;

class FinanceSpaTest extends TestCase
{
    public function test_finance_routes_serve_the_spa_shell(): void
    {
        $this->get('/finance')->assertOk()->assertSee('<div id="app"></div>', false);
        $this->get('/finance/create')->assertOk()->assertSee('<div id="app"></div>', false);
        $this->get('/finance/1')->assertOk()->assertSee('<div id="app"></div>', false);
        $this->get('/finance/1/edit')->assertOk()->assertSee('<div id="app"></div>', false);
    }
}
