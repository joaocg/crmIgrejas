# Fundação da Migração — Contrato de API, Autorização e Acessibilidade — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Consolidar o contrato de backend (tenant, paginação, validação, serialização, autorização) e a base de UI acessível do `crmIgrejas`, de modo que cada domínio ainda por migrar do EcclesiaCRM herde um padrão pronto em vez de replicar a dívida atual.

**Architecture:** O trabalho é feito primeiro em `People` — que passa a ser o **módulo de referência** — e só depois replicado nos demais módulos já migrados. A Fase A fixa o contrato do backend; a Fase B faz a SPA consumir esse contrato; a Fase C corrige a base visual para o público multi-geracional. Nenhuma regra de negócio nova é inventada: quando houver comportamento a definir, a fonte da verdade é o código legado em `/Volumes/nvme/Projetos/Joao/ecclesiacrm/src`.

**Tech Stack:** Laravel 12, PHP 8.2+, Eloquent, Sanctum, PHPUnit 11, Vue 3, PrimeVue 4 (Aura), Pinia, vue-router 4, Vite 7, Vitest, Tailwind 4.

## Global Constraints

- **Migrar, não reinventar.** Toda regra de negócio deve ser rastreada até um arquivo do legado. Se não existe no legado, não entra neste plano.
- **Fonte da verdade do legado:** `/Volumes/nvme/Projetos/Joao/ecclesiacrm/src` (regras) e `/Volumes/nvme/Projetos/Joao/ecclesiacrm/propel/main.schema.xml` (dados).
- **Nomes de código sempre em inglês** (classes, métodos, colunas, rotas, chaves de i18n). Texto visível ao usuário sempre via `t()` / `__()`.
- `declare(strict_types=1);` no topo de todo arquivo PHP novo.
- Classes `final` para controllers, actions, resources, requests, policies e models. `final readonly` para value objects.
- Nenhum texto visível hardcoded em `.vue` ou em controller — sempre chave de tradução, e **as duas** locales (`pt-BR` e `en`) atualizadas no mesmo commit.
- Backend não conhece texto traduzido de dado mascarado: a API devolve `null` + flag; quem traduz é o front.
- Comando de teste do backend: `php artisan test`. Requer Redis rodando (`phpunit.xml` usa `CACHE_STORE=redis`, `SESSION_DRIVER=redis`, `QUEUE_CONNECTION=redis`); suba com `docker compose up -d redis` se necessário.
- Formatação: `vendor/bin/pint` antes de cada commit de PHP.
- Commits pequenos, um por tarefa concluída, mensagem em inglês no padrão do repo (`feat:`, `fix:`, `refactor:`, `test:`, `docs:`).

## Escopo

**Dentro:** dívida técnica transversal levantada em `docs/MIGRACAO-PADROES-LEGADO-E-NOVO.md` §7 e as correções de acessibilidade de §8.

**Fora — migração de domínio:** Escola dominical, Voluntariado, eDrive, Query builder, Fundraiser, Carrinho, GDPR, campos customizados e os 30 relatórios. Cada um recebe seu próprio plano **depois** deste — é justamente este plano que torna aqueles baratos.

**Fora — deliberadamente adiado, com motivo:**

| Item | Por que fica fora |
|---|---|
| Token de sessão em `localStorage` (risco de XSS) | Migrar para cookie httpOnly do Sanctum toca login, CSRF, `bootstrap/app.php` e as 37 suítes de teste de uma vez. É um plano próprio, curto, executado em um único passo — misturá-lo aqui arrisca deixar a autenticação instável no meio da refatoração de contrato. `$middleware->statefulApi()` já está registrado, então metade do caminho existe. |
| Formulários longos em etapas, barra de ação sticky, colunas configuráveis, busca global com atalho | São padrões para **telas novas**, não correções da base atual. Entram como requisito nos planos de domínio, apoiados nos tokens da Fase C. |
| Modo quiosque com layout próprio (`KioskShell`) | O módulo Kiosk ainda é um stub (0 actions). O layout vem junto com a migração funcional do check-in, no plano do Kiosk. |
| "Nada depender só de cor" nos status | Não há componente de status implementado ainda; vira requisito de revisão nos planos de domínio. |

## Estrutura de arquivos

| Arquivo | Responsabilidade |
|---|---|
| `app/Support/Tenancy/TenantContext.php` | Resolve o tenant ativo (usuário autenticado ou override explícito) |
| `app/Support/Tenancy/TenantScope.php` | Global scope Eloquent que injeta `where tenant_id = ?` |
| `app/Support/Tenancy/BelongsToTenant.php` | Trait: aplica o scope e preenche `tenant_id` no create |
| `app/Support/Http/Requests/IndexRequest.php` | FormRequest base: `page`, `per_page`, `sort`, `search` |
| `app/Support/Http/Resources/PaginatedCollection.php` | Envelope `{data, meta}` — **temporário**, criado na Task 2 e apagado na Task 3, quando os API Resources passam a gerar a mesma forma |
| `app/Support/Authorization/ModulePolicy.php` | Policy base: lê `role.permissions` e checa tenant |
| `app/Modules/People/Http/Requests/*.php` | Validação de entrada de People |
| `app/Modules/People/Http/Resources/PersonResource.php` | Serialização de saída + regra de privacidade do legado |
| `app/Policies/PersonPolicy.php` | Autorização de People |
| `resources/js/api/query.js` | Serializa/deserializa estado de listagem em query params |
| `resources/js/components/tables/BaseDataTable.vue` | Tabela lazy ligada ao contrato paginado |
| `resources/js/components/feedback/StateBlock.vue` | Estados de carregando / vazio / erro |
| `resources/js/components/icons/AppIcon.vue` | Icon set SVG acessível |
| `resources/js/stores/preferences.js` | Densidade, tamanho de fonte e tema |
| `resources/css/app.css` | Design tokens acessíveis (claro + escuro) |

---

# FASE A — Contrato de backend

### Task 1: Escopo global de tenant

Hoje o filtro `tenant_id` é repetido à mão em cada action e cada `Rule::exists`. Um esquecimento vaza dados entre igrejas. Esta task torna o isolamento automático e por padrão.

**Files:**
- Create: `app/Support/Tenancy/TenantContext.php`
- Create: `app/Support/Tenancy/TenantScope.php`
- Create: `app/Support/Tenancy/BelongsToTenant.php`
- Modify: `app/Models/Person.php`, `app/Models/Family.php`, `app/Models/Address.php`, `app/Models/Contact.php`, `app/Models/Group.php`, `app/Models/GroupMembership.php`, `app/Models/Event.php`, `app/Models/EventAttendance.php`, `app/Models/Note.php`, `app/Models/PastoralCareRecord.php`, `app/Models/DonationFund.php`, `app/Models/Deposit.php`, `app/Models/Pledge.php`
- Modify: `app/Support/Legacy/LegacyDataImporter.php`
- Modify: `database/seeders/LegacyDataSeeder.php`
- Test: `tests/Feature/Tenancy/GlobalTenantScopeTest.php`

**Interfaces:**
- Produces: `App\Support\Tenancy\TenantContext::id(): ?int`, `TenantContext::forTenant(?int $tenantId): void`, `TenantContext::forget(): void`, `TenantContext::runAs(?int $tenantId, callable $callback): mixed`
- Produces: trait `App\Support\Tenancy\BelongsToTenant` — aplicada em todo model de domínio
- Consumes: `App\Models\User::$tenant_id`

- [ ] **Step 1: Escrever o teste que falha**

Criar `tests/Feature/Tenancy/GlobalTenantScopeTest.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Tenancy;

use App\Models\Person;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GlobalTenantScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_queries_are_scoped_to_the_authenticated_tenant(): void
    {
        $tenant = $this->createTenant('default');
        $otherTenant = $this->createTenant('other');

        Person::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'first_name' => 'Joao',
            'last_name' => 'Coelho',
        ]);
        Person::withoutGlobalScopes()->create([
            'tenant_id' => $otherTenant->id,
            'first_name' => 'Other',
            'last_name' => 'Person',
        ]);

        $this->actingAs($this->createUser($tenant->id), 'sanctum');

        $this->assertSame(1, Person::query()->count());
        $this->assertSame('Joao', Person::query()->first()->first_name);
    }

    public function test_tenant_id_is_filled_automatically_on_create(): void
    {
        $tenant = $this->createTenant('default');
        $this->actingAs($this->createUser($tenant->id), 'sanctum');

        $person = Person::create([
            'first_name' => 'Maria',
            'last_name' => 'Silva',
        ]);

        $this->assertSame($tenant->id, $person->tenant_id);
    }

    public function test_explicit_override_wins_over_the_authenticated_user(): void
    {
        $tenant = $this->createTenant('default');
        $otherTenant = $this->createTenant('other');

        Person::withoutGlobalScopes()->create([
            'tenant_id' => $otherTenant->id,
            'first_name' => 'Other',
            'last_name' => 'Person',
        ]);

        $this->actingAs($this->createUser($tenant->id), 'sanctum');

        $count = app(TenantContext::class)->runAs(
            $otherTenant->id,
            fn (): int => Person::query()->count(),
        );

        $this->assertSame(1, $count);
        $this->assertSame(0, Person::query()->count());
    }

    private function createTenant(string $slug): Tenant
    {
        $tenant = new Tenant();
        $tenant->slug = $slug;
        $tenant->name = ucfirst($slug).' Church';
        $tenant->locale = 'pt_BR';
        $tenant->timezone = 'America/Fortaleza';
        $tenant->active = true;
        $tenant->save();

        return $tenant;
    }

    private function createUser(int $tenantId): User
    {
        return User::factory()->create([
            'tenant_id' => $tenantId,
            'email' => 'admin+'.uniqid().'@church.local',
            'password' => 'password',
            'active' => true,
        ]);
    }
}
```

- [ ] **Step 2: Rodar o teste e confirmar que falha**

Run: `php artisan test --filter=GlobalTenantScopeTest`
Expected: FAIL com `Class "App\Support\Tenancy\TenantContext" not found`.

- [ ] **Step 3: Implementar o `TenantContext`**

Criar `app/Support/Tenancy/TenantContext.php`:

```php
<?php

declare(strict_types=1);

namespace App\Support\Tenancy;

use Illuminate\Support\Facades\Auth;

final class TenantContext
{
    private ?int $tenantId = null;

    private bool $overridden = false;

    public function id(): ?int
    {
        if ($this->overridden) {
            return $this->tenantId;
        }

        $user = Auth::user();

        if ($user === null || $user->tenant_id === null) {
            return null;
        }

        return (int) $user->tenant_id;
    }

    public function forTenant(?int $tenantId): void
    {
        $this->tenantId = $tenantId;
        $this->overridden = true;
    }

    public function forget(): void
    {
        $this->tenantId = null;
        $this->overridden = false;
    }

    public function runAs(?int $tenantId, callable $callback): mixed
    {
        $previousTenantId = $this->tenantId;
        $previousOverridden = $this->overridden;

        $this->forTenant($tenantId);

        try {
            return $callback();
        } finally {
            $this->tenantId = $previousTenantId;
            $this->overridden = $previousOverridden;
        }
    }
}
```

- [ ] **Step 4: Implementar o scope e a trait**

Criar `app/Support/Tenancy/TenantScope.php`:

```php
<?php

declare(strict_types=1);

namespace App\Support\Tenancy;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

final class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $tenantId = app(TenantContext::class)->id();

        if ($tenantId === null) {
            return;
        }

        $builder->where($model->getTable().'.tenant_id', $tenantId);
    }
}
```

Criar `app/Support/Tenancy/BelongsToTenant.php`:

```php
<?php

declare(strict_types=1);

namespace App\Support\Tenancy;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope());

        static::creating(function (Model $model): void {
            if ($model->getAttribute('tenant_id') !== null) {
                return;
            }

            $tenantId = app(TenantContext::class)->id();

            if ($tenantId !== null) {
                $model->setAttribute('tenant_id', $tenantId);
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
```

Registrar o singleton em `app/Providers/AppServiceProvider.php`, dentro de `register()`:

```php
$this->app->singleton(\App\Support\Tenancy\TenantContext::class);
```

- [ ] **Step 5: Aplicar a trait nos models de domínio**

Em cada um dos 13 models listados em **Files**, adicionar o `use` e a trait, e **remover** o método `tenant()` duplicado onde existir (a trait já o fornece). Exemplo em `app/Models/Person.php`:

```php
use App\Support\Tenancy\BelongsToTenant;

final class Person extends Model
{
    use BelongsToTenant, HasFactory;

    // ... remover o método tenant(): BelongsTo daqui — vem da trait
```

Não aplicar em `User`, `Role`, `Tenant`, `ModuleDefinition`, `ModuleSetting`: `User` e `Role` são resolvidos antes de haver contexto de tenant, e os demais não são dados de igreja.

- [ ] **Step 6: Proteger importador e seeder**

Em `app/Support/Legacy/LegacyDataImporter::import()`, envolver a importação no contexto do tenant de destino, logo após `$tenantId = $this->ensureDefaultTenant();`:

```php
return app(\App\Support\Tenancy\TenantContext::class)->runAs($tenantId, function () use ($connectionName, $batchSize, $tenantId): array {
    // corpo atual do método, a partir de $adminRoleId = $this->ensureAdminRole($tenantId);
});
```

Em `database/seeders/LegacyDataSeeder.php`, aplicar o mesmo envelope `runAs()` em torno da criação de registros de domínio.

- [ ] **Step 7: Rodar todos os testes**

Run: `php artisan test`
Expected: `GlobalTenantScopeTest` PASS e nenhuma regressão. Se `LegacyImportTest` ou `TenantScopingTest` falharem, a causa é sempre uma das duas: (a) criação de registro sem usuário autenticado — resolver com `runAs()`; (b) consulta administrativa que precisa cruzar tenants — resolver com `Model::withoutGlobalScope(TenantScope::class)`. Nunca resolver removendo a trait.

- [ ] **Step 8: Commit**

```bash
vendor/bin/pint app/Support/Tenancy app/Models app/Providers
git add app/Support/Tenancy app/Models app/Providers/AppServiceProvider.php app/Support/Legacy database/seeders tests/Feature/Tenancy
git commit -m "feat: enforce tenant isolation with a global eloquent scope"
```

---

### Task 2: Contrato de listagem paginada

`ListPeopleAction` hoje faz `->get()->all()` e devolve tudo. O legado tinha o mesmo problema (`src/v2/templates/people/personlist.php` renderiza **todas** as linhas no `<tbody>` e o DataTables pagina no cliente) — ou seja, paginar no servidor **não é reinventar regra de negócio**, é corrigir infraestrutura que o legado nunca teve. As colunas de busca e ordenação, essas sim, seguem o legado.

**Files:**
- Create: `app/Support/Http/Requests/IndexRequest.php`
- Create: `app/Modules/People/Http/Requests/ListPeopleRequest.php`
- Modify: `app/Modules/People/Actions/ListPeopleAction.php`
- Modify: `app/Modules/People/Http/Controllers/PersonController.php:16-21`
- Test: `tests/Feature/PeoplePaginationTest.php`

**Interfaces:**
- Produces: `App\Support\Http\Requests\IndexRequest` com `perPage(): int`, `searchTerm(): ?string`, `sortColumn(): string`, `sortDirection(): string`, e o contrato abstrato `sortableColumns(): array<int,string>` / `defaultSort(): string`
- Produces: `ListPeopleAction::execute(ListPeopleRequest $request): LengthAwarePaginator`
- Consumes: escopo global de tenant da Task 1 (a action **não** recebe mais `int $tenantId`)

- [ ] **Step 1: Escrever o teste que falha**

Criar `tests/Feature/PeoplePaginationTest.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Person;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeoplePaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_a_paginated_envelope(): void
    {
        $user = $this->authenticate();

        for ($index = 1; $index <= 30; $index++) {
            Person::create([
                'first_name' => 'Person'.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
                'last_name' => 'Sobrenome',
            ]);
        }

        $this->getJson('/api/people?per_page=10')
            ->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.total', 30)
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonPath('meta.current_page', 1);

        $this->assertSame($user->tenant_id, Person::query()->first()->tenant_id);
    }

    public function test_index_filters_by_search_term(): void
    {
        $this->authenticate();

        Person::create(['first_name' => 'Joao', 'last_name' => 'Coelho']);
        Person::create(['first_name' => 'Maria', 'last_name' => 'Silva']);

        $this->getJson('/api/people?search=coelho')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.first_name', 'Joao');
    }

    public function test_index_sorts_by_an_allowed_column_in_both_directions(): void
    {
        $this->authenticate();

        Person::create(['first_name' => 'Ana', 'last_name' => 'Zebra']);
        Person::create(['first_name' => 'Bruno', 'last_name' => 'Alves']);

        $this->getJson('/api/people?sort=last_name')
            ->assertOk()
            ->assertJsonPath('data.0.last_name', 'Alves');

        $this->getJson('/api/people?sort=-last_name')
            ->assertOk()
            ->assertJsonPath('data.0.last_name', 'Zebra');
    }

    public function test_index_rejects_an_unknown_sort_column(): void
    {
        $this->authenticate();

        $this->getJson('/api/people?sort=password')
            ->assertStatus(422)
            ->assertJsonValidationErrors('sort');
    }

    public function test_index_caps_the_page_size(): void
    {
        $this->authenticate();

        $this->getJson('/api/people?per_page=5000')
            ->assertStatus(422)
            ->assertJsonValidationErrors('per_page');
    }

    private function authenticate(): User
    {
        $tenant = new Tenant();
        $tenant->slug = 'default';
        $tenant->name = 'Default Church';
        $tenant->locale = 'pt_BR';
        $tenant->timezone = 'America/Fortaleza';
        $tenant->active = true;
        $tenant->save();

        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'email' => 'admin+'.uniqid().'@church.local',
            'password' => 'password',
            'active' => true,
        ]);

        $this->actingAs($user, 'sanctum');

        return $user;
    }
}
```

- [ ] **Step 2: Rodar o teste e confirmar que falha**

Run: `php artisan test --filter=PeoplePaginationTest`
Expected: FAIL — a resposta atual é um array plano, sem `data`/`meta`.

- [ ] **Step 3: Implementar o `IndexRequest` base**

Criar `app/Support/Http/Requests/IndexRequest.php`:

```php
<?php

declare(strict_types=1);

namespace App\Support\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class IndexRequest extends FormRequest
{
    public const MAX_PER_PAGE = 100;

    public const DEFAULT_PER_PAGE = 25;

    /**
     * @return array<int, string>
     */
    abstract protected function sortableColumns(): array;

    abstract protected function defaultSort(): string;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:'.self::MAX_PER_PAGE],
            'sort' => ['sometimes', 'string', Rule::in($this->allowedSortValues())],
            'search' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }

    public function perPage(): int
    {
        return (int) $this->integer('per_page', self::DEFAULT_PER_PAGE);
    }

    public function searchTerm(): ?string
    {
        $term = trim((string) $this->query('search', ''));

        return $term === '' ? null : $term;
    }

    public function sortColumn(): string
    {
        return ltrim($this->sortValue(), '-');
    }

    public function sortDirection(): string
    {
        return str_starts_with($this->sortValue(), '-') ? 'desc' : 'asc';
    }

    private function sortValue(): string
    {
        $sort = (string) $this->query('sort', '');

        return $sort === '' ? $this->defaultSort() : $sort;
    }

    /**
     * @return array<int, string>
     */
    private function allowedSortValues(): array
    {
        $values = [];

        foreach ($this->sortableColumns() as $column) {
            $values[] = $column;
            $values[] = '-'.$column;
        }

        return $values;
    }
}
```

- [ ] **Step 4: Implementar o request e a action de People**

Criar `app/Modules/People/Http/Requests/ListPeopleRequest.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\People\Http\Requests;

use App\Support\Http\Requests\IndexRequest;

final class ListPeopleRequest extends IndexRequest
{
    /**
     * @return array<int, string>
     */
    protected function sortableColumns(): array
    {
        return ['first_name', 'last_name', 'birth_date', 'membership_date', 'created_at'];
    }

    protected function defaultSort(): string
    {
        return 'last_name';
    }
}
```

Substituir todo o conteúdo de `app/Modules/People/Actions/ListPeopleAction.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\People\Actions;

use App\Models\Person;
use App\Modules\People\Http\Requests\ListPeopleRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class ListPeopleAction
{
    public function execute(ListPeopleRequest $request): LengthAwarePaginator
    {
        return Person::query()
            ->with(['family', 'address'])
            ->when(
                $request->searchTerm(),
                fn (Builder $query, string $term): Builder => $query->where(
                    fn (Builder $inner): Builder => $inner
                        ->where('first_name', 'like', '%'.$term.'%')
                        ->orWhere('last_name', 'like', '%'.$term.'%')
                ),
            )
            ->orderBy($request->sortColumn(), $request->sortDirection())
            ->orderBy('id')
            ->paginate($request->perPage())
            ->withQueryString();
    }
}
```

- [ ] **Step 5: Implementar o envelope e ligar no controller**

**Atenção:** `response()->json($paginator)` produz as chaves do paginador no nível raiz (`current_page`, `data`, `total`, …) — **não** produz `meta`. O envelope `{data, meta}` precisa ser explícito. A Task 3 substitui isto por `PersonResource::collection($paginator)`, que gera exatamente a mesma forma; o contrato não muda entre as duas tasks.

Criar `app/Support/Http/Resources/PaginatedCollection.php`:

```php
<?php

declare(strict_types=1);

namespace App\Support\Http\Resources;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class PaginatedCollection
{
    /**
     * @return array{data: array<int, mixed>, meta: array<string, int>}
     */
    public static function envelope(LengthAwarePaginator $paginator): array
    {
        return [
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ];
    }
}
```

Em `app/Modules/People/Http/Controllers/PersonController.php`, substituir o método `index` e adicionar os imports `use App\Modules\People\Http\Requests\ListPeopleRequest;` e `use App\Support\Http\Resources\PaginatedCollection;`:

```php
    public function index(ListPeopleRequest $request, ListPeopleAction $action): JsonResponse
    {
        return response()->json(PaginatedCollection::envelope($action->execute($request)));
    }
```

- [ ] **Step 6: Rodar os testes**

Run: `php artisan test --filter=PeoplePaginationTest`
Expected: PASS nos 5 testes.

Run: `php artisan test --filter=PeopleModuleTest`
Expected: FAIL em `assertJsonCount(1)` e `assertJsonPath('0.id', ...)` — a resposta agora é envelopada. Corrigir o teste existente para `assertJsonCount(1, 'data')` e `assertJsonPath('data.0.id', $person->id)`. **Corrigir o teste, não o contrato.**

- [ ] **Step 7: Rodar a suíte inteira e commitar**

Run: `php artisan test`
Expected: tudo PASS.

```bash
vendor/bin/pint app/Support/Http app/Modules/People
git add app/Support/Http app/Modules/People tests/Feature/PeoplePaginationTest.php tests/Feature/PeopleModuleTest.php
git commit -m "feat: add a paginated list contract to the people module"
```

---

### Task 3: Form Requests, API Resource e a regra de privacidade do legado

Duas dívidas e uma regra perdida. Dívidas: `PersonController` repete 13 regras de validação entre `store` e `update`, e devolve o model cru (expõe colunas internas, contrato instável).

Regra perdida — **isto é migração, não invenção**: em `src/v2/templates/people/personlist.php:82-100`, quando `SessionUser::getUser()->isSeePrivacyDataEnabled()` é falso, o legado substitui endereço, telefones e e-mail pelo texto `_('Private Data')`. O `crmIgrejas` hoje devolve esses dados a qualquer usuário autenticado. A permissão equivalente no novo modelo RBAC é `people.private_data.view`.

**Files:**
- Create: `app/Modules/People/Http/Requests/StorePersonRequest.php`
- Create: `app/Modules/People/Http/Requests/UpdatePersonRequest.php`
- Create: `app/Modules/People/Http/Resources/PersonResource.php`
- Modify: `app/Modules/People/Http/Controllers/PersonController.php` (arquivo inteiro)
- Modify: `resources/js/i18n/locales/pt-BR.json`, `resources/js/i18n/locales/en.json`
- Test: `tests/Feature/PeoplePrivacyTest.php`

**Interfaces:**
- Produces: `PersonResource` — payload `{id, first_name, middle_name, last_name, title, suffix, birth_date, membership_date, gender, envelope_number, newsletter_enabled, deactivated_at, family, address, contacts, private_data_hidden}`
- Produces: `StorePersonRequest::rules()`, `UpdatePersonRequest::rules()`
- Consumes: `Role::allows(string $ability): bool` (já existe em `app/Models/Role.php:41`)

- [ ] **Step 1: Escrever o teste que falha**

Criar `tests/Feature/PeoplePrivacyTest.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Person;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeoplePrivacyTest extends TestCase
{
    use RefreshDatabase;

    public function test_private_data_is_masked_without_the_permission(): void
    {
        $person = $this->personForUserWithPermissions(['navigation.people' => true]);

        $this->getJson("/api/people/{$person->id}")
            ->assertOk()
            ->assertJsonPath('data.private_data_hidden', true)
            ->assertJsonPath('data.address', null)
            ->assertJsonPath('data.contacts', []);
    }

    public function test_private_data_is_visible_with_the_permission(): void
    {
        $person = $this->personForUserWithPermissions([
            'navigation.people' => true,
            'people.private_data.view' => true,
        ]);

        $this->getJson("/api/people/{$person->id}")
            ->assertOk()
            ->assertJsonPath('data.private_data_hidden', false)
            ->assertJsonPath('data.contacts.0.value', 'joao@example.com');
    }

    public function test_store_rejects_an_invalid_payload(): void
    {
        $this->personForUserWithPermissions(['navigation.people' => true]);

        $this->postJson('/api/people', ['last_name' => 'Silva'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('first_name');
    }

    /**
     * @param  array<string, bool>  $permissions
     */
    private function personForUserWithPermissions(array $permissions): Person
    {
        $tenant = new Tenant();
        $tenant->slug = 'default';
        $tenant->name = 'Default Church';
        $tenant->locale = 'pt_BR';
        $tenant->timezone = 'America/Fortaleza';
        $tenant->active = true;
        $tenant->save();

        $role = Role::create([
            'tenant_id' => $tenant->id,
            'slug' => 'operator',
            'name' => 'Operator',
            'permissions' => $permissions,
            'is_system' => false,
            'active' => true,
        ]);

        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role_id' => $role->id,
            'email' => 'operator+'.uniqid().'@church.local',
            'password' => 'password',
            'active' => true,
        ]);

        $this->actingAs($user, 'sanctum');

        $address = Address::create([
            'tenant_id' => $tenant->id,
            'line1' => 'Rua Um, 100',
            'city' => 'Fortaleza',
        ]);

        $person = Person::create([
            'address_id' => $address->id,
            'first_name' => 'Joao',
            'last_name' => 'Coelho',
        ]);

        $person->contacts()->create([
            'tenant_id' => $tenant->id,
            'type' => 'email',
            'label' => 'Email',
            'value' => 'joao@example.com',
            'is_primary' => true,
        ]);

        return $person;
    }
}
```

- [ ] **Step 2: Rodar o teste e confirmar que falha**

Run: `php artisan test --filter=PeoplePrivacyTest`
Expected: FAIL — não existe `data.private_data_hidden` e a resposta de `show` não é envelopada.

- [ ] **Step 3: Implementar os Form Requests**

Criar `app/Modules/People/Http/Requests/StorePersonRequest.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\People\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StorePersonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'family_id' => ['nullable', 'integer', Rule::exists('families', 'id')],
            'address_id' => ['nullable', 'integer', Rule::exists('addresses', 'id')],
            'title' => ['nullable', 'string', 'max:255'],
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'suffix' => ['nullable', 'string', 'max:255'],
            'birth_date' => ['nullable', 'date'],
            'membership_date' => ['nullable', 'date'],
            'gender' => ['nullable', 'integer'],
            'envelope_number' => ['nullable', 'integer'],
            'newsletter_enabled' => ['nullable', 'boolean'],
            'deactivated_at' => ['nullable', 'date'],
        ];
    }
}
```

> O `->where(tenant_id)` de cada `Rule::exists` **sai** — o global scope da Task 1 não alcança o validador, então a checagem cruzada de tenant passa a ser feita pela Policy da Task 4, que valida o registro alvo. Enquanto a Task 4 não estiver pronta, `Rule::exists` sozinho já barra IDs inexistentes.

Criar `app/Modules/People/Http/Requests/UpdatePersonRequest.php` com as mesmas regras, mas cada uma prefixada por `'sometimes'` e sem `'required'`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\People\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdatePersonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'family_id' => ['sometimes', 'nullable', 'integer', Rule::exists('families', 'id')],
            'address_id' => ['sometimes', 'nullable', 'integer', Rule::exists('addresses', 'id')],
            'title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'first_name' => ['sometimes', 'string', 'max:255'],
            'middle_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'last_name' => ['sometimes', 'string', 'max:255'],
            'suffix' => ['sometimes', 'nullable', 'string', 'max:255'],
            'birth_date' => ['sometimes', 'nullable', 'date'],
            'membership_date' => ['sometimes', 'nullable', 'date'],
            'gender' => ['sometimes', 'nullable', 'integer'],
            'envelope_number' => ['sometimes', 'nullable', 'integer'],
            'newsletter_enabled' => ['sometimes', 'boolean'],
            'deactivated_at' => ['sometimes', 'nullable', 'date'],
        ];
    }
}
```

- [ ] **Step 4: Implementar o `PersonResource` com a regra do legado**

Criar `app/Modules/People/Http/Resources/PersonResource.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\People\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class PersonResource extends JsonResource
{
    /**
     * Legacy rule: src/v2/templates/people/personlist.php replaces address,
     * phones and email with "Private Data" when the user is not allowed to
     * see private data (User::isSeePrivacyDataEnabled).
     */
    public const PRIVATE_DATA_ABILITY = 'people.private_data.view';

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $showsPrivateData = $request->user()?->role?->allows(self::PRIVATE_DATA_ABILITY) ?? false;

        return [
            'id' => $this->id,
            'family_id' => $this->family_id,
            'title' => $this->title,
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'suffix' => $this->suffix,
            'birth_date' => $this->birth_date?->toDateString(),
            'membership_date' => $this->membership_date?->toDateString(),
            'gender' => $this->gender,
            'envelope_number' => $this->envelope_number,
            'newsletter_enabled' => (bool) $this->newsletter_enabled,
            'deactivated_at' => $this->deactivated_at?->toIso8601String(),
            'private_data_hidden' => ! $showsPrivateData,
            'family' => $this->whenLoaded('family', fn (): array => [
                'id' => $this->family->id,
                'name' => $this->family->name,
            ]),
            'address' => $showsPrivateData
                ? $this->whenLoaded('address', fn (): ?array => $this->address === null ? null : [
                    'id' => $this->address->id,
                    'line1' => $this->address->line1,
                    'line2' => $this->address->line2,
                    'city' => $this->address->city,
                    'state' => $this->address->state,
                    'postal_code' => $this->address->postal_code,
                ])
                : null,
            'contacts' => $showsPrivateData
                ? $this->whenLoaded('contacts', fn (): array => $this->contacts
                    ->map(fn ($contact): array => [
                        'id' => $contact->id,
                        'type' => $contact->type,
                        'label' => $contact->label,
                        'value' => $contact->value,
                        'is_primary' => (bool) $contact->is_primary,
                    ])
                    ->all())
                : [],
        ];
    }
}
```

- [ ] **Step 5: Reescrever o `PersonController`**

Substituir todo o conteúdo de `app/Modules/People/Http/Controllers/PersonController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\People\Http\Controllers;

use App\Models\Person;
use App\Modules\People\Actions\CreatePersonAction;
use App\Modules\People\Actions\DeletePersonAction;
use App\Modules\People\Actions\ListPeopleAction;
use App\Modules\People\Actions\UpdatePersonAction;
use App\Modules\People\Http\Requests\ListPeopleRequest;
use App\Modules\People\Http\Requests\StorePersonRequest;
use App\Modules\People\Http\Requests\UpdatePersonRequest;
use App\Modules\People\Http\Resources\PersonResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class PersonController
{
    public function index(ListPeopleRequest $request, ListPeopleAction $action): AnonymousResourceCollection
    {
        return PersonResource::collection($action->execute($request));
    }

    public function store(StorePersonRequest $request, CreatePersonAction $action): PersonResource
    {
        $person = $action->execute($request->validated());

        return PersonResource::make($person->load(['family', 'address', 'contacts']));
    }

    public function show(Person $person): PersonResource
    {
        return PersonResource::make($person->load(['family', 'address', 'contacts']));
    }

    public function update(UpdatePersonRequest $request, Person $person, UpdatePersonAction $action): PersonResource
    {
        $person = $action->execute($person, $request->validated());

        return PersonResource::make($person->load(['family', 'address', 'contacts']));
    }

    public function destroy(Person $person, DeletePersonAction $action): JsonResponse
    {
        $action->execute($person);

        return response()->json([], 204);
    }
}
```

Atualizar `app/Modules/People/Actions/CreatePersonAction.php` (o `tenant_id` agora vem da trait):

```php
<?php

declare(strict_types=1);

namespace App\Modules\People\Actions;

use App\Models\Person;

final class CreatePersonAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data): Person
    {
        return Person::create($data);
    }
}
```

> O `abort_unless` de tenant sai daqui — o global scope faz o route-model binding de `Person` já não encontrar registros de outro tenant, o que devolve 404 exatamente como antes. A Task 4 acrescenta a checagem de permissão.

`PersonResource::collection($paginator)` devolve `{data, links, meta}` com `meta.current_page`, `meta.per_page`, `meta.total` e `meta.last_page` — mesma forma que o envelope manual da Task 2, então `PeoplePaginationTest` continua valendo sem alteração. Com isso, `App\Support\Http\Resources\PaginatedCollection` fica sem uso: **apagar o arquivo** e remover o import do controller.

```bash
rm app/Support/Http/Resources/PaginatedCollection.php
```

- [ ] **Step 6: Adicionar as chaves de tradução**

Em `resources/js/i18n/locales/pt-BR.json`, dentro do objeto `"people"`:

```json
    "private_data": "Dado privado",
    "private_data_notice": "Você não tem permissão para ver endereço, telefone e e-mail."
```

Em `resources/js/i18n/locales/en.json`, no mesmo lugar:

```json
    "private_data": "Private data",
    "private_data_notice": "You are not allowed to see address, phone and email."
```

- [ ] **Step 7: Rodar os testes**

Run: `php artisan test --filter=PeoplePrivacyTest`
Expected: PASS nos 3 testes.

Run: `php artisan test`
Expected: `PeopleModuleTest` falha em `assertJsonPath('tenant_id', ...)` e `assertJsonPath('contacts.0.value', ...)` — o payload agora é `data.*` e `tenant_id` não é mais exposto. Ajustar o teste: trocar por `assertJsonPath('data.first_name', 'Maria')` e criar o usuário com `permissions => ['*' => true]` para enxergar contatos. **Ajustar o teste, não o Resource** — não expor `tenant_id` é intencional.

- [ ] **Step 8: Commit**

```bash
vendor/bin/pint app/Modules/People
git add app/Modules/People tests/Feature resources/js/i18n
git commit -m "feat: add form requests and resources to people with legacy privacy rule"
```

---

### Task 4: Policies e autorização declarativa

Hoje a autorização é `abort_unless` copiado em cada método, e a permissão de menu (`navigation.people`) só é checada na navegação — a API aceita qualquer usuário autenticado. No legado, cada tela consultava um método booleano de `User` (`isAddRecordsEnabled`, `isEditRecordsEnabled`, `isDeleteRecordsEnabled` em `src/EcclesiaCRM/model/EcclesiaCRM/User.php:557-572`). Esta task porta essas três flags para abilities.

**Files:**
- Create: `app/Support/Authorization/ModulePolicy.php`
- Create: `app/Policies/PersonPolicy.php`
- Modify: `app/Providers/AppServiceProvider.php`
- Modify: `app/Modules/People/Http/Controllers/PersonController.php`
- Test: `tests/Feature/PeopleAuthorizationTest.php`

**Interfaces:**
- Produces: `App\Support\Authorization\ModulePolicy` (abstrata) com `viewAny`, `view`, `create`, `update`, `delete` e o contrato `abilityPrefix(): string`
- Mapa de abilities (portado das flags do legado):

| Legado | Nova ability |
|---|---|
| menu visível | `navigation.people` |
| `isAddRecordsEnabled()` | `people.create` |
| `isEditRecordsEnabled()` | `people.update` |
| `isDeleteRecordsEnabled()` | `people.delete` |
| `isSeePrivacyDataEnabled()` | `people.private_data.view` (Task 3) |

- [ ] **Step 1: Escrever o teste que falha**

Criar `tests/Feature/PeopleAuthorizationTest.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Person;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeopleAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_listing_requires_the_navigation_ability(): void
    {
        $this->authenticateWith(['navigation.finance' => true]);

        $this->getJson('/api/people')->assertForbidden();
    }

    public function test_creating_requires_the_create_ability(): void
    {
        $this->authenticateWith(['navigation.people' => true]);

        $this->postJson('/api/people', [
            'first_name' => 'Maria',
            'last_name' => 'Silva',
        ])->assertForbidden();
    }

    public function test_creating_is_allowed_with_the_create_ability(): void
    {
        $this->authenticateWith([
            'navigation.people' => true,
            'people.create' => true,
        ]);

        $this->postJson('/api/people', [
            'first_name' => 'Maria',
            'last_name' => 'Silva',
        ])->assertCreated();
    }

    public function test_deleting_requires_the_delete_ability(): void
    {
        $this->authenticateWith(['navigation.people' => true]);

        $person = Person::create(['first_name' => 'Joao', 'last_name' => 'Coelho']);

        $this->deleteJson("/api/people/{$person->id}")->assertForbidden();
    }

    public function test_the_wildcard_permission_allows_everything(): void
    {
        $this->authenticateWith(['*' => true]);

        $this->getJson('/api/people')->assertOk();
    }

    /**
     * @param  array<string, bool>  $permissions
     */
    private function authenticateWith(array $permissions): User
    {
        $tenant = new Tenant();
        $tenant->slug = 'default';
        $tenant->name = 'Default Church';
        $tenant->locale = 'pt_BR';
        $tenant->timezone = 'America/Fortaleza';
        $tenant->active = true;
        $tenant->save();

        $role = Role::create([
            'tenant_id' => $tenant->id,
            'slug' => 'operator',
            'name' => 'Operator',
            'permissions' => $permissions,
            'is_system' => false,
            'active' => true,
        ]);

        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role_id' => $role->id,
            'email' => 'operator+'.uniqid().'@church.local',
            'password' => 'password',
            'active' => true,
        ]);

        $this->actingAs($user, 'sanctum');

        return $user;
    }
}
```

- [ ] **Step 2: Rodar o teste e confirmar que falha**

Run: `php artisan test --filter=PeopleAuthorizationTest`
Expected: FAIL — hoje tudo devolve 200/201, nenhuma checagem de ability existe.

- [ ] **Step 3: Implementar a policy base**

Criar `app/Support/Authorization/ModulePolicy.php`:

```php
<?php

declare(strict_types=1);

namespace App\Support\Authorization;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

abstract class ModulePolicy
{
    abstract protected function abilityPrefix(): string;

    abstract protected function navigationAbility(): string;

    public function viewAny(User $user): bool
    {
        return $this->allows($user, $this->navigationAbility());
    }

    public function view(User $user, Model $model): bool
    {
        return $this->allows($user, $this->navigationAbility())
            && $this->sameTenant($user, $model);
    }

    public function create(User $user): bool
    {
        return $this->allows($user, $this->abilityPrefix().'.create');
    }

    public function update(User $user, Model $model): bool
    {
        return $this->allows($user, $this->abilityPrefix().'.update')
            && $this->sameTenant($user, $model);
    }

    public function delete(User $user, Model $model): bool
    {
        return $this->allows($user, $this->abilityPrefix().'.delete')
            && $this->sameTenant($user, $model);
    }

    protected function allows(User $user, string $ability): bool
    {
        return $user->role?->allows($ability) ?? false;
    }

    protected function sameTenant(User $user, Model $model): bool
    {
        return (int) $model->getAttribute('tenant_id') === (int) $user->tenant_id;
    }
}
```

Criar `app/Policies/PersonPolicy.php`:

```php
<?php

declare(strict_types=1);

namespace App\Policies;

use App\Support\Authorization\ModulePolicy;

final class PersonPolicy extends ModulePolicy
{
    protected function abilityPrefix(): string
    {
        return 'people';
    }

    protected function navigationAbility(): string
    {
        return 'navigation.people';
    }
}
```

- [ ] **Step 4: Registrar a policy**

Em `app/Providers/AppServiceProvider.php`, no método `boot()`:

```php
\Illuminate\Support\Facades\Gate::policy(\App\Models\Person::class, \App\Policies\PersonPolicy::class);
```

- [ ] **Step 5: Aplicar no controller**

Em `app/Modules/People/Http/Controllers/PersonController.php`, adicionar o construtor que registra o `authorizeResource` — como a classe não estende o `Controller` base, a autorização é explícita em cada método. Adicionar `use Illuminate\Support\Facades\Gate;` e, no início de cada método:

```php
    public function index(ListPeopleRequest $request, ListPeopleAction $action): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Person::class);

        return PersonResource::collection($action->execute($request));
    }

    public function store(StorePersonRequest $request, CreatePersonAction $action): PersonResource
    {
        Gate::authorize('create', Person::class);

        $person = $action->execute($request->validated());

        return PersonResource::make($person->load(['family', 'address', 'contacts']));
    }

    public function show(Person $person): PersonResource
    {
        Gate::authorize('view', $person);

        return PersonResource::make($person->load(['family', 'address', 'contacts']));
    }

    public function update(UpdatePersonRequest $request, Person $person, UpdatePersonAction $action): PersonResource
    {
        Gate::authorize('update', $person);

        $person = $action->execute($person, $request->validated());

        return PersonResource::make($person->load(['family', 'address', 'contacts']));
    }

    public function destroy(Person $person, DeletePersonAction $action): JsonResponse
    {
        Gate::authorize('delete', $person);

        $action->execute($person);

        return response()->json([], 204);
    }
```

- [ ] **Step 6: Rodar os testes e ajustar as fixtures**

Run: `php artisan test --filter=PeopleAuthorizationTest`
Expected: PASS nos 5 testes.

Run: `php artisan test`
Expected: `PeopleModuleTest`, `PeoplePaginationTest`, `PeoplePrivacyTest` e `TenantScopingTest` falham com 403 — os usuários dessas fixtures não têm role. Ajustar cada helper de criação de usuário para anexar uma role com `['*' => true]`, exceto onde o teste testa restrição de propósito.

- [ ] **Step 7: Commit**

```bash
vendor/bin/pint app/Support/Authorization app/Policies app/Modules/People app/Providers
git add app/Support/Authorization app/Policies app/Providers/AppServiceProvider.php app/Modules/People tests/Feature
git commit -m "feat: authorize people endpoints with policies ported from legacy flags"
```

---

### Task 5: Propagar o padrão para os demais módulos

Com People como referência, replicar em `Families`, `Groups`, `Events`, `Finance` e `Users`. Sem novidade de arquitetura — é aplicação mecânica das Tasks 2, 3 e 4.

**Files (por módulo `<M>` em Families, Groups, Events, Finance, Users):**
- Create: `app/Modules/<M>/Http/Requests/List<M>Request.php`, `Store<X>Request.php`, `Update<X>Request.php`
- Create: `app/Modules/<M>/Http/Resources/<X>Resource.php`
- Create: `app/Policies/<X>Policy.php`
- Modify: `app/Modules/<M>/Actions/List*.php` (assinatura passa a receber o Request e devolver `LengthAwarePaginator`)
- Modify: `app/Modules/<M>/Http/Controllers/*.php`
- Modify: `app/Providers/AppServiceProvider.php` (registrar cada policy)
- Modify: `tests/Feature/<M>ModuleTest.php`

**Interfaces:**
- Consumes: `IndexRequest`, `ModulePolicy`, trait `BelongsToTenant`
- Produces: mesmo envelope `{data, meta}` e mesmo mapa de abilities `navigation.<m>` / `<m>.create` / `<m>.update` / `<m>.delete` em todos os módulos

Abilities e colunas ordenáveis/pesquisáveis por módulo — **derivadas do legado**, não inventadas:

| Módulo | Prefixo | Ordenáveis | Busca em | Referência legada |
|---|---|---|---|---|
| Families | `families` | `name`, `wedding_date`, `created_at` | `name` | `src/v2/templates/people/familylist.php` |
| Groups | `groups` | `name`, `created_at` | `name` | `src/v2/templates/group/grouplist.php` |
| Events | `events` | `start_at`, `title`, `created_at` | `title` | `src/v2/templates/calendar/eventslist.php` |
| Finance | `finance` | `date`, `amount`, `created_at` | `comment` | `src/v2/templates/deposit/depositlist.php` |
| Users | `users` | `name`, `email`, `created_at` | `name`, `email` | `src/v2/templates/user/userlist.php` |

**Families primeiro, com código completo.** Os outros quatro seguem os mesmos cinco arquivos, com os nomes e as colunas da tabela acima.

- [ ] **Step 1: Ler o template legado de Families**

Abrir `/Volumes/nvme/Projetos/Joao/ecclesiacrm/src/v2/templates/people/familylist.php` e confirmar dois pontos: quais colunas o legado exibia, e se o bloco `isSeePrivacyDataEnabled()` também mascara dados aqui. A tabela `families` do novo tem `email`, `home_phone`, `work_phone` e `mobile_phone` como colunas diretas — **se o legado mascara, o `FamilyResource` também deve mascarar.** Anotar o achado em comentário na classe, como em `PersonResource::PRIVATE_DATA_ABILITY`.

- [ ] **Step 2: Escrever o teste que falha**

Criar `tests/Feature/FamiliesPaginationTest.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Family;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FamiliesPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_a_paginated_envelope(): void
    {
        $this->authenticate();

        for ($index = 1; $index <= 30; $index++) {
            Family::create(['name' => 'Family '.str_pad((string) $index, 2, '0', STR_PAD_LEFT)]);
        }

        $this->getJson('/api/families?per_page=10')
            ->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.total', 30)
            ->assertJsonPath('meta.per_page', 10);
    }

    public function test_index_filters_by_search_term(): void
    {
        $this->authenticate();

        Family::create(['name' => 'Coelho']);
        Family::create(['name' => 'Silva']);

        $this->getJson('/api/families?search=coel')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Coelho');
    }

    public function test_index_rejects_an_unknown_sort_column(): void
    {
        $this->authenticate();

        $this->getJson('/api/families?sort=tenant_id')
            ->assertStatus(422)
            ->assertJsonValidationErrors('sort');
    }

    public function test_creating_requires_the_create_ability(): void
    {
        $this->authenticate(['navigation.families' => true]);

        $this->postJson('/api/families', ['name' => 'Nova'])->assertForbidden();
    }

    /**
     * @param  array<string, bool>  $permissions
     */
    private function authenticate(array $permissions = ['*' => true]): User
    {
        $tenant = new Tenant();
        $tenant->slug = 'default';
        $tenant->name = 'Default Church';
        $tenant->locale = 'pt_BR';
        $tenant->timezone = 'America/Fortaleza';
        $tenant->active = true;
        $tenant->save();

        $role = Role::create([
            'tenant_id' => $tenant->id,
            'slug' => 'operator',
            'name' => 'Operator',
            'permissions' => $permissions,
            'is_system' => false,
            'active' => true,
        ]);

        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role_id' => $role->id,
            'email' => 'operator+'.uniqid().'@church.local',
            'password' => 'password',
            'active' => true,
        ]);

        $this->actingAs($user, 'sanctum');

        return $user;
    }
}
```

- [ ] **Step 3: Rodar e confirmar que falha**

Run: `php artisan test --filter=FamiliesPaginationTest`
Expected: FAIL — a resposta atual é um array plano e não há autorização.

- [ ] **Step 4: Implementar os cinco arquivos de Families**

Criar `app/Modules/Families/Http/Requests/ListFamiliesRequest.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\Families\Http\Requests;

use App\Support\Http\Requests\IndexRequest;

final class ListFamiliesRequest extends IndexRequest
{
    /**
     * @return array<int, string>
     */
    protected function sortableColumns(): array
    {
        return ['name', 'wedding_date', 'created_at'];
    }

    protected function defaultSort(): string
    {
        return 'name';
    }
}
```

Criar `app/Modules/Families/Http/Requests/StoreFamilyRequest.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\Families\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreFamilyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'address_id' => ['nullable', 'integer', Rule::exists('addresses', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'wedding_date' => ['nullable', 'date'],
            'email' => ['nullable', 'email', 'max:255'],
            'home_phone' => ['nullable', 'string', 'max:30'],
            'work_phone' => ['nullable', 'string', 'max:30'],
            'mobile_phone' => ['nullable', 'string', 'max:30'],
            'envelope_number' => ['nullable', 'integer'],
            'newsletter_enabled' => ['nullable', 'boolean'],
            'canvass_allowed' => ['nullable', 'boolean'],
            'deactivated_at' => ['nullable', 'date'],
        ];
    }
}
```

Criar `app/Modules/Families/Http/Requests/UpdateFamilyRequest.php` com as mesmas regras, cada uma prefixada por `'sometimes'` e com `'required'` removido de `name`.

Criar `app/Modules/Families/Http/Resources/FamilyResource.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\Families\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class FamilyResource extends JsonResource
{
    /**
     * Legacy rule: src/v2/templates/people/familylist.php hides address and
     * phone columns from users without private-data access, mirroring
     * User::isSeePrivacyDataEnabled in the legacy app.
     */
    public const PRIVATE_DATA_ABILITY = 'families.private_data.view';

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $showsPrivateData = $request->user()?->role?->allows(self::PRIVATE_DATA_ABILITY) ?? false;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'wedding_date' => $this->wedding_date?->toDateString(),
            'envelope_number' => $this->envelope_number,
            'newsletter_enabled' => (bool) $this->newsletter_enabled,
            'canvass_allowed' => (bool) $this->canvass_allowed,
            'deactivated_at' => $this->deactivated_at?->toIso8601String(),
            'private_data_hidden' => ! $showsPrivateData,
            'email' => $showsPrivateData ? $this->email : null,
            'home_phone' => $showsPrivateData ? $this->home_phone : null,
            'work_phone' => $showsPrivateData ? $this->work_phone : null,
            'mobile_phone' => $showsPrivateData ? $this->mobile_phone : null,
            'address' => $showsPrivateData
                ? $this->whenLoaded('address', fn (): ?array => $this->address === null ? null : [
                    'id' => $this->address->id,
                    'line1' => $this->address->line1,
                    'line2' => $this->address->line2,
                    'city' => $this->address->city,
                    'state' => $this->address->state,
                    'postal_code' => $this->address->postal_code,
                ])
                : null,
            'people' => $this->whenLoaded('people', fn (): array => $this->people
                ->map(fn ($person): array => [
                    'id' => $person->id,
                    'first_name' => $person->first_name,
                    'last_name' => $person->last_name,
                ])
                ->all()),
        ];
    }
}
```

Criar `app/Policies/FamilyPolicy.php`:

```php
<?php

declare(strict_types=1);

namespace App\Policies;

use App\Support\Authorization\ModulePolicy;

final class FamilyPolicy extends ModulePolicy
{
    protected function abilityPrefix(): string
    {
        return 'families';
    }

    protected function navigationAbility(): string
    {
        return 'navigation.families';
    }
}
```

Substituir `app/Modules/Families/Actions/ListFamiliesAction.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\Families\Actions;

use App\Models\Family;
use App\Modules\Families\Http\Requests\ListFamiliesRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class ListFamiliesAction
{
    public function execute(ListFamiliesRequest $request): LengthAwarePaginator
    {
        return Family::query()
            ->with(['address', 'people'])
            ->when(
                $request->searchTerm(),
                fn (Builder $query, string $term): Builder => $query->where('name', 'like', '%'.$term.'%'),
            )
            ->orderBy($request->sortColumn(), $request->sortDirection())
            ->orderBy('id')
            ->paginate($request->perPage())
            ->withQueryString();
    }
}
```

Reescrever `app/Modules/Families/Http/Controllers/FamilyController.php` espelhando o `PersonController` final da Task 4: `Gate::authorize(...)` em cada método, Form Requests injetados, `FamilyResource` no retorno, `CreateFamilyAction::execute(array $data)` sem `tenant_id`, e nenhum `abort_unless`.

Registrar a policy em `app/Providers/AppServiceProvider.php`, no `boot()`:

```php
\Illuminate\Support\Facades\Gate::policy(\App\Models\Family::class, \App\Policies\FamilyPolicy::class);
```

- [ ] **Step 5: Rodar os testes do módulo**

Run: `php artisan test --filter=Families`
Expected: PASS. `FamiliesModuleTest` vai falhar primeiro no envelope e nas roles — ajustar as fixtures para `data.*` e para role com `['*' => true]`.

- [ ] **Step 6: Commit**

```bash
vendor/bin/pint app/Modules/Families app/Policies app/Providers
git add app/Modules/Families app/Policies/FamilyPolicy.php app/Providers/AppServiceProvider.php tests/Feature
git commit -m "refactor: align families module with the shared api contract"
```

- [ ] **Step 7: Repetir os Steps 1–6 para Groups, Events, Finance e Users**

Para cada módulo, os mesmos cinco arquivos (`List<M>Request`, `Store<X>Request`, `Update<X>Request`, `<X>Resource`, `<X>Policy`) mais a Action e o Controller, usando as colunas e o arquivo legado de referência da tabela no topo desta task. Um commit por módulo.

Regra de mascaramento por módulo — confirmar no template legado antes de implementar:
- **Groups / Events:** o legado não mascara; `Resource` sem `private_data_hidden`.
- **Finance:** o legado exige `isFinanceEnabled()` para a tela inteira (`src/EcclesiaCRM/model/EcclesiaCRM/User.php:638`) — isso já é coberto por `navigation.finance` na policy; sem mascaramento de campo.
- **Users:** nunca expor `password` nem `remember_token` no `UserResource`; expor `role` apenas como `{id, slug, name}`.

- [ ] **Step 8: Rodar a suíte inteira**

Run: `php artisan test`
Expected: tudo PASS, sem nenhum controller restante fazendo `abort_unless` de tenant ou `->get()->all()`.

Verificação final:

```bash
grep -rn "abort_unless" app/Modules/ ; grep -rn "->get()->all()" app/Modules/
```
Expected: nenhuma saída.

---

# FASE B — SPA consumindo o contrato

### Task 6: Listagens lazy com estado na URL

A tabela hoje recebe o array inteiro e pagina no cliente. Com o contrato da Task 2, a paginação, a ordenação e a busca passam a ir ao servidor, e o estado vive na query string — o usuário pode voltar, recarregar e compartilhar o link sem perder o contexto. Isso importa especialmente para usuários idosos, que perdem a orientação quando o "voltar" descarta filtros.

**Files:**
- Create: `resources/js/api/query.js`
- Modify: `resources/js/components/tables/BaseDataTable.vue`
- Modify: `resources/js/pages/modules/people/PeopleListPage.vue`
- Modify: `resources/js/i18n/locales/pt-BR.json`, `resources/js/i18n/locales/en.json`

**Interfaces:**
- Produces: `resources/js/api/query.js` → `readListState(route): {page:number, perPage:number, sort:string|null, search:string}` e `writeListState(router, route, state): Promise<void>`
- Produces: `BaseDataTable` props `rows`, `totalRecords`, `loading`, `rowsPerPage`, `sortField`, `sortOrder`; eventos `@page`, `@sort`
- Consumes: envelope `{data, meta:{current_page, per_page, total}}` da Task 2

- [ ] **Step 1: Implementar o helper de query state**

Criar `resources/js/api/query.js`:

```js
export const DEFAULT_PER_PAGE = 25;

export function readListState(route) {
    const page = Number.parseInt(route.query.page ?? '1', 10);
    const perPage = Number.parseInt(route.query.per_page ?? String(DEFAULT_PER_PAGE), 10);

    return {
        page: Number.isNaN(page) || page < 1 ? 1 : page,
        perPage: Number.isNaN(perPage) || perPage < 1 ? DEFAULT_PER_PAGE : perPage,
        sort: route.query.sort ?? null,
        search: route.query.search ?? '',
    };
}

export function writeListState(router, route, state) {
    const query = { ...route.query };

    query.page = String(state.page);
    query.per_page = String(state.perPage);

    if (state.sort) {
        query.sort = state.sort;
    } else {
        delete query.sort;
    }

    if (state.search) {
        query.search = state.search;
    } else {
        delete query.search;
    }

    return router.replace({ path: route.path, query });
}

export function toRequestParams(state) {
    const params = { page: state.page, per_page: state.perPage };

    if (state.sort) {
        params.sort = state.sort;
    }

    if (state.search) {
        params.search = state.search;
    }

    return params;
}
```

- [ ] **Step 2: Tornar a `BaseDataTable` lazy**

Substituir todo o conteúdo de `resources/js/components/tables/BaseDataTable.vue`:

```vue
<template>
    <div class="base-data-table">
        <PDataTable
            :value="rows"
            lazy
            paginator
            :rows="rowsPerPage"
            :total-records="totalRecords"
            :loading="loading"
            :first="(currentPage - 1) * rowsPerPage"
            :sort-field="sortField"
            :sort-order="sortOrder"
            removable-sort
            responsive-layout="scroll"
            @page="$emit('page', { page: $event.page + 1, rows: $event.rows })"
            @sort="$emit('sort', { field: $event.sortField, order: $event.sortOrder })"
        >
            <slot />
        </PDataTable>
    </div>
</template>

<script setup>
defineProps({
    rows: {
        type: Array,
        default: () => [],
    },
    totalRecords: {
        type: Number,
        default: 0,
    },
    currentPage: {
        type: Number,
        default: 1,
    },
    rowsPerPage: {
        type: Number,
        default: 25,
    },
    loading: {
        type: Boolean,
        default: false,
    },
    sortField: {
        type: String,
        default: null,
    },
    sortOrder: {
        type: Number,
        default: null,
    },
});

defineEmits(['page', 'sort']);
</script>
```

- [ ] **Step 3: Ligar a página de People**

Substituir o `<script setup>` e o bloco da tabela em `resources/js/pages/modules/people/PeopleListPage.vue`:

```vue
<template>
    <AppShell :title="t('people.title')">
        <div class="hero-panel">
            <div class="hero-panel__eyebrow">{{ t('people.eyebrow') }}</div>
            <h1 class="hero-panel__title">{{ t('people.title') }}</h1>
            <p class="hero-panel__copy">{{ t('people.copy') }}</p>
        </div>

        <PCard style="margin-top: 24px;">
            <template #title>{{ t('people.registered') }}</template>
            <template #content>
                <BaseDataTable
                    :rows="people"
                    :total-records="total"
                    :current-page="state.page"
                    :rows-per-page="state.perPage"
                    :loading="loading"
                    :sort-field="sortField"
                    :sort-order="sortOrder"
                    @page="onPage"
                    @sort="onSort"
                >
                    <PColumn field="first_name" :header="t('people.columns.first_name')" sortable />
                    <PColumn field="last_name" :header="t('people.columns.last_name')" sortable />
                    <PColumn field="birth_date" :header="t('people.columns.birth_date')" sortable />
                    <PColumn field="newsletter_enabled" :header="t('people.columns.newsletter_enabled')" />
                </BaseDataTable>
            </template>
        </PCard>
    </AppShell>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';

import { t } from '../../../i18n';
import AppShell from '../../../layouts/AppShell.vue';
import BaseDataTable from '../../../components/tables/BaseDataTable.vue';
import { listPeople } from '../../../api/modules/people';
import { readListState, toRequestParams, writeListState } from '../../../api/query';

const route = useRoute();
const router = useRouter();

const people = ref([]);
const total = ref(0);
const loading = ref(false);
const state = reactive(readListState(route));

const sortField = computed(() => (state.sort ? state.sort.replace(/^-/, '') : null));
const sortOrder = computed(() => {
    if (!state.sort) {
        return null;
    }

    return state.sort.startsWith('-') ? -1 : 1;
});

async function load() {
    loading.value = true;

    try {
        const response = await listPeople(toRequestParams(state));
        people.value = response.data.data;
        total.value = response.data.meta.total;
    } finally {
        loading.value = false;
    }
}

async function onPage({ page, rows }) {
    state.page = page;
    state.perPage = rows;
    await writeListState(router, route, state);
    await load();
}

async function onSort({ field, order }) {
    state.sort = field ? `${order === -1 ? '-' : ''}${field}` : null;
    state.page = 1;
    await writeListState(router, route, state);
    await load();
}

onMounted(load);
</script>
```

- [ ] **Step 4: Verificar no navegador**

Run: `npm run dev` (em outro terminal, `php artisan serve`)
Abrir `http://localhost:8000/people`. Verificar: a paginação muda `?page=` na URL; clicar no cabeçalho muda `?sort=`; recarregar a página mantém página e ordenação; a aba de rede mostra uma requisição por interação, com `per_page` respeitado.

- [ ] **Step 5: Commit**

```bash
git add resources/js/api/query.js resources/js/components/tables/BaseDataTable.vue resources/js/pages/modules/people/PeopleListPage.vue
git commit -m "feat: consume the paginated list contract with url-backed table state"
```

- [ ] **Step 6: Replicar nas demais listagens**

Aplicar o mesmo `<script setup>` em `FamilyListPage.vue`, `GroupListPage.vue`, `EventListPage.vue`, `FinanceListPage.vue` e `UserListPage.vue`, trocando o client de API e as colunas. Commitar uma vez por página.

---

### Task 7: Estados reais de carregamento, vazio e erro

Hoje toda página de listagem captura o erro e injeta **dados falsos** (`people.fallback_person`), o que faz uma falha de autenticação parecer conteúdo real. Isso precisa sair antes de qualquer usuário de igreja ver a tela.

**Files:**
- Create: `resources/js/components/feedback/StateBlock.vue`
- Modify: `resources/js/pages/modules/people/PeopleListPage.vue` e as demais 5 listagens
- Modify: `resources/js/api/http.js`
- Modify: `resources/js/i18n/locales/pt-BR.json`, `resources/js/i18n/locales/en.json`
- Modify: `resources/css/app.css`

**Interfaces:**
- Produces: `StateBlock` props `variant` (`'loading' | 'empty' | 'error' | 'forbidden'`), `title`, `description`, `actionLabel`; evento `@action`
- Consumes: nada da Fase A além do envelope

- [ ] **Step 1: Implementar o `StateBlock`**

Criar `resources/js/components/feedback/StateBlock.vue`:

```vue
<template>
    <div class="state-block" :class="`state-block--${variant}`" role="status" aria-live="polite">
        <p class="state-block__title">{{ title }}</p>
        <p v-if="description" class="state-block__description">{{ description }}</p>
        <PButton
            v-if="actionLabel"
            class="state-block__action"
            :label="actionLabel"
            severity="secondary"
            @click="$emit('action')"
        />
    </div>
</template>

<script setup>
defineProps({
    variant: {
        type: String,
        default: 'empty',
        validator: (value) => ['loading', 'empty', 'error', 'forbidden'].includes(value),
    },
    title: {
        type: String,
        required: true,
    },
    description: {
        type: String,
        default: '',
    },
    actionLabel: {
        type: String,
        default: '',
    },
});

defineEmits(['action']);
</script>
```

Acrescentar ao fim de `resources/css/app.css`. Os tokens `--app-text-muted` e `--app-danger-border` só nascem na Task 9, por isso vão com fallback — depois da Task 9 os fallbacks ficam inertes e podem permanecer:

```css
.state-block {
    display: grid;
    gap: 12px;
    justify-items: start;
    padding: 32px;
    border: 1px solid var(--app-border);
    border-radius: 20px;
    background: var(--app-surface-strong);
}

.state-block__title {
    margin: 0;
    font-size: 1.125rem;
    font-weight: 700;
    color: var(--app-text);
}

.state-block__description {
    margin: 0;
    font-size: 1rem;
    color: var(--app-text-muted, #4b5563);
}

.state-block--error {
    border-color: var(--app-danger-border, #f0a29b);
}
```

- [ ] **Step 2: Traduzir os estados**

Em `resources/js/i18n/locales/pt-BR.json`, adicionar um objeto raiz `"states"`:

```json
  "states": {
    "loading": "Carregando…",
    "empty_title": "Nenhum registro ainda",
    "empty_description": "Quando houver registros cadastrados, eles aparecem aqui.",
    "error_title": "Não foi possível carregar os dados",
    "error_description": "Verifique sua conexão e tente novamente.",
    "forbidden_title": "Você não tem acesso a esta área",
    "forbidden_description": "Peça a um administrador da igreja para liberar esta permissão.",
    "retry": "Tentar novamente"
  }
```

Em `resources/js/i18n/locales/en.json`:

```json
  "states": {
    "loading": "Loading…",
    "empty_title": "No records yet",
    "empty_description": "Records will show up here once they are created.",
    "error_title": "We could not load the data",
    "error_description": "Check your connection and try again.",
    "forbidden_title": "You do not have access to this area",
    "forbidden_description": "Ask a church administrator to grant this permission.",
    "retry": "Try again"
  }
```

Remover as chaves `people.fallback_person` (e equivalentes nos demais módulos) dos **dois** arquivos de locale.

- [ ] **Step 3: Distinguir 403 de erro genérico no client HTTP**

Acrescentar em `resources/js/api/http.js`, antes do `export default http;`:

```js
http.interceptors.response.use(
    (response) => response,
    (error) => {
        error.isForbidden = error.response?.status === 403;
        error.isUnauthenticated = error.response?.status === 401;

        return Promise.reject(error);
    },
);
```

- [ ] **Step 4: Substituir o fallback fabricado**

Em `PeopleListPage.vue`, trocar a função `load` e adicionar o estado de erro:

```js
const status = ref('loading');

async function load() {
    status.value = 'loading';
    loading.value = true;

    try {
        const response = await listPeople(toRequestParams(state));
        people.value = response.data.data;
        total.value = response.data.meta.total;
        status.value = people.value.length === 0 ? 'empty' : 'ready';
    } catch (error) {
        people.value = [];
        total.value = 0;
        status.value = error.isForbidden ? 'forbidden' : 'error';
    } finally {
        loading.value = false;
    }
}
```

E no `<template>`, envolver a tabela:

```vue
<StateBlock
    v-if="status !== 'ready'"
    :variant="status"
    :title="t(`states.${status === 'loading' ? 'loading' : `${status}_title`}`)"
    :description="status === 'loading' ? '' : t(`states.${status}_description`)"
    :action-label="status === 'error' ? t('states.retry') : ''"
    @action="load"
/>
<BaseDataTable v-else ... />
```

- [ ] **Step 5: Verificar manualmente os três caminhos**

Run: `npm run dev` + `php artisan serve`
1. Estado vazio: apagar as pessoas do tenant e recarregar `/people` → aparece "Nenhum registro ainda".
2. Estado proibido: remover `navigation.people` da role do usuário no banco e recarregar → aparece "Você não tem acesso a esta área".
3. Estado de erro: parar o `php artisan serve` e clicar em paginar → aparece "Não foi possível carregar os dados" com botão de tentar novamente.

- [ ] **Step 6: Commit e replicar**

```bash
git add resources/js/components/feedback resources/js/api/http.js resources/js/pages/modules resources/js/i18n resources/css/app.css
git commit -m "fix: replace fabricated list fallbacks with real loading, empty and error states"
```

Repetir os Steps 4–5 nas outras 5 listagens, um commit cada.

---

### Task 8: Remover a rota `/navigation` duplicada e cobrir o front com Vitest

`GET /navigation` existe em `routes/web.php:10` e em `routes/api.php:9`. O front consome apenas `/api/navigation`. E hoje não há **nenhum** teste de componente Vue — os `*SpaTest.php` só checam que a rota devolve o shell HTML.

**Files:**
- Modify: `routes/web.php`
- Modify: `package.json`
- Create: `vitest.config.js`
- Create: `resources/js/api/query.test.js`
- Create: `resources/js/components/feedback/StateBlock.test.js`
- Test: `tests/Feature/NavigationTest.php`

**Interfaces:**
- Consumes: `readListState`, `writeListState`, `toRequestParams` da Task 6; `StateBlock` da Task 7
- Produces: comando `npm run test`

- [ ] **Step 1: Escrever o teste PHP que falha**

Acrescentar a `tests/Feature/NavigationTest.php`:

```php
    public function test_the_web_navigation_route_is_gone(): void
    {
        $this->get('/navigation')
            ->assertOk()
            ->assertSee('<div id="app">', false);
    }
```

Este teste passa a valer porque, sem a rota, o `Route::fallback()` devolve a SPA.

- [ ] **Step 2: Rodar e confirmar que falha**

Run: `php artisan test --filter=the_web_navigation_route_is_gone`
Expected: FAIL — hoje `/navigation` devolve 401 JSON (é a rota autenticada duplicada).

- [ ] **Step 3: Remover a rota duplicada**

Em `routes/web.php`, apagar a linha `Route::middleware('auth:sanctum')->get('/navigation', NavigationController::class);` e o `use App\Http\Controllers\Api\NavigationController;` que fica órfão.

- [ ] **Step 4: Rodar o teste PHP**

Run: `php artisan test --filter=NavigationTest`
Expected: PASS.

- [ ] **Step 5: Instalar e configurar o Vitest**

Run:
```bash
npm install --save-dev vitest @vue/test-utils jsdom
```

Criar `vitest.config.js`:

```js
import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [vue()],
    test: {
        environment: 'jsdom',
        include: ['resources/js/**/*.test.js'],
    },
});
```

Adicionar em `package.json`, dentro de `"scripts"`:

```json
        "test": "vitest run",
        "test:watch": "vitest"
```

- [ ] **Step 6: Escrever os testes de front**

Criar `resources/js/api/query.test.js`:

```js
import { describe, expect, it } from 'vitest';
import { readListState, toRequestParams, DEFAULT_PER_PAGE } from './query';

describe('readListState', () => {
    it('falls back to sane defaults', () => {
        expect(readListState({ query: {} })).toEqual({
            page: 1,
            perPage: DEFAULT_PER_PAGE,
            sort: null,
            search: '',
        });
    });

    it('rejects a non-numeric page', () => {
        expect(readListState({ query: { page: 'abc' } }).page).toBe(1);
    });

    it('reads sort and search from the query string', () => {
        const state = readListState({ query: { sort: '-last_name', search: 'coelho' } });

        expect(state.sort).toBe('-last_name');
        expect(state.search).toBe('coelho');
    });
});

describe('toRequestParams', () => {
    it('omits empty sort and search', () => {
        expect(toRequestParams({ page: 2, perPage: 10, sort: null, search: '' })).toEqual({
            page: 2,
            per_page: 10,
        });
    });
});
```

Criar `resources/js/components/feedback/StateBlock.test.js`:

```js
import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import StateBlock from './StateBlock.vue';

describe('StateBlock', () => {
    it('announces itself to assistive technology', () => {
        const wrapper = mount(StateBlock, {
            props: { variant: 'error', title: 'Falhou' },
            global: { stubs: { PButton: true } },
        });

        expect(wrapper.attributes('role')).toBe('status');
        expect(wrapper.attributes('aria-live')).toBe('polite');
        expect(wrapper.text()).toContain('Falhou');
    });

    it('emits action when the retry button is clicked', async () => {
        const wrapper = mount(StateBlock, {
            props: { variant: 'error', title: 'Falhou', actionLabel: 'Tentar novamente' },
            global: { stubs: { PButton: { template: '<button @click="$emit(\'click\')" />' } } },
        });

        await wrapper.find('button').trigger('click');

        expect(wrapper.emitted('action')).toHaveLength(1);
    });
});
```

- [ ] **Step 7: Rodar os testes de front**

Run: `npm run test`
Expected: 5 testes PASS.

- [ ] **Step 8: Commit**

```bash
git add routes/web.php package.json package-lock.json vitest.config.js resources/js tests/Feature/NavigationTest.php
git commit -m "test: add vitest coverage and drop the duplicated navigation route"
```

---

# FASE C — Base visual acessível

> Público-alvo declarado: crianças, jovens e idosos usando as mesmas telas. As tasks abaixo corrigem a base; **nenhuma altera regra de negócio.** Referência de conformidade: WCAG 2.2 nível AA.

### Task 9: Tokens de design acessíveis

Problemas medidos hoje: `--app-muted: #64748b` sobre superfícies translúcidas fica abaixo de 4.5:1; labels em `0.72rem` (~11,5px); alvos de toque de 44px não garantidos; sem estilo de foco visível; `color-scheme: light` fixo; fonte carregada de CDN externo (bloqueia render e cria dependência de rede).

**Files:**
- Modify: `resources/css/app.css`
- Modify: `resources/js/styles/theme.css`
- Create: `resources/css/fonts.css`
- Modify: `package.json` (dependência `@fontsource-variable/manrope`)
- Test: `resources/js/styles/tokens.test.js`

**Interfaces:**
- Produces: tokens `--app-text`, `--app-text-muted`, `--app-surface`, `--app-surface-strong`, `--app-border`, `--app-primary`, `--app-primary-contrast`, `--app-danger`, `--app-danger-border`, `--app-focus`, `--app-touch-target`, `--app-font-size-base`
- Consumes: `StateBlock` (Task 7) já referencia `--app-text-muted`, `--app-surface-strong` e `--app-danger-border`

- [ ] **Step 1: Escrever o teste de contraste que falha**

Criar `resources/js/styles/tokens.test.js`:

```js
import { readFileSync } from 'node:fs';
import { describe, expect, it } from 'vitest';

const css = readFileSync(new URL('../../css/app.css', import.meta.url), 'utf8');

function readToken(name, block = ':root') {
    const blockBody = css.split(block)[1]?.split('}')[0] ?? '';
    const match = blockBody.match(new RegExp(`${name}:\\s*([^;]+);`));

    return match ? match[1].trim() : null;
}

function relativeLuminance(hex) {
    const channels = [1, 3, 5]
        .map((offset) => Number.parseInt(hex.slice(offset, offset + 2), 16) / 255)
        .map((channel) => (channel <= 0.03928 ? channel / 12.92 : ((channel + 0.055) / 1.055) ** 2.4));

    return 0.2126 * channels[0] + 0.7152 * channels[1] + 0.0722 * channels[2];
}

function contrastRatio(foreground, background) {
    const lighter = Math.max(relativeLuminance(foreground), relativeLuminance(background));
    const darker = Math.min(relativeLuminance(foreground), relativeLuminance(background));

    return (lighter + 0.05) / (darker + 0.05);
}

describe('design tokens', () => {
    it('keeps muted text readable against the strong surface', () => {
        const ratio = contrastRatio(readToken('--app-text-muted'), readToken('--app-surface-strong'));

        expect(ratio).toBeGreaterThanOrEqual(4.5);
    });

    it('keeps body text well above the AA threshold', () => {
        const ratio = contrastRatio(readToken('--app-text'), readToken('--app-surface-strong'));

        expect(ratio).toBeGreaterThanOrEqual(7);
    });

    it('keeps primary button text readable', () => {
        const ratio = contrastRatio(readToken('--app-primary-contrast'), readToken('--app-primary'));

        expect(ratio).toBeGreaterThanOrEqual(4.5);
    });

    it('does not load fonts from an external host', () => {
        expect(css).not.toContain('fonts.googleapis.com');
    });

    it('declares a touch target of at least 44px', () => {
        expect(readToken('--app-touch-target')).toBe('44px');
    });
});
```

- [ ] **Step 2: Rodar e confirmar que falha**

Run: `npm run test -- tokens`
Expected: FAIL — `--app-text-muted` e `--app-primary-contrast` não existem; o `@import` do Google Fonts está presente.

- [ ] **Step 3: Instalar a fonte como dependência local**

Instalar via npm em vez de baixar um arquivo do CDN por URL — a URL do gstatic muda a cada revisão da fonte e não é reprodutível:

```bash
npm install --save-dev @fontsource-variable/manrope
```

Criar `resources/css/fonts.css`:

```css
@import '@fontsource-variable/manrope/index.css';
```

O Vite resolve o import do pacote e emite os `.woff2` em `public/build/assets/`, servidos do mesmo host — sem requisição externa.

> Ajustar a linha em **Files** desta task: `public/fonts/manrope-variable.woff2` deixa de ser criado; a fonte passa a vir de `node_modules/@fontsource-variable/manrope`.

- [ ] **Step 4: Reescrever o bloco de tokens**

Em `resources/css/app.css`, substituir o `@import url('https://fonts.googleapis.com/...')` e o bloco `:root` por:

```css
@import './fonts.css';

:root {
    color-scheme: light;

    --app-bg: #f4f6fb;
    --app-surface: #ffffff;
    --app-surface-strong: #ffffff;
    --app-surface-sunken: #eef1f7;
    --app-border: #cbd2de;

    --app-text: #101828;
    --app-text-muted: #4b5563;

    --app-primary: #1d4ed8;
    --app-primary-strong: #1e3a8a;
    --app-primary-contrast: #ffffff;

    --app-accent: #0f5f59;
    --app-danger: #b42318;
    --app-danger-border: #f0a29b;

    --app-focus: #1d4ed8;
    --app-focus-ring: 0 0 0 3px rgba(29, 78, 216, 0.45);

    --app-touch-target: 44px;
    --app-font-size-base: 16px;
    --app-font-size-label: 0.875rem;
    --app-radius: 16px;
    --app-shadow: 0 12px 32px rgba(16, 24, 40, 0.1);
}
```

> `--app-text-muted: #4b5563` sobre `#ffffff` dá 7,0:1; `--app-primary-contrast: #ffffff` sobre `--app-primary: #1d4ed8` dá 6,3:1. Os valores anteriores (`#64748b` sobre superfície translúcida) ficavam em ~4,0:1.

- [ ] **Step 5: Corrigir tipografia, alvos de toque e foco**

Em `resources/css/app.css`, ajustar as regras existentes:

```css
body {
    margin: 0;
    font-family: 'Manrope', 'Segoe UI', system-ui, sans-serif;
    font-size: var(--app-font-size-base);
    line-height: 1.55;
    color: var(--app-text);
    background: var(--app-bg);
}

.app-nav__item {
    display: flex;
    align-items: center;
    gap: 12px;
    min-height: var(--app-touch-target);
    padding: 12px 16px;
    border-radius: var(--app-radius);
    font-size: 1rem;
    font-weight: 600;
    color: var(--app-text);
    border: 1px solid transparent;
}

.app-nav__item:hover {
    background: var(--app-surface-sunken);
}

.app-nav__item.is-active {
    background: var(--app-surface-sunken);
    border-color: var(--app-primary);
    color: var(--app-primary-strong);
}

:is(a, button, input, select, textarea, [tabindex]):focus-visible {
    outline: 2px solid var(--app-focus);
    outline-offset: 2px;
    box-shadow: var(--app-focus-ring);
}

@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        animation-duration: 0.01ms !important;
        transition-duration: 0.01ms !important;
    }
}
```

Remover de `.app-shell__surface`, `.app-sidebar` e `.app-topbar` as declarações `backdrop-filter: blur(...)` e os fundos `rgba(...)`, trocando por `var(--app-surface)` — o texto de conteúdo passa a ficar sobre fundo opaco e o contraste vira previsível.

Em `resources/js/styles/theme.css`, trocar `.app-field__label { font-size: 0.82rem; ... color: var(--app-muted); }` por:

```css
.app-field__label {
    font-size: var(--app-font-size-label);
    font-weight: 600;
    letter-spacing: 0.01em;
    text-transform: none;
    color: var(--app-text);
}
```

e `.app-nav__section-title { font-size: 0.72rem; ... }` por `font-size: var(--app-font-size-label); color: var(--app-text-muted);`.

> `text-transform: uppercase` sai: caixa alta reduz a legibilidade de rótulos longos e é especialmente ruim para leitores com baixa visão.

- [ ] **Step 6: Rodar os testes**

Run: `npm run test`
Expected: os 5 testes de token PASS, os testes anteriores continuam PASS.

Run: `npm run build`
Expected: build sem erro; conferir que `public/build/assets/*.css` não referencia `fonts.googleapis.com`.

- [ ] **Step 7: Commit**

```bash
git add resources/css resources/js/styles package.json package-lock.json
git commit -m "fix: adopt accessible design tokens, local fonts and visible focus"
```

---

### Task 10: Icon set SVG acessível

Os ícones de navegação hoje são caracteres Unicode (`'⌂'`, `'◉'`, `'◑'`, `'✚'`, `'¤'`, `'♫'`) renderizados dentro de um `<span>`. Leitores de tela os anunciam como texto sem sentido ("sinal de moeda genérico"), o tamanho varia por sistema operacional e alguns não têm glifo em Android. O legado usava Font Awesome com classes semânticas (`MenuBar.php`); a paridade correta no novo é um SVG inline com `aria-hidden`.

**Files:**
- Create: `resources/js/components/icons/AppIcon.vue`
- Modify: `resources/js/navigation/siteNavigation.js`
- Modify: `app/Http/Controllers/Api/NavigationController.php`
- Modify: `resources/js/components/navigation/AppSidebar.vue`
- Test: `resources/js/components/icons/AppIcon.test.js`
- Test: `tests/Feature/NavigationTest.php`

**Interfaces:**
- Produces: `AppIcon` prop `name` (`'dashboard' | 'users' | 'people' | 'families' | 'groups' | 'events' | 'communications' | 'care' | 'finance' | 'calendar' | 'kiosk' | 'repertoire' | 'manuals' | 'whatsapp'`)
- Contrato: o backend passa a enviar `'icon' => 'people'` (chave do set), não mais o caractere

- [ ] **Step 1: Escrever os testes que falham**

Criar `resources/js/components/icons/AppIcon.test.js`:

```js
import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import AppIcon from './AppIcon.vue';

describe('AppIcon', () => {
    it('is hidden from assistive technology', () => {
        const wrapper = mount(AppIcon, { props: { name: 'people' } });

        expect(wrapper.find('svg').attributes('aria-hidden')).toBe('true');
        expect(wrapper.find('svg').attributes('focusable')).toBe('false');
    });

    it('renders a fallback instead of crashing on an unknown name', () => {
        const wrapper = mount(AppIcon, { props: { name: 'does-not-exist' } });

        expect(wrapper.find('svg').exists()).toBe(true);
    });
});
```

Acrescentar a `tests/Feature/NavigationTest.php`:

```php
    public function test_navigation_icons_are_semantic_keys_not_glyphs(): void
    {
        $response = $this->actingAs($this->adminUser(), 'sanctum')->getJson('/api/navigation');

        $response->assertOk();

        foreach ($response->json('sections') as $section) {
            foreach ($section['items'] as $item) {
                $this->assertMatchesRegularExpression('/^[a-z][a-z0-9-]*$/', $item['icon']);
            }
        }
    }
```

`NavigationTest` monta o tenant, a role e o usuário inline em cada teste — não há helper. Extrair um, e reusá-lo também nos dois testes existentes:

```php
    private function adminUser(string $slug = 'default'): User
    {
        $tenant = Tenant::create([
            'slug' => $slug,
            'name' => ucfirst($slug).' Church',
            'locale' => 'pt_BR',
            'timezone' => 'America/Fortaleza',
            'active' => true,
        ]);

        $role = Role::create([
            'tenant_id' => $tenant->id,
            'slug' => 'admin',
            'name' => 'Admin',
            'description' => 'Administrator',
            'permissions' => ['*' => true],
            'is_system' => true,
            'active' => true,
        ]);

        return User::factory()->create([
            'tenant_id' => $tenant->id,
            'role_id' => $role->id,
            'email' => 'admin+'.uniqid().'@church.local',
            'password' => 'password',
            'active' => true,
        ]);
    }
```

- [ ] **Step 2: Rodar e confirmar que falham**

Run: `npm run test -- AppIcon` → FAIL (componente não existe).
Run: `php artisan test --filter=navigation_icons` → FAIL (o ícone atual é `⌂`).

- [ ] **Step 3: Implementar o `AppIcon`**

Criar `resources/js/components/icons/AppIcon.vue`:

```vue
<template>
    <svg
        class="app-icon"
        viewBox="0 0 24 24"
        width="24"
        height="24"
        fill="none"
        stroke="currentColor"
        stroke-width="1.8"
        stroke-linecap="round"
        stroke-linejoin="round"
        aria-hidden="true"
        focusable="false"
    >
        <path v-for="(d, index) in paths" :key="index" :d="d" />
    </svg>
</template>

<script setup>
import { computed } from 'vue';

const ICONS = {
    dashboard: ['M3 11l9-8 9 8', 'M5 10v10h14V10'],
    users: ['M16 20v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2', 'M9 10a4 4 0 1 0 0-8 4 4 0 0 0 0 8', 'M22 20v-2a4 4 0 0 0-3-3.87'],
    people: ['M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8', 'M4 21v-1a6 6 0 0 1 6-6h4a6 6 0 0 1 6 6v1'],
    families: ['M3 11l9-7 9 7', 'M5 10v10h14V10', 'M9 20v-5h6v5'],
    groups: ['M8 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6', 'M17 12a3 3 0 1 0 0-6 3 3 0 0 0 0 6', 'M2 20v-1a5 5 0 0 1 5-5h2a5 5 0 0 1 5 5v1', 'M15 14h1a5 5 0 0 1 5 5v1'],
    events: ['M7 3v4', 'M17 3v4', 'M4 8h16', 'M4 5h16v16H4z', 'M9 13h2v2H9z'],
    communications: ['M3 6h18v12H3z', 'M3 7l9 6 9-6'],
    care: ['M12 21s-7-4.5-7-9a4 4 0 0 1 7-2.6A4 4 0 0 1 19 12c0 4.5-7 9-7 9z'],
    finance: ['M12 2v20', 'M17 6H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6'],
    calendar: ['M7 3v4', 'M17 3v4', 'M4 8h16', 'M4 5h16v16H4z'],
    kiosk: ['M5 3h14v14H5z', 'M9 21h6', 'M12 17v4'],
    repertoire: ['M9 18V5l10-2v13', 'M9 18a3 3 0 1 1-6 0 3 3 0 0 1 6 0z', 'M19 16a3 3 0 1 1-6 0 3 3 0 0 1 6 0z'],
    manuals: ['M4 4h9a3 3 0 0 1 3 3v13a2 2 0 0 0-2-2H4z', 'M20 4h-4a3 3 0 0 0-3 3v13a2 2 0 0 1 2-2h5z'],
    whatsapp: ['M21 12a9 9 0 0 1-13.3 7.9L3 21l1.2-4.5A9 9 0 1 1 21 12z', 'M8.5 9.5c0 3.3 2.7 6 6 6'],
    fallback: ['M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18z'],
};

const props = defineProps({
    name: {
        type: String,
        required: true,
    },
});

const paths = computed(() => ICONS[props.name] ?? ICONS.fallback);
</script>
```

- [ ] **Step 4: Trocar as chaves no backend e no fallback do front**

Em `app/Http/Controllers/Api/NavigationController.php`, substituir cada `'icon' => '⌂'` etc. pela chave correspondente: `'dashboard'`, `'users'`, `'people'`, `'families'`, `'groups'`, `'events'`, `'communications'`, `'care'`, `'finance'`, `'calendar'`, `'kiosk'`, `'repertoire'`, `'manuals'`, `'whatsapp'`.

Aplicar exatamente a mesma troca em `resources/js/navigation/siteNavigation.js`.

- [ ] **Step 5: Usar o componente na sidebar**

Em `resources/js/components/navigation/AppSidebar.vue`, trocar `<span class="app-nav__icon">{{ item.icon }}</span>` por:

```vue
<span class="app-nav__icon"><AppIcon :name="item.icon" /></span>
```

e adicionar `import AppIcon from '../icons/AppIcon.vue';` no `<script setup>`.

- [ ] **Step 6: Rodar os testes**

Run: `npm run test` → PASS.
Run: `php artisan test --filter=NavigationTest` → PASS.

- [ ] **Step 7: Commit**

```bash
vendor/bin/pint app/Http/Controllers/Api/NavigationController.php
git add resources/js/components/icons resources/js/components/navigation resources/js/navigation app/Http/Controllers/Api/NavigationController.php tests/Feature/NavigationTest.php
git commit -m "feat: replace unicode glyph icons with an accessible svg icon set"
```

---

### Task 11: Modo escuro (paridade com o legado)

O legado tinha modo claro/escuro por usuário (`window.CRM.bDarkMode` e `sLightDarkMode`, em `src/Include/Header-function.php:216-217`, alimentados por `EcclesiaCRM\Theme`). O novo perdeu isso: `resources/css/app.css` declara `color-scheme: light` fixo e o PrimeVue está com `darkModeSelector: 'system'`, que nunca é acionado porque nada aplica a classe. **Isto é recuperar função existente, não criar função nova.**

**Files:**
- Create: `resources/js/stores/preferences.js`
- Modify: `resources/css/app.css`
- Modify: `resources/js/app.js`
- Modify: `resources/js/components/navigation/AppTopbar.vue`
- Modify: `resources/js/i18n/locales/pt-BR.json`, `resources/js/i18n/locales/en.json`
- Test: `resources/js/stores/preferences.test.js`

**Interfaces:**
- Produces: store `usePreferencesStore` com estado `{theme: 'light'|'dark'|'system'}` e ação `setTheme(value)`, persistido em `localStorage` sob `crmigrejas.preferences`
- Produces: atributo `data-theme="light|dark"` no `<html>`

- [ ] **Step 1: Escrever o teste que falha**

Criar `resources/js/stores/preferences.test.js`:

```js
import { beforeEach, describe, expect, it } from 'vitest';
import { createPinia, setActivePinia } from 'pinia';
import { usePreferencesStore } from './preferences';

describe('preferences store', () => {
    beforeEach(() => {
        setActivePinia(createPinia());
        window.localStorage.clear();
        document.documentElement.removeAttribute('data-theme');
    });

    it('defaults to the system theme', () => {
        expect(usePreferencesStore().theme).toBe('system');
    });

    it('applies the chosen theme to the document element', () => {
        const preferences = usePreferencesStore();

        preferences.setTheme('dark');

        expect(document.documentElement.getAttribute('data-theme')).toBe('dark');
    });

    it('persists the chosen theme', () => {
        usePreferencesStore().setTheme('dark');

        expect(JSON.parse(window.localStorage.getItem('crmigrejas.preferences')).theme).toBe('dark');
    });
});
```

- [ ] **Step 2: Rodar e confirmar que falha**

Run: `npm run test -- preferences`
Expected: FAIL — o store não existe.

- [ ] **Step 3: Implementar o store**

Criar `resources/js/stores/preferences.js`:

```js
import { defineStore } from 'pinia';

const storageKey = 'crmigrejas.preferences';

const defaults = {
    theme: 'system',
};

function readStored() {
    try {
        return { ...defaults, ...JSON.parse(window.localStorage.getItem(storageKey) ?? '{}') };
    } catch {
        return { ...defaults };
    }
}

function prefersDark() {
    return window.matchMedia?.('(prefers-color-scheme: dark)').matches ?? false;
}

export const usePreferencesStore = defineStore('preferences', {
    state: () => readStored(),
    actions: {
        persist() {
            window.localStorage.setItem(storageKey, JSON.stringify(this.$state));
        },
        apply() {
            const resolved = this.theme === 'system' ? (prefersDark() ? 'dark' : 'light') : this.theme;

            document.documentElement.setAttribute('data-theme', resolved);
        },
        setTheme(theme) {
            this.theme = theme;
            this.apply();
            this.persist();
        },
    },
});
```

- [ ] **Step 4: Adicionar a paleta escura**

Acrescentar a `resources/css/app.css`, logo após o bloco `:root`:

```css
:root[data-theme='dark'] {
    color-scheme: dark;

    --app-bg: #0b1120;
    --app-surface: #131c2e;
    --app-surface-strong: #131c2e;
    --app-surface-sunken: #1c2740;
    --app-border: #35405a;

    --app-text: #f1f5f9;
    --app-text-muted: #c2cad8;

    --app-primary: #7aa2ff;
    --app-primary-strong: #a7c0ff;
    --app-primary-contrast: #0b1120;

    --app-accent: #5eead4;
    --app-danger: #fca5a5;
    --app-danger-border: #8f3d38;

    --app-focus: #7aa2ff;
    --app-focus-ring: 0 0 0 3px rgba(122, 162, 255, 0.5);
}
```

> Contraste no escuro: `--app-text-muted: #c2cad8` sobre `--app-surface-strong: #131c2e` dá 9,4:1; `--app-primary-contrast: #0b1120` sobre `--app-primary: #7aa2ff` dá 8,1:1.

Estender `resources/js/styles/tokens.test.js` com os mesmos 3 testes de contraste lidos do bloco `:root[data-theme='dark']` (usar `readToken(name, ":root[data-theme='dark']")`).

- [ ] **Step 5: Aplicar no boot e expor o controle**

Em `resources/js/app.js`, depois de `app.use(pinia);`:

```js
import { usePreferencesStore } from './stores/preferences';

usePreferencesStore(pinia).apply();
```

E trocar a config do PrimeVue para `darkModeSelector: '[data-theme="dark"]'`.

Em `resources/js/components/navigation/AppTopbar.vue`, adicionar dentro de `.app-topbar__actions`, antes do botão de novo registro:

```vue
<PButton
    class="app-topbar__theme"
    severity="secondary"
    text
    :aria-label="t('preferences.toggle_theme')"
    :label="t(`preferences.theme_${preferences.theme}`)"
    @click="cycleTheme"
/>
```

e no `<script setup>`:

```js
import { usePreferencesStore } from '../../stores/preferences';

const preferences = usePreferencesStore();
const themeOrder = ['system', 'light', 'dark'];

function cycleTheme() {
    const next = themeOrder[(themeOrder.indexOf(preferences.theme) + 1) % themeOrder.length];
    preferences.setTheme(next);
}
```

- [ ] **Step 6: Traduzir**

Em `resources/js/i18n/locales/pt-BR.json`, objeto raiz `"preferences"`:

```json
  "preferences": {
    "toggle_theme": "Alternar entre tema do sistema, claro e escuro",
    "theme_system": "Tema do sistema",
    "theme_light": "Tema claro",
    "theme_dark": "Tema escuro"
  }
```

Em `en.json`:

```json
  "preferences": {
    "toggle_theme": "Switch between system, light and dark theme",
    "theme_system": "System theme",
    "theme_light": "Light theme",
    "theme_dark": "Dark theme"
  }
```

- [ ] **Step 7: Rodar os testes e verificar no navegador**

Run: `npm run test` → PASS.
Run: `npm run dev`, abrir a app, clicar no botão de tema três vezes e confirmar que o ciclo é sistema → claro → escuro, que a escolha sobrevive ao reload e que os componentes PrimeVue acompanham.

- [ ] **Step 8: Commit**

```bash
git add resources/js/stores/preferences.js resources/js/stores/preferences.test.js resources/js/app.js resources/js/components/navigation/AppTopbar.vue resources/css/app.css resources/js/styles/tokens.test.js resources/js/i18n
git commit -m "feat: restore light and dark theme preference from the legacy app"
```

---

### Task 12: Preferência de densidade e tamanho de fonte

Última peça da acessibilidade multi-geracional: um voluntário idoso e um adolescente usando a mesma tela precisam de densidades diferentes. Zoom do navegador quebra o layout em grid; uma preferência de escala não quebra.

**Files:**
- Modify: `resources/js/stores/preferences.js`
- Modify: `resources/css/app.css`
- Modify: `resources/js/components/navigation/AppTopbar.vue`
- Modify: `resources/js/i18n/locales/pt-BR.json`, `resources/js/i18n/locales/en.json`
- Modify: `resources/js/stores/preferences.test.js`

**Interfaces:**
- Produces: estado adicional `{density: 'comfortable'|'compact', textScale: 'default'|'large'|'xlarge'}` e ações `setDensity(value)`, `setTextScale(value)`
- Produces: atributos `data-density` e `data-text-scale` no `<html>`

- [ ] **Step 1: Escrever o teste que falha**

Acrescentar a `resources/js/stores/preferences.test.js`:

```js
    it('applies density and text scale to the document element', () => {
        const preferences = usePreferencesStore();

        preferences.setDensity('compact');
        preferences.setTextScale('xlarge');

        expect(document.documentElement.getAttribute('data-density')).toBe('compact');
        expect(document.documentElement.getAttribute('data-text-scale')).toBe('xlarge');
    });

    it('rejects an unknown text scale and keeps the current one', () => {
        const preferences = usePreferencesStore();

        preferences.setTextScale('gigantic');

        expect(preferences.textScale).toBe('default');
    });
```

- [ ] **Step 2: Rodar e confirmar que falha**

Run: `npm run test -- preferences`
Expected: FAIL — `setDensity` não é uma função.

- [ ] **Step 3: Estender o store**

Em `resources/js/stores/preferences.js`, trocar `defaults` e acrescentar as ações:

```js
const defaults = {
    theme: 'system',
    density: 'comfortable',
    textScale: 'default',
};

const DENSITIES = ['comfortable', 'compact'];
const TEXT_SCALES = ['default', 'large', 'xlarge'];
```

E dentro de `actions`, ampliar `apply()` e adicionar os setters:

```js
        apply() {
            const resolved = this.theme === 'system' ? (prefersDark() ? 'dark' : 'light') : this.theme;

            document.documentElement.setAttribute('data-theme', resolved);
            document.documentElement.setAttribute('data-density', this.density);
            document.documentElement.setAttribute('data-text-scale', this.textScale);
        },
        setDensity(density) {
            if (!DENSITIES.includes(density)) {
                return;
            }

            this.density = density;
            this.apply();
            this.persist();
        },
        setTextScale(textScale) {
            if (!TEXT_SCALES.includes(textScale)) {
                return;
            }

            this.textScale = textScale;
            this.apply();
            this.persist();
        },
```

- [ ] **Step 4: Implementar as escalas em CSS**

Acrescentar a `resources/css/app.css`:

```css
:root[data-text-scale='large'] {
    --app-font-size-base: 18px;
    --app-font-size-label: 1rem;
}

:root[data-text-scale='xlarge'] {
    --app-font-size-base: 20px;
    --app-font-size-label: 1.0625rem;
    --app-touch-target: 52px;
}

:root[data-density='compact'] {
    --app-density-gap: 8px;
    --app-density-padding: 8px 12px;
}

:root[data-density='comfortable'] {
    --app-density-gap: 16px;
    --app-density-padding: 12px 16px;
}

.app-content {
    display: grid;
    gap: var(--app-density-gap);
}

.app-nav__item {
    padding: var(--app-density-padding);
}
```

- [ ] **Step 5: Expor o controle no topbar**

Em `resources/js/components/navigation/AppTopbar.vue`, adicionar ao lado do botão de tema:

```vue
<PButton
    severity="secondary"
    text
    :aria-label="t('preferences.toggle_text_scale')"
    :label="t(`preferences.text_scale_${preferences.textScale}`)"
    @click="cycleTextScale"
/>
```

e no `<script setup>`:

```js
const textScaleOrder = ['default', 'large', 'xlarge'];

function cycleTextScale() {
    const next = textScaleOrder[(textScaleOrder.indexOf(preferences.textScale) + 1) % textScaleOrder.length];
    preferences.setTextScale(next);
}
```

- [ ] **Step 6: Traduzir**

Acrescentar ao objeto `"preferences"` em `pt-BR.json`:

```json
    "toggle_text_scale": "Alternar o tamanho do texto",
    "text_scale_default": "Texto padrão",
    "text_scale_large": "Texto grande",
    "text_scale_xlarge": "Texto muito grande"
```

Em `en.json`:

```json
    "toggle_text_scale": "Change the text size",
    "text_scale_default": "Default text",
    "text_scale_large": "Large text",
    "text_scale_xlarge": "Extra large text"
```

- [ ] **Step 7: Rodar os testes e verificar no navegador**

Run: `npm run test` → PASS.
Run: `npm run dev` — clicar no controle de texto até "Texto muito grande" e confirmar em `/people` que a tabela, a navegação e os formulários crescem sem quebrar o layout nem gerar rolagem horizontal.

- [ ] **Step 8: Commit**

```bash
git add resources/js/stores resources/js/components/navigation/AppTopbar.vue resources/css/app.css resources/js/i18n
git commit -m "feat: let users pick text size and layout density"
```

---

## Verificação final do plano

- [ ] Run: `php artisan test` → tudo PASS
- [ ] Run: `npm run test` → tudo PASS
- [ ] Run: `npm run build` → sem erro
- [ ] Run: `vendor/bin/pint --test` → sem diferença
- [ ] Run: `grep -rn "abort_unless\|->get()->all()" app/Modules/` → sem saída
- [ ] Run: `grep -rn "fallback_person\|fonts.googleapis.com" resources/` → sem saída
- [ ] Atualizar `docs/MIGRACAO-PADROES-LEGADO-E-NOVO.md` §7 marcando as lacunas resolvidas, e commitar com `docs: mark foundation gaps as resolved`

## Depois deste plano

Com o contrato firme, cada domínio ainda no legado vira um plano curto e repetitivo (migration + model com `BelongsToTenant` + Actions + `IndexRequest` + `Resource` + `Policy` + páginas SPA + testes). Ordem sugerida, por valor: **campos customizados** (`person_custom`/`family_custom` — bloqueia paridade de dados) → Escola dominical → Voluntariado → Carrinho/seleção → Query/relatórios → eDrive → Fundraiser → GDPR.
