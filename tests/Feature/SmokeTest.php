<?php

namespace Tests\Feature;

use Tests\TestCase;

class SmokeTest extends TestCase
{
    public function test_application_loads_the_homepage(): void
    {
        $response = $this->get('/');

        $response->assertOk();
    }
}
