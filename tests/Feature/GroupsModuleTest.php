<?php

namespace Tests\Feature;

use Tests\TestCase;

class GroupsModuleTest extends TestCase
{
    public function test_group_membership_routes_require_authentication(): void
    {
        $this->getJson('/api/groups')->assertUnauthorized();
        $this->postJson('/api/groups/1/members')->assertUnauthorized();
    }
}
