<?php

namespace Tests\Feature;

use Tests\TestCase;

class FinanceModuleTest extends TestCase
{
    public function test_finance_routes_require_authentication(): void
    {
        $this->getJson('/api/donation-funds')->assertUnauthorized();
        $this->getJson('/api/deposits')->assertUnauthorized();
        $this->getJson('/api/pledges')->assertUnauthorized();
    }
}
