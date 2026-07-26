# Laravel 12 Foundation Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Turn the new repository into a fully containerized Laravel 12 development base with Sanctum, Telescope, Redis, Memcached, and pt_BR-first defaults.

**Architecture:** This plan keeps the bootstrapping layer small and explicit. Docker owns runtime parity, Laravel config owns application defaults, and the first tests prove the app boots inside the container rather than relying on the host PHP installation.

**Tech Stack:** Laravel 12, PHP 8.3-FPM, Nginx, MySQL 8.4, Redis, Memcached, Sanctum, Telescope, PHPUnit.

---

### Task 1: Make Docker the only supported local runtime

**Files:**
- Create: `docker-compose.yml`
- Create: `docker/php/Dockerfile`
- Create: `docker/php/php.ini`
- Create: `docker/nginx/default.conf`
- Create: `docker/mysql/my.cnf`
- Create: `docker/redis/redis.conf`
- Create: `docker/memcached/memcached.conf`
- Modify: `.env.example`

- [ ] **Step 1: Write the failing smoke test**

```php
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
```

- [ ] **Step 2: Run the smoke test in Docker and confirm the baseline fails before wiring the stack**

```bash
docker compose up -d --build
docker compose exec app php artisan test --filter=SmokeTest -v
```

Expected: fail until the container stack, web server, and app entrypoint are all wired correctly.

- [ ] **Step 3: Implement the runtime files**

`docker-compose.yml` should define:
```yaml
services:
  app:
    build:
      context: .
      dockerfile: docker/php/Dockerfile
  web:
    image: nginx:1.27
  queue:
    depends_on:
      - app
      - redis
  scheduler:
    depends_on:
      - app
      - redis
  mysql:
    image: mysql:8.4
  redis:
    image: redis:7.4
  memcached:
    image: memcached:1.6
```

- [ ] **Step 4: Re-run the smoke test until it passes**

```bash
docker compose exec app php artisan test --filter=SmokeTest -v
```

Expected: PASS.

- [ ] **Step 5: Commit the foundation runtime**

```bash
git add docker-compose.yml docker/ .env.example tests/Feature/SmokeTest.php
git commit -m "feat: add dockerized Laravel 12 foundation"
```

### Task 2: Set the application defaults for pt_BR and local development

**Files:**
- Modify: `config/app.php`
- Modify: `.env.example`
- Modify: `bootstrap/app.php`
- Modify: `routes/web.php`

- [ ] **Step 1: Write the config assertions**

```php
public function test_app_defaults_are_pt_br_ready(): void
{
    $this->assertSame('pt_BR', config('app.locale'));
    $this->assertSame('America/Fortaleza', config('app.timezone'));
}
```

- [ ] **Step 2: Apply the config changes**

```php
'timezone' => 'America/Fortaleza',
'locale' => env('APP_LOCALE', 'pt_BR'),
'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),
'faker_locale' => env('APP_FAKER_LOCALE', 'pt_BR'),
```

- [ ] **Step 3: Re-run the config test and the smoke test**

```bash
docker compose exec app php artisan test --filter=SmokeTest -v
```

- [ ] **Step 4: Commit the locale defaults**

```bash
git add config/app.php .env.example bootstrap/app.php routes/web.php
git commit -m "feat: set pt_BR as the default Laravel locale"
```

### Task 3: Wire Sanctum, Telescope, and the base auth surface

**Files:**
- Modify: `composer.json`
- Modify: `bootstrap/providers.php`
- Create: `app/Providers/TelescopeServiceProvider.php`
- Modify: `config/sanctum.php`
- Modify: `config/auth.php`
- Modify: `routes/api.php`
- Modify: `routes/web.php`

- [ ] **Step 1: Write the first auth and telemetry tests**

```php
public function test_api_route_requires_authentication(): void
{
    $this->getJson('/api/me')->assertUnauthorized();
}
```

- [ ] **Step 2: Register the providers**

```php
return [
    App\Providers\AppServiceProvider::class,
    App\Providers\TelescopeServiceProvider::class,
];
```

- [ ] **Step 3: Create the authenticated API stub**

```php
Route::middleware('auth:sanctum')->get('/me', function () {
    return request()->user();
});
```

- [ ] **Step 4: Run the auth and telemetry tests in Docker**

```bash
docker compose exec app php artisan test -v
```

- [ ] **Step 5: Commit the auth foundation**

```bash
git add composer.json bootstrap/providers.php app/Providers/TelescopeServiceProvider.php config/auth.php config/sanctum.php routes/api.php routes/web.php
git commit -m "feat: wire sanctum and telescope"
```
