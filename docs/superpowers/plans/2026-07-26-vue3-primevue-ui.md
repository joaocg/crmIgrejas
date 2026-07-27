# Vue 3 PrimeVue UI Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace the legacy server-rendered UI with a clean SPA that uses Vue 3, PrimeVue 4, and a modular page shell to handle complex CRUD flows without forcing the user through full reloads.

**Architecture:** The frontend is a true SPA with a single Laravel-served entrypoint, authenticated API calls, reusable PrimeVue layout primitives, and route-level code splitting by module. The first screen is the authenticated shell, then each module expands into list, create, edit, and detail views built from shared form and table building blocks. The visual system should look intentionally simple, bright, and modern rather than resembling the legacy admin UI.

**Tech Stack:** Vue 3, Vite, PrimeVue 4, Vue Router, Pinia, Axios, vue-i18n, CSS variables, and a small tokenized design system.

---

### Task 1: Bootstrap the SPA shell

**Files:**
- Modify: `package.json`
- Modify: `vite.config.js`
- Create: `resources/js/app.js`
- Create: `resources/js/App.vue`
- Create: `resources/js/router/index.js`
- Create: `resources/js/stores/auth.js`
- Create: `resources/js/layouts/AppShell.vue`
- Create: `resources/js/pages/DashboardPage.vue`
- Create: `resources/js/pages/auth/LoginPage.vue`
- Modify: `resources/views/welcome.blade.php`
- Modify: `routes/web.php`

- [x] **Step 1: Write the frontend smoke test**

```php
public function test_spa_shell_is_served(): void
{
    $this->get('/')->assertOk();
}
```

- [x] **Step 2: Implement the shell entrypoint**

```js
import { createApp } from 'vue';
import router from './router';
createApp(App).use(router).mount('#app');
```

- [x] **Step 3: Load the SPA from the Laravel view**

```blade
<div id="app"></div>
@vite(['resources/js/app.js'])
```

- [x] **Step 4: Verify the shell builds and renders**

```bash
docker run --rm -e HOME=/tmp -e npm_config_cache=/tmp/npm-cache -u $(id -u):$(id -g) -v /Volumes/nvme/Projetos/Joao/ecclesiacrm/crmIgrejas:/workspace -w /workspace node:22-bookworm npm run build
docker compose exec app php artisan test --filter=SmokeTest
```

- [x] **Step 5: Commit the SPA shell**

```bash
git add package.json vite.config.js resources/js resources/views/welcome.blade.php routes/web.php
git commit -m "feat: add vue spa shell"
```

### Task 2: Build the PrimeVue application shell and navigation

**Files:**
- Create: `resources/js/components/navigation/AppSidebar.vue`
- Create: `resources/js/components/navigation/AppTopbar.vue`
- Create: `resources/js/components/forms/BaseTextField.vue`
- Create: `resources/js/components/forms/BaseSelectField.vue`
- Create: `resources/js/components/tables/BaseDataTable.vue`
- Create: `resources/js/styles/theme.css`
- Modify: `resources/js/app.js`
- Modify: `resources/js/layouts/AppShell.vue`
- Modify: `resources/js/pages/DashboardPage.vue`
- Modify: `routes/web.php`
- Create: `tests/Feature/PrimeVueShellTest.php`

- [x] **Step 1: Write a layout regression test**

```php
public function test_dashboard_and_login_routes_serve_the_spa_shell(): void
{
    $this->get('/dashboard')->assertOk()->assertSee('<div id="app"></div>', false);
    $this->get('/login')->assertOk()->assertSee('<div id="app"></div>', false);
}
```

- [x] **Step 2: Implement shared PrimeVue wrappers**

```vue
<script setup>
defineProps({
  modelValue: [String, Number, null],
  label: String,
});
</script>
```

- [x] **Step 3: Verify the shell keeps CRUD affordances consistent**

```bash
docker run --rm -e HOME=/tmp -e npm_config_cache=/tmp/npm-cache -u $(id -u):$(id -g) -v /Volumes/nvme/Projetos/Joao/ecclesiacrm/crmIgrejas:/workspace -w /workspace node:22-bookworm npm run build
```

- [x] **Step 4: Commit the shell components**

```bash
git add resources/js
git commit -m "feat: add primevue app shell components"
```

### Task 3: Structure CRUD pages around module resources

**Files:**
- Create: `resources/js/pages/modules/users/UserListPage.vue`
- Create: `resources/js/pages/modules/users/UserCreatePage.vue`
- Create: `resources/js/pages/modules/users/UserEditPage.vue`
- Create: `resources/js/pages/modules/users/UserShowPage.vue`
- Create: `resources/js/api/http.js`
- Create: `resources/js/api/modules/users.js`

- [ ] **Step 1: Write page-level API tests**

```php
public function test_users_index_returns_json(): void
{
    $this->getJson('/api/users')->assertUnauthorized();
}
```

- [ ] **Step 2: Implement list/create/edit/show pages**

```vue
<template>
  <AppShell>
    <DataTable />
  </AppShell>
</template>
```

- [ ] **Step 3: Verify the build and route split**

```bash
docker compose exec app npm run build
docker compose exec app php artisan test -v
```

- [ ] **Step 4: Commit the CRUD page structure**

```bash
git add resources/js/api resources/js/pages
git commit -m "feat: scaffold module CRUD pages"
```
