# Architecture-Security Plan: Criar, listar, detalhar e resgatar investimentos

**Branch**: `feat/investment-api` (`003-investment-api`) | **Date**: 2026-09-08 | **Spec**: [spec.md](./spec.md)

**Agent**: Architecture / Security Agent
**State**: ARCHITECTURE_SECURITY_PLAN_READY
**Implementation**: this agent MUST NOT implement the solution

**Input**: Feature specification from `/specs/003-investment-api/spec.md`

## Architecture

```text
HTTP JSON + Bearer
        ↓
routes/api.php  (auth:sanctum + throttle:investments)
        ↓
Form Request (allowlist) + InvestmentPolicy / visibleTo
        ↓
Index|Show|Store|WithdrawInvestmentController  (sem regra)
        ↓
List|Show|Create|WithdrawInvestment  (application)
        ↓
Investment Eloquent  ←→  InvestmentValuation (domínio 002)
        ↓
investments table (amount_cents, created_on, withdrawn_on, user_id)
```

**Boundaries**:
- **HTTP**: status codes, Scribe, Resource JSON, validação de formato.
- **Application**: dono = `auth()->id()`, lock no resgate, chamar valuation, nunca aceitar saldo/IR do cliente.
- **Domain**: `Money`, aniversário civil, composto, IR — **não reimplementar** nos controllers.
- **Authz**: `InvestmentPolicy` + scope `visibleTo` (fail-closed se não Admin).

**Não fazer**: SPA, Repository, Strategy de papéis, `GET /users/{id}`, edição/delete, saque parcial, persistir `expected_balance`.

## SOLID

| Princípio | Aplicação |
|-----------|-----------|
| S | Um invocável por operação; um service por caso de uso; Policy só authz; Resource só serializa; Valuation só calcula |
| O | Nova alíquota continua no domínio; novo papel exigiria enum + Policy, não os services de cálculo |
| L | Policy não trata string solta como Admin; `isAdmin()` fail-closed já existe |
| I | Policy com `viewAny`, `view`, `create`, `withdraw` — sem `update`/`delete` vazios como contrato de cliente |
| D | Application depende de `InvestmentValuation` concreto (uma implementação; interface extra = YAGNI) |

Controllers **sem** `amount * 1.0052` e **sem** `Investment::find($id)` sem scope.

## Design Patterns

- **Policy** (Laravel): Admin vs Owner.
- **API Resource / Form Request**: allowlist in/out.
- **Não** Strategy: papéis não são algoritmos de ganho.
- **Não** Repository: um model + scope.
- **Value Object** existente: `Money`.

## Security Architecture

**Authentication**: `auth:sanctum` nas quatro rotas. Sem token → 401. Health/login inalterados.

**Authorization**: Server-side only. `user.role` no JSON do login **não** autoriza. Owner: só `user_id` próprio. Admin: todos. Frontend permission N/A (sem SPA).

**IDOR**: `GET/POST .../investments/{id}` — Owner com ID_B → **404**. Implementação: `visibleTo($user)->findOrFail($id)`, nunca `find` global + 403. Testes obrigatórios com dois Owners.

**Validação**: Form Requests; datas civis; amount string duas casas &gt; 0; `withdrawn_on` ≤ today e ≥ `created_on` no service.

**Banco**: Eloquent bindings. FK `user_id`. Index `(user_id, created_at)`. Sem SQL concatenado.

**RLS**: SQLite sem RLS de produto; isolamento = scope + policy.

**Mass assignment**: `user_id` / `withdrawn_on` / status não fillable. Testes enviam `user_id`, `expected_balance`, `tax`, `status`, `role`.

**Injection**: Query builder / ORM.

**Privilege escalation**: Owner não lista/resgata alheio via `user_id` query. Admin não muda `user_id` na criação (sempre o autenticado).

**XSS**: JSON only.

**CSRF**: Bearer, não cookie session da API.

**Secrets**: nenhum novo. Não logar Bearer.

**Concurrency**: `lockForUpdate` no resgate. Race: no máximo um `withdrawn_on`.

**Cache / filas**: não.

**Erros**: 401 / 404 / 422 / 429; produção sem stack. Mapear `InvalidInvestmentDate` e `InvestmentAlreadyWithdrawn` → 422 com mensagem de negócio.

**Data exposure**: Resource allowlist; Owner 404 não inclui amount alheio; senha nunca no owner summary.

**Rate limit**: `throttle:investments` 60/min por user id + IP.

## API Inventory

```text
METHOD: GET
ROUTE: /api/investments
AUTHENTICATION: auth:sanctum
AUTHORIZATION: viewAny — lista já filtrada por visibleTo
INPUT: query page, per_page (max 100); ignore user_id
OUTPUT: 200 { data: Investment[], meta: { current_page, per_page, total, last_page } }
RATE LIMIT: throttle:investments 60/min
ERROR HANDLING: 401; 422 page/per_page
LOGGING: no tokens
```

```text
METHOD: POST
ROUTE: /api/investments
AUTHENTICATION: auth:sanctum
AUTHORIZATION: create (qualquer autenticado; dono = self)
INPUT: JSON { amount, created_on }; ignore user_id, status, expected_balance, tax, withdrawn_on
OUTPUT: 201 Investment
RATE LIMIT: throttle:investments 60/min
ERROR HANDLING: 401; 422 validation
LOGGING: no tokens
```

```text
METHOD: GET
ROUTE: /api/investments/{investment}
AUTHENTICATION: auth:sanctum
AUTHORIZATION: visibleTo + policy view
INPUT: path id
OUTPUT: 200 Investment (valuation as of today or freeze)
RATE LIMIT: throttle:investments 60/min
ERROR HANDLING: 401; 404 missing or IDOR
LOGGING: no tokens
```

```text
METHOD: POST
ROUTE: /api/investments/{investment}/withdraw
AUTHENTICATION: auth:sanctum
AUTHORIZATION: visibleTo + policy withdraw
INPUT: JSON { withdrawn_on }; ignore tax, net, expected_balance, rate
OUTPUT: 200 Investment withdrawn
RATE LIMIT: throttle:investments 60/min
ERROR HANDLING: 401; 404 IDOR; 422 date / already withdrawn
LOGGING: no tokens
```

Login / logout / user / health: inalterados (`001-user-auth`).

## File-by-file Analysis

```text
FILE: routes/api.php

RESPONSIBILITY:
Registrar rotas HTTP da API.

CURRENT_PROBLEM:
Só health + auth. Sem investimentos (FR-001).

PROPOSED_CHANGE:
No grupo auth:sanctum existente (L13–16), acrescentar:
GET/POST investments, GET investments/{investment},
POST investments/{investment}/withdraw, middleware throttle:investments.
Não mover health para dentro do grupo.

ARCHITECTURE:
Única entrada HTTP desta feature.

SOLID:
S — roteamento só.

DESIGN_PATTERN:
Nenhum.

SECURITY:
auth:sanctum em todas as novas rotas. Sem rota pública de investimento.

DATA_ACCESS:
Nenhum.

AUTHORIZATION:
Delegada a Policy/scope nos controllers/services.

INPUT:
N/A no arquivo de rotas.

OUTPUT:
N/A.

DEPENDENCIES:
Novos invocáveis.

LINES:
L3–6 imports; L13–16 grupo — inserir 4 rotas.

TESTS:
Feature 401 sem token em cada verbo.

RISKS:
Esquecer throttle ou deixar {investment} fora do grupo.
```

```text
FILE: app/Models/User.php

RESPONSIBILITY:
Identidade e papel.

CURRENT_PROBLEM:
Sem relação com investimentos (L14–44).

PROPOSED_CHANGE:
Adicionar investments(): HasMany. Não alterar Fillable. Não tornar role fillable.

ARCHITECTURE:
Dono do Investment.

SOLID:
S — relação, não cálculo de IR.

DESIGN_PATTERN:
Eloquent relation.

SECURITY:
role permanece fora de Fillable (L14).

DATA_ACCESS:
hasMany investments.

AUTHORIZATION:
isAdmin/isOwner inalterados (L35–43) — Policy usa isso.

INPUT:
N/A.

OUTPUT:
N/A.

DEPENDENCIES:
App\Models\Investment.

LINES:
Após L43: método investments().

TESTS:
Factory cria investimento associado; mass assignment role ainda falha (regressão UserTest).

RISKS:
Eager load N+1 na lista — ListInvestments deve with('user').
```

```text
FILE: app/Models/Investment.php

RESPONSIBILITY:
Persistência e scope de visibilidade. Sem fórmula 0,52%.

CURRENT_PROBLEM:
Arquivo novo.

PROPOSED_CHANGE:
BelongsTo user; casts dates; scopeVisibleTo; helpers status/principal; sem fillable de user_id/withdrawn_on.

ARCHITECTURE:
Adapter de persistência para o application layer.

SOLID:
S — não chamar Valuation no model (evitar “god model”).

DESIGN_PATTERN:
Active Record + query scope (não Repository).

SECURITY:
visibleTo fail-closed; atributos setados pelo service.

DATA_ACCESS:
tabela investments.

AUTHORIZATION:
scope + Policy.

INPUT:
Só via service.

OUTPUT:
Atributos persistidos; Resource serializa.

DEPENDENCIES:
User, Carbon date casts.

LINES:
Novo arquivo (~80 linhas).

TESTS:
Unit/Feature: Admin vê todos; Owner só os seus no scope.

RISKS:
Esquecer scope no show/withdraw.
```

```text
FILE: database/migrations/xxxx_create_investments_table.php

RESPONSIBILITY:
Schema.

CURRENT_PROBLEM:
Tabela inexistente.

PROPOSED_CHANGE:
user_id FK cascade; amount_cents unsignedInt; created_on date; withdrawn_on date nullable;
index user_id; timestamps.

ARCHITECTURE:
Storage.

SOLID:
N/A.

DESIGN_PATTERN:
Nenhum.

SECURITY:
FK impede órfãos; amount_cents inteiro.

DATA_ACCESS:
DDL.

AUTHORIZATION:
N/A.

INPUT:
N/A.

OUTPUT:
N/A.

DEPENDENCIES:
users table.

LINES:
Novo.

TESTS:
RefreshDatabase nas Feature.

RISKS:
Usar decimal float; omitir FK.
```

```text
FILE: database/factories/InvestmentFactory.php

RESPONSIBILITY:
Dados de teste.

CURRENT_PROBLEM:
Novo.

PROPOSED_CHANGE:
Defaults seguros; states withdrawn / forUser. Nunca role no investimento.

ARCHITECTURE:
Testes.

SOLID:
S.

DESIGN_PATTERN:
Factory.

SECURITY:
Não gerar expected_balance persistido.

DATA_ACCESS:
create().

AUTHORIZATION:
N/A.

INPUT:
N/A.

OUTPUT:
Models de teste.

DEPENDENCIES:
UserFactory.

LINES:
Novo.

TESTS:
Usado pelas Feature.

RISKS:
created_on futuro no default.
```

```text
FILE: app/Policies/InvestmentPolicy.php

RESPONSIBILITY:
viewAny, view, create, withdraw.

CURRENT_PROBLEM:
Novo. 001 deixou policies para este PR.

PROPOSED_CHANGE:
viewAny: autenticado (filtro é o scope).
view/withdraw: isAdmin() OR user_id === owner.
create: autenticado.
Sem update/delete públicos.

ARCHITECTURE:
Authorization boundary.

SOLID:
S.

DESIGN_PATTERN:
Policy.

SECURITY:
Fail-closed: só isAdmin() promove. Owner nunca true no alheio.

DATA_ACCESS:
Lê user_id do model já carregado (após visibleTo, Owner alheio nem chega aqui).

AUTHORIZATION:
Este arquivo.

INPUT:
User + Investment.

OUTPUT:
bool.

DEPENDENCIES:
User::isAdmin.

LINES:
Novo.

TESTS:
InvestmentAuthorizationTest.

RISKS:
viewAny retornar só Admin — quebraria lista do Owner.
```

```text
FILE: app/Http/Requests/Api/StoreInvestmentRequest.php

RESPONSIBILITY:
Allowlist amount + created_on.

CURRENT_PROBLEM:
Novo.

PROPOSED_CHANGE:
authorize true (auth já no middleware). rules amount regex duas casas, not 0.00; created_on Y-m-d before_or_equal today.

ARCHITECTURE:
Input boundary.

SOLID:
S.

DESIGN_PATTERN:
Form Request.

SECURITY:
validated() sem user_id.

DATA_ACCESS:
Nenhum.

AUTHORIZATION:
Middleware.

INPUT:
JSON.

OUTPUT:
validated array.

DEPENDENCIES:
FormRequest.

LINES:
Novo.

TESTS:
422 zero/negativo/futuro; mass assignment ignorado.

RISKS:
decimal:2 em float JSON.
```

```text
FILE: app/Http/Requests/Api/IndexInvestmentRequest.php

RESPONSIBILITY:
page / per_page.

CURRENT_PROBLEM:
Novo.

PROPOSED_CHANGE:
page min 1; per_page 1–100. Sem rule user_id.

ARCHITECTURE:
Input.

SOLID:
S.

DESIGN_PATTERN:
Form Request.

SECURITY:
Owner não amplia por query.

DATA_ACCESS:
Nenhum.

AUTHORIZATION:
N/A.

INPUT:
Query.

OUTPUT:
validated.

DEPENDENCIES:
FormRequest.

LINES:
Novo.

TESTS:
per_page 101 → 422; user_id query não muda total do Owner.

RISKS:
Nenhum.
```

```text
FILE: app/Http/Requests/Api/WithdrawInvestmentRequest.php

RESPONSIBILITY:
Allowlist withdrawn_on.

CURRENT_PROBLEM:
Novo.

PROPOSED_CHANGE:
withdrawn_on required date Y-m-d before_or_equal today. Sem tax/net.

ARCHITECTURE:
Input.

SOLID:
S.

DESIGN_PATTERN:
Form Request.

SECURITY:
Cliente não define IR.

DATA_ACCESS:
Nenhum.

AUTHORIZATION:
N/A neste request (policy no service/controller).

INPUT:
JSON.

OUTPUT:
validated.

DEPENDENCIES:
FormRequest.

LINES:
Novo.

TESTS:
tax no body ignorado; data futura 422.

RISKS:
Validar ≥ created_on aqui sem o model — deixar no service.
```

```text
FILE: app/Http/Resources/InvestmentResource.php

RESPONSIBILITY:
Allowlist JSON + owner summary (UserResource sem senha).

CURRENT_PROBLEM:
Novo.

PROPOSED_CHANGE:
id, owner (id,name,email), amount decimal string, created_on, status, withdrawn_on,
expected_balance, gain, tax, net, rate, complete_months.
Valuation injetada pelo service (array) — Resource não calcula 0,52%.

ARCHITECTURE:
Output boundary.

SOLID:
S — serialização.

DESIGN_PATTERN:
API Resource.

SECURITY:
Sem password; sem campos internos.

DATA_ACCESS:
Read model + valuation DTO.

AUTHORIZATION:
N/A.

INPUT:
Investment + valuation.

OUTPUT:
JSON.

DEPENDENCIES:
UserResource ou subset.

LINES:
Novo.

TESTS:
Show/list assertions; password absent.

RISKS:
Calcular no Resource (proibido).
```

```text
FILE: app/Services/Investment/CreateInvestment.php

RESPONSIBILITY:
Persistir dono autenticado + Money + created_on; avaliar asOf=created_on.

CURRENT_PROBLEM:
Novo.

PROPOSED_CHANGE:
Money::fromDecimalString; user_id = actor->id; save; return model+valuation.
Nunca request->all().

ARCHITECTURE:
Application.

SOLID:
S.

DESIGN_PATTERN:
Application service.

SECURITY:
Dono forçado.

DATA_ACCESS:
Investment::query()->create atributos explícitos.

AUTHORIZATION:
create policy no controller before handle.

INPUT:
User, amount string, created_on.

OUTPUT:
Investment + valuation.

DEPENDENCIES:
Money, InvestmentValuation.

LINES:
Novo.

TESTS:
CreateInvestmentTest; user_id no body ignorado.

RISKS:
Aceitar amount float.
```

```text
FILE: app/Services/Investment/ListInvestments.php

RESPONSIBILITY:
visibleTo + with(user) + order + paginate + valuation por item.

CURRENT_PROBLEM:
Novo.

PROPOSED_CHANGE:
Não filtrar por request user_id. per_page capped.

ARCHITECTURE:
Application.

SOLID:
S.

DESIGN_PATTERN:
Application service.

SECURITY:
Scope antes de paginar.

DATA_ACCESS:
Eloquent paginator.

AUTHORIZATION:
visibleTo.

INPUT:
User, page, per_page.

OUTPUT:
LengthAwarePaginator of valued investments.

DEPENDENCIES:
InvestmentValuation, Carbon::today().

LINES:
Novo.

TESTS:
ListInvestmentsTest paginação e isolamento.

RISKS:
N+1; valuation pesada — ok no desafio.
```

```text
FILE: app/Services/Investment/ShowInvestment.php

RESPONSIBILITY:
visibleTo findOrFail + valuation.

CURRENT_PROBLEM:
Novo.

PROPOSED_CHANGE:
findOrFail no scope. asOf today; withdrawnOn do model.

ARCHITECTURE:
Application.

SOLID:
S.

DESIGN_PATTERN:
Application service.

SECURITY:
404 IDOR.

DATA_ACCESS:
scoped find.

AUTHORIZATION:
scope + authorize view.

INPUT:
User, id.

OUTPUT:
Investment + valuation.

DEPENDENCIES:
InvestmentValuation.

LINES:
Novo.

TESTS:
Show + IDOR 404 + Admin 200.

RISKS:
find($id) global.
```

```text
FILE: app/Services/Investment/WithdrawInvestment.php

RESPONSIBILITY:
Lock, validar data, valuation, persistir withdrawn_on uma vez.

CURRENT_PROBLEM:
Novo.

PROPOSED_CHANGE:
transaction + lockForUpdate; se withdrawn_on set → InvestmentAlreadyWithdrawn;
se withdrawn_on < created_on → InvalidInvestmentDate;
evaluate; save withdrawn_on; return.

ARCHITECTURE:
Application. Único writer de withdrawn_on.

SOLID:
S.

DESIGN_PATTERN:
Application service.

SECURITY:
IDOR via scope antes do lock. Mass assignment: só withdrawn_on do validated.

DATA_ACCESS:
lockForUpdate.

AUTHORIZATION:
withdraw policy.

INPUT:
User, id, withdrawn_on.

OUTPUT:
Investment + valuation frozen.

DEPENDENCIES:
DB, InvestmentValuation.

LINES:
Novo.

TESTS:
Withdraw tests; segundo withdraw 422; IDOR; lock coberto por teste sequencial (paralelo opcional).

RISKS:
Lock omitido; persistir tax do cliente.
```

```text
FILE: app/Http/Controllers/Api/StoreInvestmentController.php

RESPONSIBILITY:
HTTP 201 + Scribe.

CURRENT_PROBLEM:
Novo.

PROPOSED_CHANGE:
authorize create; this->createInvestment->handle; Resource.

ARCHITECTURE:
HTTP adapter.

SOLID:
S — sem regra.

DESIGN_PATTERN:
Invocable controller.

SECURITY:
@authenticated. Sem all().

DATA_ACCESS:
Via service.

AUTHORIZATION:
$this->authorize('create', Investment::class).

INPUT:
StoreInvestmentRequest.

OUTPUT:
201 JSON.

DEPENDENCIES:
CreateInvestment, InvestmentResource.

LINES:
Novo (~40) com docblock Scribe @group Investments.

TESTS:
Feature create.

RISKS:
Regra no controller.
```

```text
FILE: app/Http/Controllers/Api/IndexInvestmentController.php

RESPONSIBILITY:
HTTP 200 página.

CURRENT_PROBLEM:
Novo.

PROPOSED_CHANGE:
authorize viewAny; ListInvestments; Resource collection.

ARCHITECTURE:
HTTP adapter.

SOLID:
S.

DESIGN_PATTERN:
Invocable.

SECURITY:
@authenticated.

DATA_ACCESS:
Service.

AUTHORIZATION:
viewAny.

INPUT:
IndexInvestmentRequest.

OUTPUT:
200 paginado.

DEPENDENCIES:
ListInvestments.

LINES:
Novo.

TESTS:
List feature.

RISKS:
Nenhum.
```

```text
FILE: app/Http/Controllers/Api/ShowInvestmentController.php

RESPONSIBILITY:
HTTP 200/404.

CURRENT_PROBLEM:
Novo.

PROPOSED_CHANGE:
ShowInvestment (já scoped); authorize view no model.

ARCHITECTURE:
HTTP adapter.

SOLID:
S.

DESIGN_PATTERN:
Invocable.

SECURITY:
404 IDOR.

DATA_ACCESS:
Service.

AUTHORIZATION:
view.

INPUT:
id.

OUTPUT:
200 Resource.

DEPENDENCIES:
ShowInvestment.

LINES:
Novo.

TESTS:
Show + IDOR.

RISKS:
Route model binding global (desligar implicit binding; carregar no service).
```

```text
FILE: app/Http/Controllers/Api/WithdrawInvestmentController.php

RESPONSIBILITY:
HTTP 200/404/422.

CURRENT_PROBLEM:
Novo.

PROPOSED_CHANGE:
WithdrawInvestment; sem aceitar tax.

ARCHITECTURE:
HTTP adapter.

SOLID:
S.

DESIGN_PATTERN:
Invocable.

SECURITY:
IDOR 404; already withdrawn 422.

DATA_ACCESS:
Service.

AUTHORIZATION:
withdraw.

INPUT:
WithdrawInvestmentRequest + id.

OUTPUT:
200 Resource.

DEPENDENCIES:
WithdrawInvestment.

LINES:
Novo.

TESTS:
Withdraw feature.

RISKS:
Binding sem scope.
```

```text
FILE: app/Domain/Investment/InvestmentAlreadyWithdrawn.php

RESPONSIBILITY:
Exceção de invariante.

CURRENT_PROBLEM:
Novo.

PROPOSED_CHANGE:
DomainException mensagem "This investment has already been withdrawn."

ARCHITECTURE:
Domain.

SOLID:
S.

DESIGN_PATTERN:
Nenhum.

SECURITY:
Sem stack na resposta (handler).

DATA_ACCESS:
Nenhum.

AUTHORIZATION:
N/A.

INPUT:
N/A.

OUTPUT:
Exception.

DEPENDENCIES:
DomainException.

LINES:
Novo (~12).

TESTS:
Withdraw second time.

RISKS:
Vazar SQL na mensagem — não.
```

```text
FILE: bootstrap/app.php

RESPONSIBILITY:
Kernel HTTP / exceptions.

CURRENT_PROBLEM:
JSON em api/* já existe (L19–21). Exceções de domínio de investimento não mapeadas.

PROPOSED_CHANGE:
withExceptions: InvalidInvestmentDate e InvestmentAlreadyWithdrawn → 422 { message }.
Não alterar shouldRenderJsonWhen.

ARCHITECTURE:
Error handling.

SOLID:
S.

DESIGN_PATTERN:
Nenhum.

SECURITY:
Sem debug extras.

DATA_ACCESS:
Nenhum.

AUTHORIZATION:
N/A.

INPUT:
N/A.

OUTPUT:
JSON 422.

DEPENDENCIES:
Exceções de domínio.

LINES:
L18–21 bloco withExceptions — acrescentar renderables.

TESTS:
Feature 422 already withdrawn.

RISKS:
Mapear 500.
```

```text
FILE: app/Providers/AppServiceProvider.php

RESPONSIBILITY:
Rate limiters.

CURRENT_PROBLEM:
Só limiter login (L25–29).

PROPOSED_CHANGE:
RateLimiter::for('investments', 60/min by user id|ip). Não alterar login.

ARCHITECTURE:
Infra.

SOLID:
S.

DESIGN_PATTERN:
Nenhum.

SECURITY:
Abuse control.

DATA_ACCESS:
Nenhum.

AUTHORIZATION:
N/A.

INPUT:
Request.

OUTPUT:
Limit.

DEPENDENCIES:
RateLimiter.

LINES:
L25–29 — novo for('investments') após login.

TESTS:
Opcional 429; não obrigatório se 60 for alto para o desafio — ainda registrar o limiter.

RISKS:
Throttle no login por engano.
```

```text
FILE: tests/Feature/Api/Investments/*.php

RESPONSIBILITY:
Contrato + segurança ANTES do código (Test Agent).

CURRENT_PROBLEM:
Pasta inexistente.

PROPOSED_CHANGE:
Ver Required Tests.

ARCHITECTURE:
Testes.

SOLID:
N/A.

DESIGN_PATTERN:
N/A.

SECURITY:
IDOR, mass assignment, 401, privilege.

DATA_ACCESS:
RefreshDatabase.

AUTHORIZATION:
Dois Owners + Admin.

INPUT:
HTTP.

OUTPUT:
Assertions.

DEPENDENCIES:
Sanctum actingAs / token.

LINES:
Novos arquivos.

TESTS:
Estes são os testes.

RISKS:
Usar actingAs sem forgetGuards se logout misturado — investments não revogam token.
```

```text
FILE: app/Domain/Investment/Money.php (e demais domínio)

RESPONSIBILITY:
Cálculo já entregue.

CURRENT_PROBLEM:
Nenhum para esta feature.

PROPOSED_CHANGE:
**Não alterar** fórmulas. Apenas chamar de application services.

ARCHITECTURE:
Domain intacto.

SOLID:
Mantido.

DESIGN_PATTERN:
VO existente.

SECURITY:
Ainda rejeita centavos negativos.

DATA_ACCESS:
Nenhum.

AUTHORIZATION:
N/A.

INPUT:
Cents / datas.

OUTPUT:
Valuation.

DEPENDENCIES:
Existentes.

LINES:
Nenhuma alteração planejada.

TESTS:
Regressão php artisan test tests/Unit/Domain.

RISKS:
Copiar 0,52% no Resource.
```

```text
FILE: public/docs (Scribe)

RESPONSIBILITY:
Docs estáticas da API.

CURRENT_PROBLEM:
Sem grupo Investments.

PROPOSED_CHANGE:
php artisan scribe:generate após controllers anotados; commit public/docs.

ARCHITECTURE:
Documentação (FR-016).

SOLID:
N/A.

DESIGN_PATTERN:
N/A.

SECURITY:
Não documentar tokens reais; Bearer scheme já no scribe.

DATA_ACCESS:
N/A.

AUTHORIZATION:
Documentar Admin vs Owner em descrição.

INPUT:
N/A.

OUTPUT:
HTML/OpenAPI gerado.

DEPENDENCIES:
Scribe.

LINES:
Gerados.

TESTS:
Quickstart composer docs.

RISKS:
PR sem docs.
```

## Line-by-line Analysis

```text
FILE: routes/api.php

LINE 13-16:
Problema:
Grupo sanctum só user/logout.
Alteração:
Incluir as quatro rotas de investment com throttle:investments.
Motivo:
FR-001 autenticado.
Pattern:
Nenhum.
SOLID:
S.
SECURITY:
Nada de investimento público.
TEST:
401 sem Bearer.

LINE 9:
Problema:
Nenhum.
Alteração:
Não mover health para sanctum.
Motivo:
Health público.
SECURITY:
Mantido.
TEST:
HealthTest regressão.
```

```text
FILE: app/Models/User.php

LINE 14:
Problema:
Nenhum (Fillable correto).
Alteração:
Não adicionar user_id/role/investments fields fillable.
Motivo:
Mass assignment.
SECURITY:
role continua protegido.
TEST:
UserTest fillable regressão.

LINE 35-43:
Problema:
Nenhum.
Alteração:
Policy chama isAdmin(); não duplicar comparação de string.
Motivo:
Fail-closed já testado.
TEST:
Privilege existente + IDOR investments.
```

```text
FILE: bootstrap/app.php

LINE 19-21:
Problema:
Exceções de investimento cairiam em 500 JSON genérico.
Alteração:
Renderable 422 para InvalidInvestmentDate e InvestmentAlreadyWithdrawn.
Motivo:
FR-014 mensagens de negócio.
Pattern:
Nenhum.
SOLID:
S.
SECURITY:
Sem stack.
TEST:
Withdraw 422.
```

```text
FILE: app/Providers/AppServiceProvider.php

LINE 25-29:
Problema:
Sem limiter de investments.
Alteração:
Novo RateLimiter::for('investments') sem mudar o closure login.
Motivo:
Flood create/withdraw.
SECURITY:
60/min por id|ip.
TEST:
Não quebrar LoginRateLimitTest.
```

```text
FILE: app/Http/Controllers/Api/StoreInvestmentController.php

LINES 1-XX:
Responsabilidade:
201 create.
Dependencies:
StoreInvestmentRequest, CreateInvestment, InvestmentResource.
Security:
Ignorar extra fields; dono = auth.
Tests:
CreateInvestmentTest, InvestmentMassAssignmentTest.
```

```text
FILE: app/Http/Controllers/Api/IndexInvestmentController.php

LINES 1-XX:
Responsabilidade:
Lista paginada.
Dependencies:
IndexInvestmentRequest, ListInvestments.
Security:
visibleTo; ignore user_id query.
Tests:
ListInvestmentsTest.
```

```text
FILE: app/Http/Controllers/Api/ShowInvestmentController.php

LINES 1-XX:
Responsabilidade:
Detalhe.
Dependencies:
ShowInvestment.
Security:
404 IDOR.
Tests:
ShowInvestmentTest, InvestmentAuthorizationTest.
```

```text
FILE: app/Http/Controllers/Api/WithdrawInvestmentController.php

LINES 1-XX:
Responsabilidade:
Resgate integral.
Dependencies:
WithdrawInvestmentRequest, WithdrawInvestment.
Security:
lock no service; tax do body ignorado.
Tests:
WithdrawInvestmentTest.
```

```text
FILE: app/Policies/InvestmentPolicy.php

LINES 1-XX:
Responsabilidade:
viewAny/view/create/withdraw.
Dependencies:
User::isAdmin.
Security:
Owner só próprio; Admin todos; fail-closed.
Tests:
InvestmentAuthorizationTest.
```

```text
FILE: app/Models/Investment.php

LINES 1-XX:
Responsabilidade:
Persistência + visibleTo.
Dependencies:
User, date casts.
Security:
Sem fillable de controle; scope fail-closed.
Tests:
Scope coberto nas Feature.
```

```text
FILE: app/Services/Investment/WithdrawInvestment.php

LINES 1-XX:
Responsabilidade:
Transação + lock + valuation + persist withdrawn_on.
Dependencies:
DB, InvestmentValuation, InvestmentAlreadyWithdrawn.
Security:
Race; IDOR via scope.
Tests:
Double withdraw; freeze after; IDOR.
```

```text
FILE: app/Domain/Investment/InvestmentAlreadyWithdrawn.php

LINES 1-XX:
Responsabilidade:
Invariante “um resgate”.
Dependencies:
DomainException.
Security:
Mensagem sem internals.
Tests:
Segundo POST 422.
```

## Required Tests

**Antes do Code Agent (FAIL first):**

- `StoreInvestmentTest`: 201 amount/owner/status active; 401; 422 zero/future; extra user_id/expected_balance ignorados.
- `ListInvestmentsTest`: Owner só os seus; Admin vê os dois donos; per_page/page; user_id query não amplia Owner; 401.
- `ShowInvestmentTest`: 1 mês civil → 1005.20 com `setTestNow`; freeze após withdraw; 401.
- `WithdrawInvestmentTest`: 422 already withdrawn; 422 date before created_on / future; tax body ignorado; freeze no GET posterior.
- `InvestmentAuthorizationTest`: Owner GET/withdraw ID_B → 404 sem amount; Admin GET/withdraw ID do Owner → 200; ID inexistente 404.
- `InvestmentUnauthenticatedTest`: quatro rotas 401.
- `InvestmentMassAssignmentTest`: create/withdraw com status, withdrawn_on (no create), role, tax.

**Regressão:** `php artisan test` (Auth, Health, Domain).

**Não:** testes HTTP de XSS HTML; CSRF cookie (Bearer). Injection coberta por ORM + assertions de isolamento.

## Remaining Risks

- SQLite `lockForUpdate` é advisory por conexão; testes paralelos reais de race são limitados — invariante coberta por lock + segundo request sequencial.
- `APP_TIMEZONE` UTC vs “hoje” local do avaliador: documentar no quickstart; testes usam `setTestNow`.
- Exemplo README 1200/45 não aparece no HTTP real (research §5); risco de avaliador procurar 1155 na API — docs/Scribe devem dizer que 1200 é exemplo de IR, saldo HTTP é composto.
- Lista Admin sem filtro por dono: páginas misturam pessoas (spec).
- Sem SPA: avaliador só via HTTP/docs nesta etapa.
