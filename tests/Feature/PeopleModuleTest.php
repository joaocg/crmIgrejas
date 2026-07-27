<?php

namespace Tests\Feature;

use Tests\TestCase;

class PeopleModuleTest extends TestCase
{
    public function test_people_index_requires_authentication(): void
    {
        $this->getJson('/api/people')->assertUnauthorized();
    }
}
