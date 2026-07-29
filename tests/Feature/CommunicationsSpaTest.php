<?php

namespace Tests\Feature;

use Tests\TestCase;

class CommunicationsSpaTest extends TestCase
{
    public function test_communications_routes_serve_the_spa_shell(): void
    {
        $this->get('/communications')->assertOk()->assertSee('<div id="app"></div>', false);
        $this->get('/communications/whatsapp')->assertOk()->assertSee('<div id="app"></div>', false);
        $this->get('/settings/integrations/whatsapp')->assertOk()->assertSee('<div id="app"></div>', false);
    }
}
