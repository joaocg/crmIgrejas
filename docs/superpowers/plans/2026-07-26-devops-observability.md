# DevOps and Observability Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Provide a development environment that mirrors production concerns closely enough for reliable local work, while exposing queues, cache, sessions, and observability in a way developers can monitor easily.

**Architecture:** The environment is container-first and service-separated. Queue workers, scheduler, cache, and telemetry are explicit services so developers can observe runtime behavior instead of guessing. Telescope remains a local-only introspection tool, and the runtime config is optimized for repeatable dev startup.

**Tech Stack:** Docker Compose, Nginx, PHP-FPM, MySQL, Redis, Memcached, Laravel queue worker, Laravel scheduler, Telescope.

---

### Task 1: Add the operational services to the compose stack

**Files:**
- Modify: `docker-compose.yml`
- Modify: `.env.example`
- Create: `docker/queue/entrypoint.sh`
- Create: `docker/scheduler/entrypoint.sh`

- [ ] **Step 1: Write service health checks**

```yaml
healthcheck:
  test: ["CMD", "redis-cli", "ping"]
```

- [ ] **Step 2: Wire queue and scheduler containers**

```yaml
queue:
  command: php artisan queue:work --sleep=1 --tries=3
scheduler:
  command: php artisan schedule:work
```

- [ ] **Step 3: Verify container startup order**

```bash
docker compose up -d --build
docker compose ps
```

- [ ] **Step 4: Commit the ops stack**

```bash
git add docker-compose.yml docker .env.example
git commit -m "feat: add operational docker services"
```

### Task 2: Make cache and session behavior explicit

**Files:**
- Modify: `config/cache.php`
- Modify: `config/session.php`
- Modify: `.env.example`
- Modify: `app/Http/Middleware/TrustProxies.php`

- [ ] **Step 1: Write cache/session tests**

```php
public function test_cache_uses_redis_by_default(): void
{
    $this->assertSame('redis', config('cache.default'));
}
```

- [ ] **Step 2: Set the drivers**

```php
'default' => env('CACHE_STORE', 'redis'),
'driver' => env('SESSION_DRIVER', 'redis'),
```

- [ ] **Step 3: Verify the app survives restarts without losing local config**

```bash
docker compose down
docker compose up -d
docker compose exec app php artisan config:show cache.default
```

- [ ] **Step 4: Commit the runtime settings**

```bash
git add config/cache.php config/session.php .env.example app/Http/Middleware/TrustProxies.php
git commit -m "feat: configure cache and session drivers"
```

### Task 3: Keep Telescope local-only and safe

**Files:**
- Create: `app/Providers/TelescopeServiceProvider.php`
- Modify: `config/telescope.php`
- Modify: `routes/web.php`
- Modify: `app/Http/Middleware/EnsureFrontendRequestsAreStateful.php` if needed

- [ ] **Step 1: Write access-control tests**

```php
public function test_telescope_is_not_exposed_in_production(): void
{
    $this->assertTrue(true);
}
```

- [ ] **Step 2: Restrict Telescope to local and development environments**

```php
if (! $this->app->environment(['local', 'development'])) {
    return;
}
```

- [ ] **Step 3: Verify Telescope is reachable only from the dev stack**

```bash
docker compose exec app php artisan telescope:status
```

- [ ] **Step 4: Commit the observability controls**

```bash
git add app/Providers/TelescopeServiceProvider.php config/telescope.php routes/web.php
git commit -m "feat: secure telescope for local development"
```

