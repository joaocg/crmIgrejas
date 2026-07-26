# pt_BR First Internationalization Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make pt_BR the primary language of the system for the admin user while keeping English available as fallback and making the frontend translation-ready for future languages.

**Architecture:** Localization is centralized in Laravel config, translated UI strings are grouped by domain, and the browser language only overrides the default after the user profile is loaded. This keeps the app predictable for church staff while still allowing tenant-specific language strategies later.

**Tech Stack:** Laravel localization, vue-i18n, JSON translation files, user profile preference storage.

---

### Task 1: Set Laravel defaults for pt_BR

**Files:**
- Modify: `.env.example`
- Modify: `config/app.php`
- Modify: `app/Models/User.php`
- Modify: `app/Providers/AppServiceProvider.php`

- [ ] **Step 1: Write the locale regression test**

```php
public function test_locale_defaults_to_pt_br(): void
{
    $this->assertSame('pt_BR', config('app.locale'));
}
```

- [ ] **Step 2: Set the framework defaults**

```php
'locale' => env('APP_LOCALE', 'pt_BR'),
'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),
'faker_locale' => env('APP_FAKER_LOCALE', 'pt_BR'),
```

- [ ] **Step 3: Verify the locale is applied to the admin user context**

```php
public function boot(): void
{
    //
}
```

- [ ] **Step 4: Run the locale test in Docker**

```bash
docker compose exec app php artisan test --filter=Locale -v
```

- [ ] **Step 5: Commit the pt_BR defaults**

```bash
git add .env.example config/app.php app/Models/User.php app/Providers/AppServiceProvider.php
git commit -m "feat: set pt_br as the primary locale"
```

### Task 2: Add translation catalogs for core UI domains

**Files:**
- Create: `lang/pt_BR/auth.php`
- Create: `lang/pt_BR/navigation.php`
- Create: `lang/pt_BR/dashboard.php`
- Create: `lang/pt_BR/forms.php`
- Create: `lang/en/auth.php`
- Create: `lang/en/navigation.php`
- Create: `lang/en/dashboard.php`
- Create: `lang/en/forms.php`
- Create: `resources/js/i18n/index.js`
- Create: `resources/js/i18n/pt-BR.json`
- Create: `resources/js/i18n/en.json`

- [ ] **Step 1: Write translation coverage tests**

```php
public function test_core_translation_files_exist(): void
{
    $this->assertFileExists(lang_path('pt_BR/auth.php'));
}
```

- [ ] **Step 2: Add translation keys for the first modules**

```php
return [
    'login' => 'Entrar',
    'password' => 'Senha',
];
```

- [ ] **Step 3: Verify the frontend reads the same keys**

```js
export default {
  login: 'Entrar',
};
```

- [ ] **Step 4: Commit the initial catalogs**

```bash
git add lang resources/js/i18n
git commit -m "feat: add pt_br translation catalogs"
```

### Task 3: Persist user language preference

**Files:**
- Create: `database/migrations/2026_07_26_000009_add_locale_to_users_table.php`
- Modify: `app/Models/User.php`
- Create: `app/Http/Controllers/LocaleController.php`
- Create: `tests/Feature/LocalePreferenceTest.php`

- [ ] **Step 1: Write a preference test**

```php
public function test_admin_user_can_store_locale_preference(): void
{
    $this->assertTrue(true);
}
```

- [ ] **Step 2: Store the locale on the user record**

```php
$user->locale = 'pt_BR';
$user->save();
```

- [ ] **Step 3: Verify preference resolution order**

```text
user.locale > tenant.locale > APP_LOCALE > fallback_locale
```

- [ ] **Step 4: Commit preference persistence**

```bash
git add database/migrations app/Models/User.php app/Http/Controllers tests
git commit -m "feat: persist user locale preference"
```

