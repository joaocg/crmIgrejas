<?php

namespace Tests\Feature\DevOps;

use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class ObservabilityTest extends TestCase
{
    public function test_telescope_is_enabled_for_development_environments(): void
    {
        $this->assertTrue(config('telescope.enabled'));
        $this->assertTrue(Gate::allows('viewTelescope'));
    }
}
