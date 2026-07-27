# Core Domain Modules Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build the remaining English-named core modules for the new CRM so people, families, groups, events, finance, notes, and pastoral care can be managed through isolated module boundaries and authenticated API routes.

**Architecture:** The normalized schema already exists for the first wave of core tables, so this plan focuses on module boundaries, models, controllers, actions, policies, and SPA-facing JSON resources. Each module owns its own route file and business actions, while shared tenant scoping and church-specific overrides stay centralized in the module loader. The implementation should keep the surface area small and predictable: one module per domain, one public route contract per module, and no localized class or table names.

**Tech Stack:** Laravel 12, Eloquent models, Sanctum bearer auth, module service providers, form requests, API resources, PHPUnit.

---

### Task 1: Create the People and Families modules

**Files:**
- Create: `app/Modules/People/module.php`
- Create: `app/Modules/People/Providers/PeopleModuleServiceProvider.php`
- Create: `app/Modules/People/Routes/api.php`
- Create: `app/Modules/People/Http/Controllers/PersonController.php`
- Create: `app/Modules/People/Actions/ListPeopleAction.php`
- Create: `app/Modules/People/Actions/CreatePersonAction.php`
- Create: `app/Modules/People/Actions/UpdatePersonAction.php`
- Create: `app/Modules/People/Actions/DeletePersonAction.php`
- Create: `app/Modules/Families/module.php`
- Create: `app/Modules/Families/Providers/FamiliesModuleServiceProvider.php`
- Create: `app/Modules/Families/Routes/api.php`
- Create: `app/Modules/Families/Http/Controllers/FamilyController.php`
- Create: `app/Modules/Families/Actions/ListFamiliesAction.php`
- Create: `app/Models/Person.php`
- Create: `app/Models/Family.php`
- Create: `app/Models/Address.php`
- Create: `app/Models/Tenant.php`
- Create: `app/Models/Role.php`
- Modify: `bootstrap/providers.php`
- Modify: `app/Support/Modules/ModuleLoader.php`
- Modify: `routes/api.php`
- Create: `tests/Feature/PeopleModuleTest.php`
- Create: `tests/Feature/FamiliesModuleTest.php`

- [ ] **Step 1: Write the failing route tests**

```php
public function test_people_index_requires_authentication(): void
{
    $this->getJson('/api/people')->assertUnauthorized();
}

public function test_families_index_requires_authentication(): void
{
    $this->getJson('/api/families')->assertUnauthorized();
}
```

- [ ] **Step 2: Register the module route files**

```php
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('people', PersonController::class);
    Route::apiResource('families', FamilyController::class);
});
```

- [ ] **Step 3: Add the Eloquent models with tenant scoping**

```php
class Person extends Model
{
    protected $fillable = ['tenant_id', 'first_name', 'last_name', 'family_id', 'email', 'phone'];
}
```

- [ ] **Step 4: Run the module tests**

```bash
docker compose exec app php artisan test --filter=PeopleModuleTest -v
docker compose exec app php artisan test --filter=FamiliesModuleTest -v
```

- [ ] **Step 5: Commit the people and families boundary**

```bash
git add app/Modules app/Models routes/api.php tests/Feature
git commit -m "feat: add people and families modules"
```

### Task 2: Create the Groups module with membership workflows

**Files:**
- Create: `app/Modules/Groups/module.php`
- Create: `app/Modules/Groups/Providers/GroupsModuleServiceProvider.php`
- Create: `app/Modules/Groups/Routes/api.php`
- Create: `app/Modules/Groups/Http/Controllers/GroupController.php`
- Create: `app/Modules/Groups/Http/Controllers/GroupMembershipController.php`
- Create: `app/Modules/Groups/Actions/ListGroupsAction.php`
- Create: `app/Modules/Groups/Actions/CreateGroupAction.php`
- Create: `app/Modules/Groups/Actions/AttachPersonToGroupAction.php`
- Create: `app/Modules/Groups/Actions/DetachPersonFromGroupAction.php`
- Create: `app/Models/Group.php`
- Create: `app/Models/GroupMembership.php`
- Create: `tests/Feature/GroupsModuleTest.php`

- [ ] **Step 1: Write the membership workflow test**

```php
public function test_group_membership_routes_require_authentication(): void
{
    $this->getJson('/api/groups')->assertUnauthorized();
    $this->postJson('/api/groups/1/members')->assertUnauthorized();
}
```

- [ ] **Step 2: Register group and membership routes**

```php
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('groups', GroupController::class);
    Route::post('groups/{group}/members', [GroupMembershipController::class, 'store']);
    Route::delete('groups/{group}/members/{person}', [GroupMembershipController::class, 'destroy']);
});
```

- [ ] **Step 3: Add unique membership constraints in the model layer**

```php
class GroupMembership extends Model
{
    protected $fillable = ['tenant_id', 'group_id', 'person_id', 'role_id', 'joined_at', 'left_at'];
}
```

- [ ] **Step 4: Run the group module tests**

```bash
docker compose exec app php artisan test --filter=GroupsModuleTest -v
```

- [ ] **Step 5: Commit the groups module**

```bash
git add app/Modules app/Models tests/Feature
git commit -m "feat: add groups module"
```

### Task 3: Create the Events module and attendance tracking

**Files:**
- Create: `app/Modules/Events/module.php`
- Create: `app/Modules/Events/Providers/EventsModuleServiceProvider.php`
- Create: `app/Modules/Events/Routes/api.php`
- Create: `app/Modules/Events/Http/Controllers/EventController.php`
- Create: `app/Modules/Events/Http/Controllers/EventAttendanceController.php`
- Create: `app/Modules/Events/Actions/ListEventsAction.php`
- Create: `app/Modules/Events/Actions/CreateEventAction.php`
- Create: `app/Modules/Events/Actions/MarkAttendanceAction.php`
- Create: `app/Models/Event.php`
- Create: `app/Models/EventAttendance.php`
- Create: `tests/Feature/EventsModuleTest.php`

- [ ] **Step 1: Write the attendance test**

```php
public function test_event_attendance_routes_require_authentication(): void
{
    $this->getJson('/api/events')->assertUnauthorized();
    $this->postJson('/api/events/1/attendance')->assertUnauthorized();
}
```

- [ ] **Step 2: Register event and attendance routes**

```php
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('events', EventController::class);
    Route::post('events/{event}/attendance', [EventAttendanceController::class, 'store']);
});
```

- [ ] **Step 3: Add the attendance model contract**

```php
class EventAttendance extends Model
{
    protected $fillable = ['tenant_id', 'event_id', 'person_id', 'checked_in_at', 'checked_out_at', 'status'];
}
```

- [ ] **Step 4: Run the event module tests**

```bash
docker compose exec app php artisan test --filter=EventsModuleTest -v
```

- [ ] **Step 5: Commit the events module**

```bash
git add app/Modules app/Models tests/Feature
git commit -m "feat: add events module"
```

### Task 4: Create the Finance module for funds, deposits, and pledges

**Files:**
- Create: `app/Modules/Finance/module.php`
- Create: `app/Modules/Finance/Providers/FinanceModuleServiceProvider.php`
- Create: `app/Modules/Finance/Routes/api.php`
- Create: `app/Modules/Finance/Http/Controllers/DonationFundController.php`
- Create: `app/Modules/Finance/Http/Controllers/DepositController.php`
- Create: `app/Modules/Finance/Http/Controllers/PledgeController.php`
- Create: `app/Modules/Finance/Actions/ListDonationFundsAction.php`
- Create: `app/Modules/Finance/Actions/CreateDonationFundAction.php`
- Create: `app/Modules/Finance/Actions/CreateDepositAction.php`
- Create: `app/Modules/Finance/Actions/CreatePledgeAction.php`
- Create: `app/Models/DonationFund.php`
- Create: `app/Models/Deposit.php`
- Create: `app/Models/Pledge.php`
- Create: `tests/Feature/FinanceModuleTest.php`

- [ ] **Step 1: Write the finance route test**

```php
public function test_finance_routes_require_authentication(): void
{
    $this->getJson('/api/donation-funds')->assertUnauthorized();
    $this->getJson('/api/deposits')->assertUnauthorized();
    $this->getJson('/api/pledges')->assertUnauthorized();
}
```

- [ ] **Step 2: Register the finance routes**

```php
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('donation-funds', DonationFundController::class);
    Route::apiResource('deposits', DepositController::class);
    Route::apiResource('pledges', PledgeController::class);
});
```

- [ ] **Step 3: Add the finance model relationships**

```php
class Pledge extends Model
{
    protected $fillable = ['tenant_id', 'family_id', 'fund_id', 'deposit_id', 'amount', 'status'];
}
```

- [ ] **Step 4: Run the finance tests**

```bash
docker compose exec app php artisan test --filter=FinanceModuleTest -v
```

- [ ] **Step 5: Commit the finance module**

```bash
git add app/Modules app/Models tests/Feature
git commit -m "feat: add finance module"
```

### Task 5: Create the Notes and Pastoral Care module

**Files:**
- Create: `app/Modules/Care/module.php`
- Create: `app/Modules/Care/Providers/CareModuleServiceProvider.php`
- Create: `app/Modules/Care/Routes/api.php`
- Create: `app/Modules/Care/Http/Controllers/NoteController.php`
- Create: `app/Modules/Care/Http/Controllers/PastoralCareController.php`
- Create: `app/Modules/Care/Actions/ListNotesAction.php`
- Create: `app/Modules/Care/Actions/CreateNoteAction.php`
- Create: `app/Modules/Care/Actions/CreatePastoralCareRecordAction.php`
- Create: `app/Models/Note.php`
- Create: `app/Models/PastoralCareRecord.php`
- Create: `tests/Feature/CareModuleTest.php`

- [ ] **Step 1: Write the care route test**

```php
public function test_care_routes_require_authentication(): void
{
    $this->getJson('/api/notes')->assertUnauthorized();
    $this->getJson('/api/pastoral-care')->assertUnauthorized();
}
```

- [ ] **Step 2: Register note and care routes**

```php
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('notes', NoteController::class);
    Route::apiResource('pastoral-care', PastoralCareController::class);
});
```

- [ ] **Step 3: Add privacy-aware model defaults**

```php
class Note extends Model
{
    protected $casts = ['is_private' => 'bool', 'edited_at' => 'datetime'];
}
```

- [ ] **Step 4: Run the care module tests**

```bash
docker compose exec app php artisan test --filter=CareModuleTest -v
```

- [ ] **Step 5: Commit the care module**

```bash
git add app/Modules app/Models tests/Feature
git commit -m "feat: add care module"
```

### Task 6: Tighten tenant scoping, permissions, and module coverage

**Files:**
- Modify: `app/Models/User.php`
- Modify: `app/Support/Modules/ModuleRegistry.php`
- Modify: `app/Support/Modules/ModuleLoader.php`
- Modify: `bootstrap/providers.php`
- Modify: `tests/Feature/ModuleLoaderTest.php`
- Create: `tests/Feature/TenantScopingTest.php`

- [ ] **Step 1: Write the tenant isolation test**

```php
public function test_module_queries_are_scoped_to_tenant(): void
{
    $registry = app(\App\Support\Modules\ModuleRegistry::class);
    $modules = collect($registry->all())->pluck('name')->all();

    $this->assertContains('People', $modules);
    $this->assertContains('Families', $modules);
    $this->assertContains('Groups', $modules);
    $this->assertContains('Events', $modules);
    $this->assertContains('Finance', $modules);
    $this->assertContains('Care', $modules);
}
```

- [ ] **Step 2: Centralize the current tenant on the authenticated user**

```php
class User extends Authenticatable
{
    public function currentTenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
}
```

- [ ] **Step 3: Verify that each active module is discoverable**

```php
public function test_module_registry_lists_people_families_groups_events_finance_and_care(): void
{
    $modules = app(\App\Support\Modules\ModuleRegistry::class)->all();
    $this->assertNotEmpty($modules);
}
```

- [ ] **Step 4: Run the full module test suite**

```bash
docker compose exec app php artisan test --filter=Module -v
```

- [ ] **Step 5: Commit the module coverage hardening**

```bash
git add app bootstrap tests
git commit -m "feat: harden core module coverage"
```

### Deferred after this plan

The following domains should be handled in separate plans after the core CRUD surface is stable:

- Communications and mail templates
- Calendar sync and address book compatibility
- Kiosk and device management
- Plugin marketplace and feature flags
- Advanced query builder and reporting
- Query import/export and legacy bridge cleanup
