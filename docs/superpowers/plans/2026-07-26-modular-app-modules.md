# Modular App Modules Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a modular backend around `app/Modules` where each domain module owns its routes, actions, policies, views/API resources, and church-specific overrides.

**Architecture:** The module system uses a small registry and a consistent folder contract rather than a heavy package manager. Each module exposes a public interface to the rest of the app, while custom church behavior lives in a nested namespace like `app/Modules/Usuarios/{church-prefix}` so overrides do not leak into core logic.

**Tech Stack:** Laravel service providers, PSR-4 autoloading, module manifests, route registration, policies, actions, Eloquent, PHPUnit.

---

### Task 1: Define the module contract and loader

**Files:**
- Create: `app/Support/Modules/ModuleDefinition.php`
- Create: `app/Support/Modules/ModuleRegistry.php`
- Create: `app/Support/Modules/ModuleLoader.php`
- Modify: `bootstrap/providers.php`
- Modify: `composer.json`

- [ ] **Step 1: Write a module-loading test**

```php
public function test_module_registry_discovers_enabled_modules(): void
{
    $this->assertNotEmpty(app(\App\Support\Modules\ModuleRegistry::class)->all());
}
```

- [ ] **Step 2: Implement the module definition**

```php
final class ModuleDefinition
{
    public function __construct(
        public readonly string $name,
        public readonly string $path,
        public readonly bool $enabled = true,
    ) {}
}
```

- [ ] **Step 3: Register the loader in Laravel bootstrap**

```php
return [
    App\Providers\AppServiceProvider::class,
];
```

- [ ] **Step 4: Run the module discovery test**

```bash
docker compose exec app php artisan test --filter=Module -v
```

- [ ] **Step 5: Commit the module loader**

```bash
git add app/Support/Modules bootstrap/providers.php composer.json tests
git commit -m "feat: add module loader contract"
```

### Task 2: Create the first real module boundaries

**Files:**
- Create: `app/Modules/Core/Providers/CoreModuleServiceProvider.php`
- Create: `app/Modules/Identity/Providers/IdentityModuleServiceProvider.php`
- Create: `app/Modules/Identity/Routes/api.php`
- Create: `app/Modules/Identity/Actions/ListUsersAction.php`
- Create: `app/Modules/Identity/Actions/CreateUserAction.php`
- Create: `app/Modules/Identity/Actions/UpdateUserAction.php`
- Create: `app/Modules/Identity/Actions/DeleteUserAction.php`
- Modify: `routes/api.php`

- [ ] **Step 1: Write route registration tests**

```php
public function test_identity_module_registers_api_routes(): void
{
    $this->getJson('/api/users')->assertStatus(401);
}
```

- [ ] **Step 2: Implement module-local route files**

```php
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('users', UserController::class);
});
```

- [ ] **Step 3: Verify the module can own CRUD without touching the global routes**

```bash
docker compose exec app php artisan test --filter=IdentityModule -v
```

- [ ] **Step 4: Commit the first module boundary**

```bash
git add app/Modules routes/api.php tests
git commit -m "feat: add identity module boundaries"
```

### Task 3: Add church-specific overrides inside modules

**Files:**
- Create: `app/Modules/Identity/Churches/prefixo_da_igreja/IdentityOverrides.php`
- Create: `app/Modules/Identity/Churches/prefixo_da_igreja/UserLabelPolicy.php`
- Modify: `app/Support/Modules/ModuleLoader.php`

- [ ] **Step 1: Write an override resolution test**

```php
public function test_church_override_is_used_when_present(): void
{
    $this->assertTrue(app(\App\Support\Modules\ModuleLoader::class)->hasOverride('Identity', 'prefixo_da_igreja'));
}
```

- [ ] **Step 2: Resolve overrides by church prefix**

```php
if (file_exists($overridePath)) {
    require $overridePath;
}
```

- [ ] **Step 3: Commit the override mechanism**

```bash
git add app/Modules app/Support/Modules tests
git commit -m "feat: support church-specific module overrides"
```

