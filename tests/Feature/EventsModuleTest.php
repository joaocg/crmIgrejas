<?php

namespace Tests\Feature;

use Tests\TestCase;

class EventsModuleTest extends TestCase
{
    public function test_event_attendance_routes_require_authentication(): void
    {
        $this->getJson('/api/events')->assertUnauthorized();
        $this->postJson('/api/events/1/attendance')->assertUnauthorized();
    }
}
