<?php

namespace Tests\Feature;

use Tests\TestCase;

class GroupsSpaTest extends TestCase
{
    public function test_groups_spa_assets_exist(): void
    {
        $this->assertFileExists(resource_path('js/api/modules/groups.js'));
        $this->assertFileExists(resource_path('js/pages/modules/groups/GroupListPage.vue'));
    }
}
