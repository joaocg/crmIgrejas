<?php

namespace Tests\Feature;

use Tests\TestCase;

class SpaShellTest extends TestCase
{
    public function test_spa_shell_is_served(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('<div id="app"></div>', false);
    }
}
