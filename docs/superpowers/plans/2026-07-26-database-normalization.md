# Database Normalization Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace the legacy denormalized schema with a clean relational model that supports churches, tenants, users, members, families, groups, events, finance, and customization overlays without duplicating the legacy coupling.

**Architecture:** The legacy schema is treated as input, not as the design target. The normalized model introduces a tenant boundary first, then core identity tables, then domain tables with explicit foreign keys and audit metadata. Migration work is staged so the app can bootstrap on a slim seed set before historical data is backfilled. All new table, model, module, and class names stay in English; legacy names appear only in the inventory artifacts and import mapping.

**Tech Stack:** Laravel migrations, seeders, MySQL 8.4, Eloquent, database assertions, factory-driven tests.

---

### Task 1: Inventory the legacy tables and map them to bounded contexts

**Files:**
- Create: `docs/superpowers/plans/2026-07-26-database-map.md`
- Create: `database/legacy/legacy-tables.csv`
- Create: `database/legacy/legacy-fields.csv`

- [ ] **Step 1: Capture the legacy sources**

```text
propel/main.schema.xml
src/mysql/install/Install.sql
src/EcclesiaCRM/model/*
```

- [ ] **Step 2: Classify the core domains**

```text
Identity: user_usr, role tables, permissions, user preferences
Church structure: tenant/church root, campus/location, ministry, configuration
People: person_per, family_fam, address and household linkage tables
Activity: group_grp, events_event, attendance and participation logs
Operations: donations, pledges, deposits, receipts, finance exports
Legacy hotspots: config_cfg, userconfig_ucfg, pastoral care, mail, and plugin-owned tables
```

- [ ] **Step 3: Commit the inventory artifacts**

```bash
git add docs/superpowers/plans/2026-07-26-database-map.md database/legacy
git commit -m "docs: inventory legacy ecclesiacrm schema"
```

### Task 2: Define the normalized baseline schema

**Files:**
- Create: `database/migrations/2026_07_26_000001_create_tenants_table.php`
- Create: `database/migrations/2026_07_26_000002_create_users_table.php`
- Create: `database/migrations/2026_07_26_000003_create_roles_table.php`
- Create: `database/migrations/2026_07_26_000004_create_persons_table.php`
- Create: `database/migrations/2026_07_26_000005_create_families_table.php`
- Create: `database/migrations/2026_07_26_000006_create_addresses_table.php`
- Create: `database/migrations/2026_07_26_000007_create_modules_table.php`
- Create: `database/migrations/2026_07_26_000008_create_module_settings_table.php`
- Create: `database/migrations/2026_07_26_000009_create_contacts_table.php`
- Create: `database/migrations/2026_07_26_000010_create_activity_tables.php`
- Modify: `database/seeders/DatabaseSeeder.php`

- [ ] **Step 1: Write schema tests for key foreign keys**

```php
public function test_persons_belong_to_families_and_tenants(): void
{
    $this->assertTrue(Schema::hasColumns('persons', ['tenant_id', 'family_id']));
}

public function test_users_have_locale_and_tenant_scoping(): void
{
    $this->assertTrue(Schema::hasColumns('users', ['tenant_id', 'locale']));
}
```

- [ ] **Step 2: Implement the migrations with explicit FK and index strategy**

```php
$table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
$table->foreignId('family_id')->nullable()->constrained()->nullOnDelete();
$table->index(['tenant_id', 'last_name']);
```

- [ ] **Step 3: Seed a minimal tenant and admin user**

```php
Tenant::factory()->create(['slug' => 'default']);
User::factory()->create(['email' => 'admin@localhost']);
```

- [ ] **Step 4: Run migration assertions in Docker**

```bash
docker compose exec app php artisan migrate:fresh --seed
docker compose exec app php artisan test --filter=Database -v
```

- [ ] **Step 5: Commit the normalized baseline**

```bash
git add database/migrations database/seeders
git commit -m "feat: add normalized database baseline"
```

### Task 3: Backfill legacy data into the new model

**Files:**
- Create: `app/Console/Commands/MigrateLegacyEcclesiaData.php`
- Create: `database/seeders/LegacyDataSeeder.php`
- Create: `tests/Feature/LegacyImportTest.php`

- [ ] **Step 1: Write the import contract test**

```php
public function test_legacy_import_creates_tenant_and_admin(): void
{
    $this->artisan('legacy:import')->assertExitCode(0);
}
```

- [ ] **Step 2: Implement the import command in batches**

```php
public function handle(): int
{
    $this->info('Importing legacy data in batches...');
    return self::SUCCESS;
}
```

- [ ] **Step 3: Verify idempotency**

```bash
docker compose exec app php artisan legacy:import
docker compose exec app php artisan legacy:import
```

- [ ] **Step 4: Commit the import path**

```bash
git add app/Console/Commands database/seeders tests/Feature/LegacyImportTest.php
git commit -m "feat: add legacy data import path"
```
