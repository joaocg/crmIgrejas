<?php

namespace Tests\Feature;

use Tests\TestCase;

class CalendarModuleTest extends TestCase
{
    public function test_calendar_route_requires_authentication(): void
    {
        $this->getJson('/api/calendar')->assertUnauthorized();
    }
}
