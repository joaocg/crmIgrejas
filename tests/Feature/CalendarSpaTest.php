<?php

namespace Tests\Feature;

use Tests\TestCase;

class CalendarSpaTest extends TestCase
{
    public function test_calendar_routes_serve_the_spa_shell(): void
    {
        $this->get('/calendar')->assertOk()->assertSee('<div id="app"></div>', false);
    }
}
