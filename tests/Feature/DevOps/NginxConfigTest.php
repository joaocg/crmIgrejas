<?php

namespace Tests\Feature\DevOps;

use Tests\TestCase;

class NginxConfigTest extends TestCase
{
    public function test_nginx_resolves_the_php_upstream_dynamically(): void
    {
        $config = file_get_contents(base_path('docker/nginx/default.conf'));

        $this->assertStringContainsString('resolver 127.0.0.11', $config);
        $this->assertStringContainsString('set $php_upstream app:9000;', $config);
        $this->assertStringContainsString('fastcgi_pass $php_upstream;', $config);
        $this->assertStringNotContainsString('fastcgi_pass app:9000;', $config);
    }
}
