# crmIgrejas Modernization Roadmap Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace the legacy EcclesiaCRM monolith with a new Laravel 12 + Vue 3 + PrimeVue 4 platform that is modular, localized for pt_BR first, and ready for multi-tenant church customization.

**Architecture:** The migration is split into independent tracks so each one can ship a working slice without blocking the others. Foundation work establishes the containerized Laravel runtime, queues, cache, observability, and developer workflow. The application layer is then rebuilt around normalized data, module boundaries under `app/Modules`, and a SPA frontend that consumes module APIs.

**Tech Stack:** Laravel 12, PHP 8.3, Docker Compose, MySQL 8.4, Redis, Memcached, Sanctum, Telescope, Vue 3, PrimeVue 4, Pinia, Vue Router, Vite, i18n.

**Current system findings:** The legacy app is a Slim 4 + Propel monolith with server-rendered pages, very wide tables, and mixed session/bootstrap concerns. The key legacy entry points are `src/index.php`, `src/api/index.php`, and `src/EcclesiaCRM/Bootstrapper.php`. The most important source schema files are `propel/main.schema.xml` and `src/mysql/install/Install.sql`. The observed pain points are asset loading fragility, lock-screen/session churn, language mismatch for admin users, and domain logic spread across unrelated tables.

---

### Task 1: Lock the execution sequence and repo boundaries

**Files:**
- Create: `docs/superpowers/plans/2026-07-26-laravel-12-foundation.md`
- Create: `docs/superpowers/plans/2026-07-26-database-normalization.md`
- Create: `docs/superpowers/plans/2026-07-26-modular-app-modules.md`
- Create: `docs/superpowers/plans/2026-07-26-vue3-primevue-ui.md`
- Create: `docs/superpowers/plans/2026-07-26-i18n-ptbr-first.md`
- Create: `docs/superpowers/plans/2026-07-26-devops-observability.md`

- [ ] **Step 1: Define the dependency order**

Use this order:
1. Foundation
2. Database normalization
3. Modular backend
4. SPA frontend
5. i18n
6. DevOps/observability hardening

- [ ] **Step 2: Confirm the minimal ship-ready slice**

The first useful slice is:
```text
Laravel 12 app boots in Docker
pt_BR is the default locale for admin users
Redis, Memcached, Sanctum, and Telescope are wired
Base module loading exists
SPA shell can call authenticated API endpoints
```

- [ ] **Step 3: Commit the roadmap**

```bash
git add docs/superpowers/plans/2026-07-26-*.md
git commit -m "docs: add crmIgrejas modernization roadmap"
```

### Task 2: Capture the legacy system analysis in the migration scope

**Files:**
- Modify: `docs/superpowers/plans/2026-07-26-modernization-roadmap.md`

- [ ] **Step 1: Record the legacy architecture that was analyzed**

```text
Legacy app: Slim 4 + Propel + server-rendered PHP
Primary entrypoints: src/index.php, src/api/index.php
Bootstrap/session flow: src/EcclesiaCRM/Bootstrapper.php
Schema sources: propel/main.schema.xml and src/mysql/install/Install.sql
Key pain points: wide tables, coupled session handling, server-rendered pages, mixed domain concerns, lock-loop behavior, locale mismatch, and asset pipeline fragility
```

- [ ] **Step 2: Record the target constraints**

```text
Target repo: crmIgrejas
Git remote: https://github.com/joaocg/crmIgrejas.git
Frontend: Vue 3 SPA with PrimeVue 4
Auth: Sanctum-based session/token auth
Infra: Docker Compose with mysql, redis, memcached, queue worker, scheduler, Telescope for local dev
```

- [ ] **Step 3: Re-commit the updated roadmap**

```bash
git add docs/superpowers/plans/2026-07-26-modernization-roadmap.md
git commit -m "docs: expand crmIgrejas migration scope"
```
