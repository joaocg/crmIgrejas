<?php

namespace Tests\Feature;

use Tests\TestCase;

class CareModuleTest extends TestCase
{
    public function test_care_routes_require_authentication(): void
    {
        $this->getJson('/api/notes')->assertUnauthorized();
        $this->getJson('/api/pastoral-care')->assertUnauthorized();
    }
}
