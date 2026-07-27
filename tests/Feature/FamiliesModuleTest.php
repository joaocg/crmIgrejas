<?php

namespace Tests\Feature;

use Tests\TestCase;

class FamiliesModuleTest extends TestCase
{
    public function test_families_index_requires_authentication(): void
    {
        $this->getJson('/api/families')->assertUnauthorized();
    }
}
