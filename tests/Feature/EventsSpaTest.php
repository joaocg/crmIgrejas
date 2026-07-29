<?php

namespace Tests\Feature;

use Tests\TestCase;

class EventsSpaTest extends TestCase
{
    public function test_events_routes_serve_the_spa_shell(): void
    {
        $this->get('/events')->assertOk()->assertSee('<div id="app"></div>', false);
        $this->get('/events/create')->assertOk()->assertSee('<div id="app"></div>', false);
        $this->get('/events/1')->assertOk()->assertSee('<div id="app"></div>', false);
        $this->get('/events/1/edit')->assertOk()->assertSee('<div id="app"></div>', false);
    }
}
