# Migração EcclesiaCRM → crmIgrejas — Mapa de Padrões (Legado e Novo)

> Documento de referência produzido a partir da leitura direta dos dois códigos-fonte em
> `/Volumes/nvme/Projetos/Joao/ecclesiacrm` (legado) e `/Volumes/nvme/Projetos/Joao/ecclesiacrm/crmIgrejas` (novo).
> Serve de base para o planejamento da continuação da migração.
>
> Data do levantamento: 2026-07-29.

---

## 1. Panorama dos dois sistemas

| Dimensão | Legado (EcclesiaCRM 9.0.0) | Novo (crmIgrejas) |
|---|---|---|
| Backend | PHP ≥8.1, **Slim 4** + **Propel 2 (beta4)** + PHP-DI 7 | PHP ≥8.2, **Laravel 12** + **Eloquent** |
| Renderização | Server-side (`.php` puro com `<?= ?>`) via slim/php-view | SPA — Blade só entrega o shell (`welcome.blade.php`) |
| Frontend | jQuery + **AdminLTE 3** + Bootstrap 4 + DataTables 1.12 | **Vue 3** (`<script setup>`) + **PrimeVue 4 (Aura)** + Tailwind 4 |
| Build | **Grunt** (`Gruntfile.js`, 33k) + SCSS → `skin/ecclesiacrm.min.css` | **Vite 7** + `laravel-vite-plugin` |
| Estado no front | `window.CRM` global (objeto injetado no HTML) | **Pinia** (`stores/auth.js`, `stores/navigation.js`) |
| Roteamento front | Rotas server-side (`/v2/...`) | **vue-router 4** (history mode) com guards |
| Auth | Sessão PHP + JWT por request (`jimtools/jwt-auth`) | **Sanctum** (bearer token em `localStorage`) |
| Multi-tenant | Não existe — 1 instância = 1 igreja | **`tenant_id` em todas as tabelas** de domínio |
| i18n | gettext (`_()`, `.po/.pot`) + i18next no JS | Arquivos `lang/{en,pt_BR}/*.php` + JSON no front (`i18n/index.js`) |
| Idioma primário | Inglês | **pt-BR primeiro**, inglês como fallback |
| Infra | Apache/Docker legado, MySQL | Docker Compose: app, web(nginx), queue, scheduler, mysql 8.4, redis, memcached |
| Observabilidade | `monolog` + logs em `src/logs` | **Telescope** (restrito a dev), Pail, filas Redis |

### Volume de código

| Artefato | Legado | Novo |
|---|---|---|
| Arquivos PHP (sem vendor) | **1.018** | ~120 |
| Tabelas no schema | **77** (`propel/main.schema.xml`) | **25** (10 migrations) |
| Classes de modelo | **148** (Propel gerado) | **18** (Eloquent) |
| Controllers de API | **44** (`APIControllers/`) | 15 (espalhados em módulos) |
| Controllers de View | **31** (`VIEWControllers/`) | — (SPA) |
| Arquivos de rota | 43 (api) + ~28 (v2) | 1 global + 1 por módulo |
| Templates / páginas | **97** templates PHP | **36** páginas `.vue` |
| Arquivos JS | **107** em `skin/js` | 5 componentes + 10 clients de API |
| Relatórios (PDF/CSV) | **30** (`src/Reports/`) | **0** — ainda não migrado |
| Plugins | **17** (`src/Plugins/`) | 0 — substituído por módulos |
| Testes automatizados | PHPUnit residual | **37** testes de feature |

---

## 2. Padrões do LEGADO — Backend

### 2.1 Três entrypoints Slim independentes

O legado não tem um único front controller. Ele tem três apps Slim que sobem separadamente:

| Entrypoint | Papel | Base path |
|---|---|---|
| `src/index.php` | Roteador "mágico" — converte `/list-events` em `/v2/calendar/events/list` por `dashesToCamelCase` | `/` |
| `src/v2/index.php` | Páginas HTML autenticadas | `/v2` |
| `src/api/index.php` | API JSON consumida por AJAX | `/api` |

Outros entrypoints menores: `src/session/index.php` (login/lock), `src/setup/`, `calendarserver.php` e `addressbookserver.php` (SabreDAV — CalDAV/CardDAV).

### 2.2 Padrão de rota (Slim `RouteCollectorProxy`)

Cada arquivo em `src/api/routes/<domínio>/` agrupa rotas e aponta para um controller via string:

```php
// src/api/routes/people/people-persons.php
$app->group('/persons', function (RouteCollectorProxy $group) {
    $group->get('/search/{query}', PeoplePersonController::class . ":searchPerson");
    $group->post('/{personId:[0-9]+}/verify', PeoplePersonController::class . ":verifyPerson");
});
```

Características:
- **Rotas verbosas e não-REST**: `/persons/verify/{id}/now`, `/persons/volunteers/{id}` — verbos no path.
- Comentários `@!` / `#!` acima de cada rota servem como documentação inline (não gera OpenAPI).
- Os arquivos são carregados por `require_once` sequencial no `index.php` — **ordem importa**.

### 2.3 Padrão de controller

```php
class PeoplePersonController
{
    private $container;
    public function __construct(ContainerInterface $container) { $this->container = $container; }

    public function searchPerson(ServerRequest $request, Response $response, array $args): Response
    { /* query Propel + monta array + $response->withJson(...) */ }
}
```

Problemas estruturais:
- Sem camada de serviço/action — **regra de negócio dentro do controller**, misturada com query e formatação.
- Sem Form Request/validação declarativa — validação manual com `InputUtils`.
- Sem API Resource — o array de saída é montado à mão em cada método.
- Acesso a `SessionUser::getUser()` estático dentro do controller (acoplamento global à sessão).
- Uso direto de `Propel\Runtime\Propel::getConnection()` com SQL cru em vários pontos.

### 2.4 Autenticação e autorização

- **Sessão PHP** valida a entrada: `if (SessionUser::getId() == 0) RedirectUtils::Redirect('session/login');` — antes de qualquer middleware Slim.
- **JWT** só protege `/api`, com segredo por usuário (`getJwtSecretForApi()`) e uma **lista de exceções hardcoded** de ~13 paths (`$jwtIgnoreRoutes` em `src/api/index.php`).
- **Permissões**: ~40 métodos booleanos em `User.php` (`isAddRecordsEnabled()`, `isFinanceEnabled()`, `isPastoralCareEnabled()`, `isGdrpDpoEnabled()`, `isEnabledSecurity($nome)`, `isSecurityEnableForPlugin($nome, $bitmask)`). É um modelo de **flags booleanas + bitmask**, não RBAC.
- Middleware ad-hoc mantém `$_SESSION['tLastOperation']` vivo para não deslogar durante AJAX — sintoma do acoplamento sessão↔API.

### 2.5 Camada de dados (Propel)

- Schema declarado em `propel/main.schema.xml` (77 tabelas), classes geradas em `src/EcclesiaCRM/model/EcclesiaCRM/` (148 arquivos).
- **Nomenclatura com sufixo de tabela**: `person_per`, `family_fam`, `note_nte`, `pledge_plg`, `group_grp`, `deposit_dep`, `record2property_r2p`, `person2group2role_p2g2r`.
- **Tabelas muito largas** — `person_per` e `family_per` carregam dezenas de colunas incluindo campos customizados via tabelas paralelas (`person_custom` + `person_custom_master`).
- Tabelas SabreDAV misturadas no mesmo schema (`calendars`, `cards`, `addressbooks`, `principals`, `schedulingobjects`).
- Padrão de acesso: `PersonQuery::create()->filterByX()->find()` + `Criteria` do Propel; `Map\*TableMap` para nomes de coluna em joins manuais.

### 2.6 Domínios funcionais do legado (superfície a migrar)

Extraídos de `MenuBar.php`, `src/v2/routes/` e `src/api/routes/`:

| Domínio | Rotas legadas |
|---|---|
| Dashboard | `v2/dashboard` |
| Pessoas & Famílias | `v2/people/*`, `v2/personlist/*`, `v2/familylist/*` |
| Grupos | `v2/group/*` (+ tipos, papéis, gerentes de grupo) |
| Eventos / Calendário | `v2/calendar`, `/events/list`, `/events/names`, `/events/checkin` |
| Financeiro | `v2/deposit/*`, fundos, promessas (pledges), pagamentos, e-Give |
| Fundraiser | `v2/fundraiser/*` (leilões, itens, paddle numbers) |
| Cuidado pastoral | `v2/pastoralcare/*` |
| Escola dominical | `v2/sundayschool/*` |
| Voluntariado | `v2/volunteer/*` |
| Kiosk (check-in) | `v2/sidebar/kioskmanager`, `api/kiosks` |
| eDrive (documentos) | `v2/edrive/*` + filemanager + CKEditor |
| Mapa | `v2/map/{id}` (Google Maps / OpenStreetMap / Nominatim) |
| Carrinho (seleção) | `v2/cart/*` — seleção transversal de pessoas |
| Query builder | `v2/query/*` — relatórios SQL parametrizados |
| GDPR/LGPD | `v2/gdpr/*`, listas de inativos, estrutura de dados |
| Sistema/Admin | `v2/system/*`, settings, custom fields, backup/restore, upgrade, timer jobs |
| Usuários & papéis | `v2/users/*`, `api/user/*` |
| Plugins | 17 plugins, majoritariamente widgets de dashboard + MailChimp + Jitsi |
| Relatórios | 30 geradores PDF/CSV (`src/Reports/`) — TCPDF, dompdf, PhpSpreadsheet, PhpWord |

---

## 3. Padrões do LEGADO — Frontend

### 3.1 Pipeline de assets

- **Grunt** baixa e copia libs de `node_modules` para `src/skin/external/` (30+ pastas: jquery, jquery-ui, adminlte, bootstrap, datatables, select2, fullcalendar, ckeditor, leaflet, chartjs, moment, inputmask, iCheck, flot…).
- SCSS parcial por tela em `src/skin/scss/` (`_calendar.scss`, `_dashboard_plungis.scss`, `_cartDropdown.scss`…) → compilado em `ecclesiacrm.min.css`.
- **Não há bundler de JS**: cada tela inclui `<script>` avulsos. `src/skin/js/` tem 107 arquivos organizados por domínio (`people/PersonView.js`, `finance/`, `calendar/`…).

### 3.2 Template = PHP + HTML + AdminLTE

```php
// src/v2/templates/people/personlist.php
require $sRootDocument . '/Include/Header.php';
?>
<div class="card card-outline card-primary">
  <div class="card-header"><h3 class="card-title"><?= _('Person Directory') ?></h3></div>
  <div class="card-body">
    <table id="personlist" class="table table-sm table-hover data-table">…</table>
  </div>
</div>
```

- Estrutura fixa: `Include/Header.php` → conteúdo → `Include/Footer.php`.
- Variáveis vêm do VIEWController via array de dados (`$sMode`, `$bNotGDRP`, `$sRootPath`).
- Traduções inline com `_()` (gettext), **misturadas ao markup**.
- Templates gigantes: `personview.php` tem **1.852 linhas** — HTML + PHP + JS inline no mesmo arquivo.

### 3.3 Estado global `window.CRM`

Injetado em `Include/Header-function.php` (~50 chaves), é o "store" do legado:

```js
window.CRM = {
  root, lang, locale, shortLocale, currency, maxUploadSize, datePickerformat,
  showTooltip, showCart, sMapProvider, iMapZoom, sMapKey, iPersonId,
  sEntityName, bDarkMode, sLightDarkMode, jwtToken, all_plugins_i18keys,
  plugin: { dataTable: { language: {...} } }, …
}
```

### 3.4 Cliente HTTP: `CRMJSOM.js`

```js
window.CRM.APIRequest = function (options, callback, callbackError) {
  fetch(window.CRM.root + "/api/" + options.path, {
    method: options.method || "GET",
    headers: { 'Content-Type': ..., 'Authorization': 'Bearer ' + window.CRM.jwtToken },
    body: options.data
  }).then(res => res.json()).then(callback).catch(callbackError);
}
```

Padrão **callback-based** (não Promise/async-await), com variantes `APIDownloadRequest`, `CRMJSOM_MAIL.js`.

### 3.5 Tabelas e navegação

- **DataTables** faz tudo: paginação, ordenação, busca, colvis, export (copy/pdf/print). Configuração global em `window.CRM.plugin.dataTable`.
- Menu lateral construído em PHP (`MenuBar.php`, 984 linhas) com árvore de `new Menu(label, icon, url, visível?, pai)` — **visibilidade calculada por permissão em tempo de render**.
- Navegação = full page reload a cada clique.

---

## 4. Padrões do NOVO — Backend (Laravel 12)

### 4.1 Arquitetura modular por manifesto

Estrutura em `app/Modules/<Nome>/`, com 11 módulos: `Core`, `Users`, `People`, `Families`, `Groups`, `Events`, `Finance`, `Care`, `Communications`, `Calendar`, `Kiosk`.

Cada módulo declara um manifesto:

```php
// app/Modules/People/module.php
return [
    'name' => 'People',
    'enabled' => true,
    'providers'   => [App\Modules\People\Providers\PeopleModuleServiceProvider::class],
    'route_files' => [__DIR__.'/Routes/api.php'],
];
```

Descoberta e carga em `app/Support/Modules/`:
- `ModuleRegistry` — faz `glob(app/Modules/*/module.php)`, monta `ModuleDefinition` (readonly), ordena por nome.
- `ModuleLoader` — `registerProviders()` (lança `RuntimeException` se o provider não existir) e `loadRoutes()`.
- **Override por igreja**: `ModuleLoader::loadOverrides($modulo, $churchSlug)` carrega `app/Modules/<Mod>/Churches/<slug>/*.php`. Já implementado para `Users`.
- `ModuleServiceProvider` (app-level) orquestra tudo no boot.

### 4.2 Camadas por módulo

```
app/Modules/People/
├── module.php                    ← manifesto
├── Providers/PeopleModuleServiceProvider.php
├── Routes/api.php                ← Route::prefix('api')->apiResource(...)
├── Http/Controllers/PersonController.php
└── Actions/
    ├── ListPeopleAction.php
    ├── CreatePersonAction.php
    ├── UpdatePersonAction.php
    └── DeletePersonAction.php
```

Convenções observadas em todo o código:
- `declare(strict_types=1);` no topo de **todos** os arquivos.
- Classes `final` (controllers, actions, models); `final readonly` para value objects.
- **Actions de responsabilidade única** com um único método `execute(...)`, injetadas por type-hint no método do controller.
- Controllers **não estendem** `Controller` base nos módulos (só os de `app/Http/Controllers/Api`).

### 4.3 Padrão de rota (REST puro)

```php
// app/Modules/People/Routes/api.php
Route::prefix('api')->middleware('api')->group(function (): void {
    Route::middleware('auth:sanctum')->apiResource('people', PersonController::class);
});
```

`routes/api.php` global só tem o essencial: `POST /api/auth/login`, `POST /api/auth/logout`, `GET /api/navigation`, `GET /api/me`.
`routes/web.php` tem `Route::fallback()` → `view('welcome')`, entregando a SPA para qualquer rota.

### 4.4 Isolamento multi-tenant

Padrão repetido em todos os controllers:

```php
// leitura: sempre filtrada
$action->execute((int) $request->user()->tenant_id);

// escrita: FK validada dentro do tenant
Rule::exists('families', 'id')->where(fn ($q) => $q->where('tenant_id', $request->user()->tenant_id))

// acesso a registro: 404, não 403
abort_unless((int) $person->tenant_id === (int) $request->user()->tenant_id, 404);
```

Coberto por `tests/Feature/TenantScopingTest.php`.

### 4.5 Modelo de dados normalizado

25 tabelas, nomes limpos no plural em inglês:

```
tenants, roles, users, sessions, personal_access_tokens,
addresses, families, persons, contacts,
groups, group_memberships, events, event_attendances,
donation_funds, deposits, pledges,
notes, pastoral_care_records,
module_definitions, module_settings,
cache, cache_locks, jobs, job_batches, failed_jobs
```

Ganhos sobre o legado:
- `addresses` extraída de `person_per`/`family_fam` (era colunas repetidas).
- `contacts` normalizada (era `per_HomePhone`, `per_CellPhone`, `per_Email`… como colunas).
- Sufixos `_per`/`_fam`/`_nte` eliminados.
- Sem tabelas SabreDAV no schema de domínio.

Models Eloquent: `$fillable` explícito, `casts()` como método (padrão Laravel 11+), relações tipadas (`BelongsTo`, `HasMany`).

### 4.6 Autenticação e autorização

- `AuthTokenController::store` — valida credenciais, checa `$user->active`, retorna `{token, token_type: 'Bearer', user}` com `role` carregada.
- Sanctum `personal_access_tokens`, nome do token: `spa`.
- **RBAC por JSON**: `roles.permissions` é um mapa `{"navigation.people": true}` com curinga `{"*": true}` para admin.
- `NavigationController` **filtra o menu no servidor** conforme as permissões, e o front repete a checagem nos guards do router.

### 4.7 Ponte com o legado

`app/Support/Legacy/LegacyDataImporter` lê uma connection `legacy` (MySQL do EcclesiaCRM), garante tenant/role/admin default e importa `family_fam` → `families` e `person_per` → `persons` em chunks de 100. Coberto por `LegacyImportTest`. **É bridge, não destino.**

### 4.8 Infra e observabilidade

`docker-compose.yml`: `app` (php-fpm), `web` (nginx), `queue`, `scheduler`, `mysql` 8.4, `redis`, `memcached` + volumes. Telescope restrito a ambiente local. Testes de infra em `tests/Feature/DevOps/` (`NginxConfigTest`, `ObservabilityTest`, `RuntimeConfigTest`).

---

## 5. Padrões do NOVO — Frontend (Vue 3 + PrimeVue 4)

### 5.1 Bootstrap

```js
// resources/js/app.js
app.use(pinia);
app.use(router);
app.use(PrimeVue, { theme: { preset: Aura, options: { cssLayer: false, darkModeSelector: 'system' } } });
app.component('PButton', Button); // PrimeVue registrado com prefixo P*
```

Componentes PrimeVue registrados globalmente com prefixo `P`: `PButton`, `PCard`, `PColumn`, `PDataTable`, `PDropdown`, `PInputText`, `PPassword`.

### 5.2 Organização

```
resources/js/
├── app.js, App.vue, bootstrap.js
├── api/
│   ├── http.js            ← axios instance + interceptor de Bearer token
│   ├── auth.js
│   └── modules/*.js       ← 1 arquivo por módulo, funções nomeadas
├── stores/                ← Pinia: auth.js, navigation.js
├── router/index.js        ← rotas + guards
├── navigation/siteNavigation.js
├── layouts/AppShell.vue
├── components/{forms,tables,navigation}/
├── pages/modules/<dominio>/<X>{List,Show,Create,Edit}Page.vue
├── i18n/index.js + locales/{pt-BR,en}.json
└── styles/theme.css
```

### 5.3 Cliente de API

```js
// resources/js/api/http.js
const http = axios.create({ baseURL: '/api', withCredentials: true,
  headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' } });
http.interceptors.request.use((config) => { /* injeta Bearer de localStorage */ });

// resources/js/api/modules/people.js
export function listPeople(params = {}) { return http.get('/people', { params }); }
export function showPerson(id)          { return http.get(`/people/${id}`); }
export function createPerson(payload)   { return http.post('/people', payload); }
```

Padrão: **um módulo de API por domínio, funções nomeadas, sem classe**.

### 5.4 Roteamento e permissões

```js
{ path: '/people', name: 'people.index',
  component: () => import('../pages/modules/people/PeopleListPage.vue'),
  meta: { requiresAuth: true, ability: 'navigation.people' } }
```

- **Lazy loading** em todas as rotas (`() => import(...)`).
- Nomenclatura `<dominio>.<acao>`: `people.index|create|edit|show`.
- Guard único `router.beforeEach`: hidrata auth se houver token → bloqueia `guestOnly` → redireciona para `/login?redirect=` → checa `ability` contra `user.role.permissions`.
- `scrollBehavior` sempre volta ao topo.
- `placeholderRoutes` gera automaticamente páginas-stub (`ModuleLandingPage.vue`) para itens de menu ainda não implementados.

### 5.5 Padrão de página

```vue
<template>
  <AppShell :title="t('people.title')">
    <div class="hero-panel">…</div>
    <PCard>
      <template #title>{{ t('people.registered') }}</template>
      <template #content>
        <BaseDataTable :rows="people" :rows-per-page="8">
          <PColumn field="first_name" :header="t('people.columns.first_name')" />
        </BaseDataTable>
      </template>
    </PCard>
  </AppShell>
</template>

<script setup>
onMounted(async () => {
  try { const r = await listPeople(); people.value = r.data?.data ?? r.data ?? []; }
  catch { error.value = t('forms.messages.auth_required_load'); people.value = [/* fallback */]; }
});
</script>
```

Convenções: `<script setup>`, Composition API, `ref` local (sem store por página), `AppShell` como layout, `hero-panel` como cabeçalho de contexto, `t()` para todo texto visível.

### 5.6 Design system

- **Design tokens em CSS custom properties** (`resources/css/app.css`): `--app-bg`, `--app-surface`, `--app-border`, `--app-text`, `--app-muted`, `--app-primary`, `--app-primary-strong`, `--app-accent`, `--app-shadow`.
- Estética: superfícies translúcidas com `backdrop-filter: blur(18px)`, `border-radius` 16–28px, gradientes radiais no `body`, sombras longas.
- Fonte **Manrope** (via Google Fonts — CDN externo).
- `theme.css` complementa com classes utilitárias de domínio: `.app-field`, `.app-nav__section`, `.app-topbar`, `.base-data-table`.
- Shell: grid `280px | 1fr`, sidebar + topbar sticky.

### 5.7 i18n

`i18n/index.js` é um micro-runtime próprio (não vue-i18n): resolve o locale de `document.documentElement.lang` → `window.__APP_LOCALE__` → `'pt-BR'`; `t(key, replacements)` faz lookup por caminho com fallback para inglês e interpolação `:{token}`. Catálogos de 601 linhas cada em `locales/pt-BR.json` e `locales/en.json`, validados por `TranslationCatalogTest`.

---

## 6. Tabela de correspondência Legado → Novo

| Conceito | Legado | Novo |
|---|---|---|
| Definir rota | `$group->get('/x', Ctrl::class.":metodo")` | `Route::apiResource('x', Ctrl::class)` |
| Lógica de negócio | Dentro do método do controller | `Actions/XAction::execute()` |
| Validação | `InputUtils` manual | `$request->validate([...])` com `Rule::exists` tenant-aware |
| Query | `PersonQuery::create()->filterBy...` | `Person::query()->where(...)->with(...)` |
| Sessão do usuário | `SessionUser::getUser()` (estático global) | `$request->user()` (injetado) |
| Permissão | `$user->isFinanceEnabled()` (flag/bitmask) | `role.permissions['navigation.finance']` (JSON) |
| Menu | `MenuBar.php` construindo árvore em PHP | `NavigationController` + `siteNavigation.js` |
| Página | `src/v2/templates/**.php` + `Header.php` | `pages/modules/**/XPage.vue` + `AppShell` |
| Tabela de dados | DataTables jQuery | `BaseDataTable` → `PDataTable` |
| Chamada HTTP | `CRM.APIRequest({path}, cb, errCb)` | `await listX()` (axios + Promise) |
| Estado global | `window.CRM` | Pinia stores |
| Tradução | `_('texto')` gettext no template | `t('chave.aninhada')` + JSON |
| Extensibilidade | `src/Plugins/*` | `app/Modules/*` + `Churches/<slug>` overrides |
| Tabela | `person_per` (sufixada, larga) | `persons` + `addresses` + `contacts` |
| Escopo de dados | Instância única | `tenant_id` em toda query |

---

## 7. Estado atual da migração

### Já migrado (backend + SPA)

| Módulo | Backend | Páginas SPA | Observação |
|---|---|---|---|
| Users | 4 actions, 1 controller | List/Show/Create/Edit | tem override por igreja |
| People | 4 actions, 1 controller | List/Show/Create/Edit | CRUD completo |
| Families | 4 actions, 1 controller | List/Show/Create/Edit | CRUD completo |
| Groups | 6 actions, 2 controllers | List/Show/Create/Edit | CRUD completo |
| Events | 5 actions, 2 controllers | List/Show/Create/Edit | inclui presença |
| Finance | 12 actions, 3 controllers | List/Show/Create/Edit | fundos, depósitos, promessas |
| Care | 8 actions, 2 controllers | Overview + 2 Create | parcial |
| Communications | 2 actions, 1 controller | Overview + WhatsApp | parcial |
| Calendar | 1 action, 1 controller | Overview | mínimo |
| Kiosk | 0 actions, 1 controller | Overview | mínimo |
| Core | — | — | infraestrutura |

### Ainda não migrado (do legado)

Fundraiser · Escola dominical · Voluntariado · eDrive/gerenciador de arquivos · Query builder · Mapa/geolocalização · Carrinho (seleção transversal) · GDPR/LGPD · Backup & restore · Custom fields (`person_custom`, `family_custom`) · Timer jobs · Sistema/settings · **os 30 relatórios PDF/CSV** · CalDAV/CardDAV (SabreDAV) · MailChimp · Jitsi · Repertório e Manuais (novos, ainda stubs).

### Lacunas técnicas do código novo (dívida a resolver antes de escalar)

1. **Sem API Resources** — controllers devolvem o model cru (`response()->json($person)`), expondo colunas internas e sem contrato estável.
2. **Sem Form Requests** — regras de validação duplicadas entre `store` e `update` no mesmo controller (`PersonController` repete 13 regras).
3. **Sem paginação no backend** — `ListPeopleAction` faz `->get()->all()` de tudo; a paginação é só visual no `PDataTable`. Vai quebrar com milhares de membros.
4. **Sem Policies** — autorização é `abort_unless` manual em cada método.
5. **Sem escopo global de tenant** — o filtro `tenant_id` é repetido à mão; um esquecimento vaza dados entre igrejas.
6. **Fallback silencioso na UI** — páginas capturam o erro e injetam dados falsos (`people.fallback_person`), o que mascara falha de autenticação.
7. **Token em `localStorage`** — vulnerável a XSS; considerar cookie httpOnly do Sanctum.
8. **Sem testes de front** — nenhum Vitest/Playwright; os `*SpaTest.php` só verificam se a rota entrega o shell.
9. **Fonte via CDN externo** — `@import` do Google Fonts bloqueia render e cria dependência externa.
10. **Rotas duplicadas** — `GET /navigation` existe em `web.php` e `api.php`.
11. **Ícones como caracteres Unicode** (`'⌂'`, `'◉'`, `'✚'`) no lugar de um icon set — não escala e não é acessível a leitores de tela.

---

## 8. Diretriz de UX para o público-alvo (crianças, jovens, idosos)

O objetivo declarado — telas extensas com excelente navegabilidade para todas as faixas etárias — colide com alguns padrões atuais do novo front. Recomendações concretas:

### 8.1 O que corrigir no design system atual

| Problema atual | Impacto | Correção |
|---|---|---|
| `--app-muted: #64748b` sobre superfície translúcida | Contraste abaixo de 4.5:1 em vários pontos | Fixar paleta com contraste AA garantido; `.app-nav__item` inativo hoje é `--app-muted` |
| Fundo `rgba(...)` + `backdrop-filter: blur` | Texto sobre fundo variável = contraste imprevisível | Superfícies opacas para conteúdo; blur só em decoração |
| `color-scheme: light` fixo | Sem modo escuro real (o legado tinha) | Suportar `prefers-color-scheme` + toggle persistido |
| Ícones Unicode em `<span>` | Invisíveis para leitor de tela, tamanho inconsistente | Icon set SVG com `aria-hidden` + label textual |
| `.app-nav__item` com `transform: translateX(2px)` no hover | Movimento sutil demais como feedback | Estado ativo com contraste + borda, não só cor |
| Fontes em `rem` fracionário (`0.72rem`, `0.82rem`) | ~11,5px em labels — pequeno para idosos | Mínimo 16px em corpo, 14px em labels |
| Densidade: `padding: 12px 14px` na nav | Alvo de toque abaixo de 44px | Alvos ≥44×44px (WCAG 2.5.5) |
| `hero-panel` + card + tabela em toda página | Muita hierarquia antes do conteúdo útil | Reduzir cabeçalho quando a tarefa é operacional |

### 8.2 Padrões a adotar para telas extensas

- **Sempre paginar no servidor** (`?page=&per_page=&sort=&filter[]=`) e refletir no `PDataTable` com `lazy`.
- **Filtros persistentes na URL** (query params) — permite voltar, compartilhar link e recarregar sem perder contexto.
- **Formulários longos em etapas** (`Steps`/`Stepper` do PrimeVue) em vez de rolagem infinita; salvar rascunho por etapa.
- **Ações primárias sempre visíveis** — barra de ação sticky no rodapé em formulários longos.
- **Colunas configuráveis com padrão enxuto** — 4–6 colunas visíveis, resto sob "mais colunas".
- **Busca global com atalho e foco por teclado**; navegação completa por `Tab` com `:focus-visible` de alto contraste.
- **Preferência de densidade e tamanho de fonte por usuário**, persistida no perfil (confortável/compacta), não apenas zoom do navegador.
- **Estados vazios e de erro explícitos** — remover os fallbacks fabricados; erro precisa dizer o que aconteceu e o que fazer.
- **Nada depender só de cor** — status com ícone + texto.
- **Modo quiosque separado** (check-in de crianças): fluxo de passo único, tipografia grande, alvos enormes, sem menu lateral — é o oposto do shell administrativo e merece um layout próprio (`KioskShell`).

---

## 9. Sequência sugerida para a continuação

1. **Consolidar a base do backend** antes de adicionar módulos: API Resources, Form Requests, paginação/filtro/ordenação padronizados, Policies e escopo global de tenant. Cada módulo novo herda o padrão pronto.
2. **Firmar o design system acessível** (contraste, tipografia, alvos de toque, foco, dark mode, icon set) — retrabalho aqui custa caro depois de 30+ telas.
3. **Completar os módulos parciais** (Care, Communications, Calendar, Kiosk) até paridade de CRUD.
4. **Migrar campos customizados** (`person_custom` / `family_custom`) — bloqueia a paridade de dados com o legado e afeta o importador.
5. **Trazer os domínios ausentes** em ordem de valor: Escola dominical → Voluntariado → Carrinho/seleção → Query/relatórios → eDrive → Fundraiser.
6. **Camada de relatórios** — decidir a estratégia (os 30 relatórios do legado usam 4 bibliotecas diferentes; consolidar em uma).
7. **Testes de front** (Vitest para componentes, Playwright para fluxos críticos) e testes de acessibilidade automatizados.
8. **Ampliar o `LegacyDataImporter`** para cobrir todos os domínios migrados, com relatório de divergências.

---

## 10. Referências rápidas de arquivo

**Legado**
- Entrypoints: `src/index.php`, `src/v2/index.php`, `src/api/index.php`, `src/session/index.php`
- Bootstrap/sessão: `src/EcclesiaCRM/Bootstrapper.php`, `src/EcclesiaCRM/SessionUser.php`
- Schema: `propel/main.schema.xml`, `src/mysql/install/Install.sql`
- Menu: `src/EcclesiaCRM/MenuBar/MenuBar.php`
- Permissões: `src/EcclesiaCRM/model/EcclesiaCRM/User.php`
- Estado front: `src/Include/Header-function.php` (`window.CRM`)
- Cliente HTTP front: `src/skin/js/CRMJSOM.js`
- Build: `Gruntfile.js`, `package.json`

**Novo**
- Módulos: `app/Modules/*/module.php`, `app/Support/Modules/{ModuleRegistry,ModuleLoader,ModuleDefinition}.php`
- Rotas: `routes/api.php`, `routes/web.php`, `app/Modules/*/Routes/api.php`
- Auth: `app/Http/Controllers/Api/AuthTokenController.php`, `config/sanctum.php`
- Navegação: `app/Http/Controllers/Api/NavigationController.php`, `resources/js/navigation/siteNavigation.js`
- Front: `resources/js/{app.js,router/index.js,api/http.js,i18n/index.js}`
- Estilo: `resources/css/app.css`, `resources/js/styles/theme.css`
- Ponte legado: `app/Support/Legacy/LegacyDataImporter.php`
- Planos anteriores: `docs/superpowers/plans/*.md`
