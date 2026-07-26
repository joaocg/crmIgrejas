<?php

namespace Tests\Feature;

use Tests\TestCase;

class FoundationTest extends TestCase
{
    public function test_app_defaults_are_pt_br_ready(): void
    {
        $this->assertSame('pt_BR', config('app.locale'));
        $this->assertSame('America/Fortaleza', config('app.timezone'));
    }

    public function test_api_route_requires_authentication(): void
    {
        $this->getJson('/api/me')->assertUnauthorized();
    }
}
