<?php

namespace Tests\Feature\DevOps;

use Tests\TestCase;

class RuntimeConfigTest extends TestCase
{
    public function test_runtime_defaults_use_redis_and_expose_memcached(): void
    {
        $this->assertSame('redis', config('cache.default'));
        $this->assertSame('redis', config('session.driver'));
        $this->assertSame('redis', config('queue.default'));
        $this->assertArrayHasKey('memcached', config('cache.stores'));
    }
}
