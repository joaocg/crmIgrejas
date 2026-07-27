# crmIgrejas Modernization Roadmap Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace the legacy EcclesiaCRM monolith with a new Laravel 12 + Vue 3 + PrimeVue 4 platform that is modular, localized for pt_BR first, and ready for multi-tenant church customization.

**Architecture:** The migration is split into independent tracks so each one can ship a working slice without blocking the others. Foundation work establishes the containerized Laravel runtime, queues, cache, observability, and developer workflow. The application layer is then rebuilt around normalized data, module boundaries under `app/Modules`, and a SPA frontend that consumes module APIs.

**Tech Stack:** Laravel 12, PHP 8.4, Docker Compose, MySQL 8.4, Redis, Memcached, Sanctum, Telescope, Vue 3, PrimeVue 4, Pinia, Vue Router, Vite, i18n.

**Current system findings:** The legacy app is a Slim 4 + Propel monolith with server-rendered pages, very wide tables, and mixed session/bootstrap concerns. The key legacy entry points are `src/index.php`, `src/api/index.php`, and `src/EcclesiaCRM/Bootstrapper.php`. The most important source schema files are `propel/main.schema.xml` and `src/mysql/install/Install.sql`. The observed pain points are asset loading fragility, lock-screen/session churn, language mismatch for admin users, and domain logic spread across unrelated tables.

**Current repo findings in `crmIgrejas`:** The new project already boots as Laravel 12 inside Docker, uses `pt_BR` as the primary locale, exposes authenticated API access through Sanctum, keeps Telescope restricted to development, and includes Redis, Memcached, queue, and scheduler services in the compose stack. The current codebase also already has the module loader, the `Core` and `Users` module boundaries, a normalized baseline database, and a Vue 3 + PrimeVue SPA shell. The next work is not to re-bootstrap the app, but to keep expanding the domain model and module surface while the legacy repo remains the source of truth for missing business rules.

**Current migration priority:** 1) normalize any remaining legacy coupling that still matters to the target product, 2) grow the module-by-module CRUD surface in English names only, 3) refine the SPA UX for complex workflows, and 4) keep the import path available only as a bridge from the legacy database.

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

The foundation, i18n, and observability slices are already implemented in `crmIgrejas`; keep these tasks in the roadmap as guardrails, not as active implementation work.

- [ ] **Step 2: Confirm the minimal ship-ready slice**

The first useful slice is:
```text
Laravel 12 app boots in Docker
pt_BR is the default locale for admin users
Redis, Memcached, Sanctum, and Telescope are wired
Base module loading exists
SPA shell can call authenticated API endpoints
```

In the current repository, this slice is already functional. The remaining milestone is the first complete business workflow built on top of the normalized model and module boundaries.

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

The legacy analysis should stay in the roadmap so future module work can be traced back to the original schema and behavior instead of guessing from the new code alone.

- [ ] **Step 2: Record the target constraints**

```text
Target repo: crmIgrejas
Git remote: https://github.com/joaocg/crmIgrejas.git
Frontend: Vue 3 SPA with PrimeVue 4
Auth: Sanctum-based session/token auth
Infra: Docker Compose with mysql, redis, memcached, queue worker, scheduler, Telescope for local dev
```

These constraints describe the target repository, not the legacy monolith. The new project should not inherit old naming patterns, table names, or module names.

- [ ] **Step 3: Re-commit the updated roadmap**

```bash
git add docs/superpowers/plans/2026-07-26-modernization-roadmap.md
git commit -m "docs: expand crmIgrejas migration scope"
```
