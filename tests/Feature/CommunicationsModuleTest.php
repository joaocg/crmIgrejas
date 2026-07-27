<?php

namespace Tests\Feature;

use Tests\TestCase;

class CommunicationsModuleTest extends TestCase
{
    public function test_communications_route_requires_authentication(): void
    {
        $this->getJson('/api/communications')->assertUnauthorized();
    }
}
