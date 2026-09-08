# Architecture-Security Plan: Autenticação e papéis Admin / Owner

**Branch**: `feat/auth` (`001-user-auth`) | **Date**: 2026-09-08 | **Spec**: [spec.md](./spec.md)

**Agent**: Architecture / Security Agent
**State**: ARCHITECTURE_SECURITY_PLAN_READY
**Implementation**: this agent MUST NOT implement the solution

**Input**: Feature specification from `/specs/001-user-auth/spec.md`

## Architecture

```text
HTTP JSON
    ↓
routes/api.php
    ↓
LoginRequest (só login) | auth:sanctum (me, logout)
    ↓
LoginController | CurrentUserController | LogoutController
    ↓
LoginUser (application)          Auth::user()
    ↓
User (Eloquent + HasApiTokens) + UserRole enum
    ↓
users.role (não fillable) | personal_access_tokens
```

**Boundaries**:
- **HTTP**: validação de formato, status codes, Resource JSON.
- **Application**: `LoginUser` — comparar credenciais sem enumerar contas; emitir token; nunca gravar `role` a partir do request.
- **Domain-lite**: enum + `isAdmin()` / `isOwner()` fail-closed. Policies de investimento **não** entram neste PR.
- **Infra**: Sanctum tokens, RateLimiter, seeder, env.

**Não fazer**: SPA, cadastro, `GET /users/{id}`, CRUD de investimento, Strategy de papéis, Repository.

Health (`GET /api/health`) permanece **fora** de `auth:sanctum`.

## SOLID

| Princípio | Aplicação |
|-----------|-----------|
| S | Um invocável por verbo de auth; `LoginUser` só autentica+emite; `UserResource` só serializa; enum só valores de papel |
| O | Novos papéis (se houver no futuro) entram no enum + policies de investimento, sem reescrever login |
| L | `UserRole` backed enum; código não assume string livre = admin |
| I | Sem interface gorda; Sanctum `HasApiTokens` é o contrato de token |
| D | Controller depende de `LoginUser` (classe concreta pequena; interface extra não se justifica com uma implementação) |

Controllers **sem** regra de negócio: sem `Hash::check` no controller.

## Design Patterns

- **Não** Strategy: Admin/Owner não são algoritmos de login.
- **Não** Repository: um model, queries triviais.
- **Factory** (Laravel): states `admin()` / `owner()` — já existe `UserFactory`.
- **API Resource**: allowlist de saída (`id`, `name`, `email`, `role`).
- **Form Request**: allowlist de entrada no login.

## Security Architecture

**Authentication**: Sanctum Bearer. Login público + throttle. Me/logout exigem token válido. Token expirado/adulterado = 401.

**Authorization**: Neste PR não há `{id}` de recurso. `GET /api/user` devolve **somente** `auth()->user()`. Extra `user_id` / `role` no query/body é ignorado. Autenticado ≠ autorizado sobre outra pessoa (FR-008). Policies de Investment ficam para specs seguintes; `User::isAdmin()` já existe para elas.

**Validação**: `LoginRequest` — email/password. Campos extras ignorados (`$request->validated()` só esses dois).

**Banco**: Eloquent/bindings. Sem SQL concatenado. `role` default `owner`. Unique `email`.

**Secrets**: Senhas hashed (`casts password => hashed`). Seed passwords via env, defaults só em `.env.example`. Não commitar `.env`. Token aparece **uma vez** no JSON de login (necessário para o cliente); não logar o token. `SCRIBE_AUTH_KEY` já no scribe config — não colocar valor real no git.

**APIs**: Ver inventário. JSON errors em `api/*` já em `bootstrap/app.php` L19–21.

**IDOR**: Sem rota `/user/{id}`. Teste de regressão: Owner autenticado envia `user_id` de Admin e continua vendo a si.

**XSS**: API JSON; Resource escapa por encoding JSON. Sem Blade nesta feature.

**CSRF**: Token Bearer (não cookie session para API). SPA stateful Sanctum **não** habilitada agora.

**Mass assignment**: `Fillable` permanece `name, email, password`. Login não chama `User::create($request->all())`. Role só factory/seeder (`forceFill` ou state).

**Injection**: Query `where('email', $email)` bound.

**Privilege escalation**: Cliente não promove. Teste envia `role=admin` no login e no GET; DB inalterado.

**Race / locking**: Login concorrente pode emitir dois tokens — aceitável (duas sessões). Unique email impede duas contas. Sem saque nesta feature. Sem cache de permissão.

**Filas**: N/A.

**Observabilidade**: Não logar senha nem token. Falha de login: log nível info sem e-mail+senha em claro (e-mail ok).

**Erros**: 401 mensagem única; 422 validação; 429 throttle; produção sem stack (`APP_DEBUG=false`).

**Frontend permission**: Sem UI. `user.role` no JSON é informativo; autorização futura no servidor.

## API Inventory

```text
METHOD: GET
ROUTE: /api/health
AUTHENTICATION: none
AUTHORIZATION: public
INPUT: none
OUTPUT: {"status":"ok"}
RATE LIMIT: default API (sem alteração)
ERROR HANDLING: n/a success path
LOGGING: none
```

```text
METHOD: POST
ROUTE: /api/login
AUTHENTICATION: none (issues token)
AUTHORIZATION: none (identity proof is password)
INPUT: JSON { email, password }; ignore role, user_id, is_admin
OUTPUT: 200 { token, user: { id, name, email, role } } — never password
RATE LIMIT: throttle:login — 5 / min / (ip + email)
ERROR HANDLING: 401 Invalid credentials. (both unknown email and bad password);
  422 validation; 429 too many attempts
LOGGING: do not log password or issued token
```

```text
METHOD: GET
ROUTE: /api/user
AUTHENTICATION: auth:sanctum (Bearer)
AUTHORIZATION: self only — identity from token, not from request id
INPUT: none used
OUTPUT: 200 { data: UserResource }
RATE LIMIT: default
ERROR HANDLING: 401 missing/invalid token
LOGGING: none with secrets
```

```text
METHOD: POST
ROUTE: /api/logout
AUTHENTICATION: auth:sanctum
AUTHORIZATION: revoke current token only (not all devices unless we choose current only — current only)
INPUT: none
OUTPUT: 204
RATE LIMIT: default
ERROR HANDLING: 401 if no token
LOGGING: none
```

## File-by-file Analysis

```text
FILE: composer.json

RESPONSIBILITY:
Declarar dependências PHP.

CURRENT_PROBLEM:
Sem laravel/sanctum; API não tem tokens revogáveis.

PROPOSED_CHANGE:
require laravel/sanctum (versão compatível com Laravel 13).

ARCHITECTURE:
Infra de sessão.

SOLID:
N/A.

DESIGN_PATTERN:
N/A.

SECURITY:
Pacote oficial; publicar migration de tokens; sem forks.

DATA_ACCESS:
N/A.

AUTHORIZATION:
N/A.

INPUT / OUTPUT:
N/A.

DEPENDENCIES:
composer.

LINES:
Bloco require (hoje L8–12): adicionar laravel/sanctum.

TESTS:
Feature auth usam HasApiTokens.

RISKS:
Versão pinada incompatível — resolver na instalação, não chutar major antigo.
```

```text
FILE: app/Enums/UserRole.php

RESPONSIBILITY:
Dois papéis de autorização.

CURRENT_PROBLEM:
Arquivo novo.

PROPOSED_CHANGE:
enum string Admin = 'admin', Owner = 'owner'.

ARCHITECTURE:
Domínio mínimo compartilhado com User e Resource.

SOLID:
S — só valores; O — novos cases no enum.

DESIGN_PATTERN:
Nenhum.

SECURITY:
Fail-closed: só Admin é admin.

DATA_ACCESS:
N/A.

AUTHORIZATION:
Fonte do papel.

INPUT:
Nunca do request.

OUTPUT:
value serializado no Resource.

DEPENDENCIES:
PHP 8.3 backed enum.

LINES:
Novo arquivo completo.

TESTS:
UserRoleTest; User::isAdmin com role inesperado.

RISKS:
String legado no banco — migration default owner; seeder só enum values.
```

```text
FILE: app/Models/User.php

RESPONSIBILITY:
Identidade persistida.

CURRENT_PROBLEM:
Sem role, sem HasApiTokens; Fillable correto (sem role).

PROPOSED_CHANGE:
use HasApiTokens; cast role => UserRole; métodos isAdmin/isOwner; NÃO adicionar role ao Fillable; Hidden já cobre password.

ARCHITECTURE:
Entity + token.

SOLID:
S — persistência e predicados de papel, não login HTTP.

DESIGN_PATTERN:
Active Record (Laravel).

SECURITY:
Mass assignment: role fora. Hidden password. Hash via cast.

DATA_ACCESS:
Eloquent users.

AUTHORIZATION:
Predicados para policies futuras.

INPUT:
Nenhum HTTP direto.

OUTPUT:
Via UserResource, não $user->toArray() cru no controller.

DEPENDENCIES:
Sanctum HasApiTokens, UserRole.

LINES:
Ver line-by-line.

TESTS:
Factory admin/owner; hidden password em JSON de login.

RISKS:
Alguém adicionar role ao Fillable depois — teste de mass assignment deve falhar o PR.
```

```text
FILE: database/migrations/xxxx_add_role_to_users_table.php

RESPONSIBILITY:
Persistir papel.

CURRENT_PROBLEM:
users sem role (migration 0001 L14–22).

PROPOSED_CHANGE:
Nova migration: string role default 'owner', index opcional. Não editar a migration original já aplicada.

ARCHITECTURE:
Schema.

SOLID:
N/A.

DESIGN_PATTERN:
N/A.

SECURITY:
Default owner (não admin). Sem valor vindo do HTTP.

DATA_ACCESS:
DDL.

AUTHORIZATION:
N/A.

INPUT:
N/A.

OUTPUT:
N/A.

DEPENDENCIES:
Blueprint.

LINES:
Arquivo novo.

TESTS:
RefreshDatabase + seed/factory.

RISKS:
SQLite vs MySQL: string default é suficiente; check constraint opcional.
```

```text
FILE: app/Services/Auth/LoginUser.php

RESPONSIBILITY:
Validar senha e emitir token; mensagem genérica.

CURRENT_PROBLEM:
Novo.

PROPOSED_CHANGE:
invoke(email, password): {token, user} ou exception 401. User::where('email')->first(); se null OU !Hash::check → mesma AuthenticationException. Nunca User::fill(request). createToken('api')->plainTextToken.

ARCHITECTURE:
Application service.

SOLID:
S; D — controller chama este serviço.

DESIGN_PATTERN:
Nenhum (não Strategy).

SECURITY:
Timing: aceitar diferença mínima first() vs check; não revelar no body. Não setar role.

DATA_ACCESS:
Eloquent por e-mail bound.

AUTHORIZATION:
Emissão de sessão, não acesso a terceiro.

INPUT:
email/password já validados.

OUTPUT:
token plaintext uma vez + User model.

DEPENDENCIES:
Hash, User, Sanctum.

LINES:
Novo.

TESTS:
Login feature success/fail unknown/fail wrong password same message; extra attributes ignored.

RISKS:
Log acidental do token no service — proibido.
```

```text
FILE: app/Http/Requests/Api/LoginRequest.php

RESPONSIBILITY:
Validar formato do login.

CURRENT_PROBLEM:
Novo.

PROPOSED_CHANGE:
rules email required|email, password required|string. authorize true (público).

ARCHITECTURE:
HTTP boundary.

SOLID:
S.

DESIGN_PATTERN:
Form Request.

SECURITY:
422 só formato; não “user not found”.

DATA_ACCESS:
Nenhum.

AUTHORIZATION:
Público.

INPUT:
validated() subset.

OUTPUT:
N/A.

DEPENDENCIES:
FormRequest.

LINES:
Novo.

TESTS:
422 empty/malformed.

RISKS:
Nenhum.
```

```text
FILE: app/Http/Resources/UserResource.php

RESPONSIBILITY:
JSON público da pessoa.

CURRENT_PROBLEM:
Novo.

PROPOSED_CHANGE:
toArray: id, name, email, role (enum value). Sem password, tokens, remember_token.

ARCHITECTURE:
Output encoding.

SOLID:
S.

DESIGN_PATTERN:
API Resource.

SECURITY:
Allowlist de saída (data exposure).

DATA_ACCESS:
Lê model já autorizado.

AUTHORIZATION:
Caller já autenticado (me) ou recém-logado (self).

INPUT:
N/A.

OUTPUT:
Só quatro campos.

DEPENDENCIES:
JsonResource.

LINES:
Novo.

TESTS:
Login/me JSON missing password.

RISKS:
Alguém usar $user->makeVisible — testes exact fragment.
```

```text
FILE: app/Http/Controllers/Api/LoginController.php

RESPONSIBILITY:
POST login HTTP.

CURRENT_PROBLEM:
Novo.

PROPOSED_CHANGE:
__invoke(LoginRequest, LoginUser): JsonResponse 200 token + UserResource. Sem Hash::check aqui. Anotações Scribe @unauthenticated @group Authentication.

ARCHITECTURE:
Controller fino.

SOLID:
S.

DESIGN_PATTERN:
Invokable controller.

SECURITY:
Não persiste request->all().

DATA_ACCESS:
Via LoginUser.

AUTHORIZATION:
N/A.

INPUT:
validated email/password.

OUTPUT:
token + user.

DEPENDENCIES:
LoginUser, LoginRequest, UserResource.

LINES:
Novo.

TESTS:
Feature login.

RISKS:
Esquecer @unauthenticated no Scribe.
```

```text
FILE: app/Http/Controllers/Api/CurrentUserController.php

RESPONSIBILITY:
GET perfil da sessão.

CURRENT_PROBLEM:
Novo.

PROPOSED_CHANGE:
UserResource::make($request->user()). Ignorar query/body. Não User::find($id).

ARCHITECTURE:
Controller fino.

SOLID:
S.

DESIGN_PATTERN:
Invokable.

SECURITY:
Anti-IDOR por design (sem id).

DATA_ACCESS:
Só user autenticado.

AUTHORIZATION:
self.

INPUT:
Ignorado.

OUTPUT:
UserResource.

DEPENDENCIES:
UserResource.

LINES:
Novo.

TESTS:
401; 200 self; ignore user_id query.

RISKS:
Adicionar {user} route depois sem policy.
```

```text
FILE: app/Http/Controllers/Api/LogoutController.php

RESPONSIBILITY:
Revogar token atual.

CURRENT_PROBLEM:
Novo.

PROPOSED_CHANGE:
$request->user()->currentAccessToken()->delete(); 204.

ARCHITECTURE:
Controller fino.

SOLID:
S.

DESIGN_PATTERN:
Invokable.

SECURITY:
Não apagar tokens de outro user; currentAccessToken só o Bearer enviado.

DATA_ACCESS:
personal_access_tokens do user autenticado.

AUTHORIZATION:
self session.

INPUT:
Nenhum.

OUTPUT:
vazio 204.

DEPENDENCIES:
Sanctum.

LINES:
Novo.

TESTS:
204 then 401 on me.

RISKS:
PersonalAccessToken vs TransientToken em testes — usar Sanctum::actingAs com token real ou createToken.
```

```text
FILE: routes/api.php

RESPONSIBILITY:
Rotas API.

CURRENT_PROBLEM:
Só health (L6).

PROPOSED_CHANGE:
POST login + throttle:login; middleware group auth:sanctum com POST logout e GET user.

ARCHITECTURE:
HTTP routing.

SOLID:
N/A.

DESIGN_PATTERN:
N/A.

SECURITY:
Health fora do group. Login rate limited.

DATA_ACCESS:
N/A.

AUTHORIZATION:
Middleware sanctum no group.

INPUT / OUTPUT:
N/A.

DEPENDENCIES:
Novos controllers.

LINES:
Ver line-by-line.

TESTS:
HealthTest continua verde; rotas auth.

RISKS:
Prefix /api duplicado — Laravel já prefixa api.php com /api.
```

```text
FILE: app/Providers/AppServiceProvider.php

RESPONSIBILITY:
Boot da aplicação.

CURRENT_PROBLEM:
boot() vazio (L20–23).

PROPOSED_CHANGE:
RateLimiter::for('login', fn (Request $request) => Limit::perMinute(5)->by(strtolower((string) $request->input('email')).'|'.$request->ip()));

ARCHITECTURE:
Infra anti-brute-force.

SOLID:
S — só registro do limiter.

DESIGN_PATTERN:
N/A.

SECURITY:
FR-011.

DATA_ACCESS:
Cache driver (já database no .env).

AUTHORIZATION:
N/A.

INPUT:
email + ip.

OUTPUT:
429 via middleware.

DEPENDENCIES:
RateLimiter, Limit.

LINES:
boot().

TESTS:
Feature: 6º login falho em sequência → 429 (RefreshDatabase + cache).

RISKS:
CACHE_STORE=database precisa tabela cache (já no skeleton Laravel).
```

```text
FILE: database/factories/UserFactory.php

RESPONSIBILITY:
Users de teste.

CURRENT_PROBLEM:
Sem role (definition L27–33).

PROPOSED_CHANGE:
'role' => UserRole::Owner no definition; methods admin() e owner().

ARCHITECTURE:
Test/seed data.

SOLID:
S.

DESIGN_PATTERN:
Factory.

SECURITY:
Default owner; testes admin() explícito.

DATA_ACCESS:
Inserts de teste.

AUTHORIZATION:
N/A.

INPUT:
N/A.

OUTPUT:
N/A.

DEPENDENCIES:
UserRole.

LINES:
definition + novos states.

TESTS:
Factory usado nos Feature tests.

RISKS:
Nenhum.
```

```text
FILE: database/seeders/DatabaseSeeder.php

RESPONSIBILITY:
Dados iniciais.

CURRENT_PROBLEM:
Um Test User sem papel (L20–23); não há Admin nem Owner nomeados.

PROPOSED_CHANGE:
updateOrCreate Admin e Owner com env SEED_*; factory states; remover ou substituir test@example.com.

ARCHITECTURE:
Provisionamento.

SOLID:
S.

DESIGN_PATTERN:
N/A.

SECURITY:
Senhas de env; não role no HTTP. Defaults só locais.

DATA_ACCESS:
users insert/update.

AUTHORIZATION:
Define papéis iniciais.

INPUT:
env.

OUTPUT:
N/A.

DEPENDENCIES:
User, UserRole, env.

LINES:
run() L16–24.

TESTS:
Feature seed opcional ou teste que factory+seeder criam papéis (pode ser teste que chama seeder).

RISKS:
Re-seed em produção com defaults fracos — documentar local-only no quickstart.
```

```text
FILE: .env.example

RESPONSIBILITY:
Contrato de env.

CURRENT_PROBLEM:
Sem SEED_*.

PROPOSED_CHANGE:
SEED_ADMIN_EMAIL, SEED_ADMIN_PASSWORD, SEED_OWNER_EMAIL, SEED_OWNER_PASSWORD (valores locais óbvios). Não adicionar tokens reais.

ARCHITECTURE:
Config.

SOLID:
N/A.

DESIGN_PATTERN:
N/A.

SECURITY:
Placeholders, não produção.

DATA_ACCESS:
N/A.

AUTHORIZATION:
N/A.

INPUT:
N/A.

OUTPUT:
N/A.

DEPENDENCIES:
N/A.

LINES:
Após CORS_ALLOWED_ORIGINS (~L8).

TESTS:
N/A.

RISKS:
Alguém copiar defaults para produção — Remaining Risks.
```

```text
FILE: config/scribe.php

RESPONSIBILITY:
Docs da API.

CURRENT_PROBLEM:
auth já Bearer (L106–129); extra_info já menciona Admin/Owner. default false — health ok.

PROPOSED_CHANGE:
Regenerar public/docs após controllers. Só ajustar extra_info se o texto de login/logout precisar. Preferir não mudar config se já adequado.

ARCHITECTURE:
Documentação.

SOLID:
N/A.

DESIGN_PATTERN:
N/A.

SECURITY:
use_value env SCRIBE_AUTH_KEY — não commitar chave real.

DATA_ACCESS:
N/A.

AUTHORIZATION:
Docs descrevem Bearer; não é enforcement.

INPUT / OUTPUT:
N/A.

DEPENDENCIES:
Scribe.

LINES:
Opcional extra_info L129.

TESTS:
N/A (artefato gerado).

RISKS:
Docs desatualizados se esquecer composer docs.
```

```text
FILE: public/docs/** (generated)

RESPONSIBILITY:
Contrato publicado.

CURRENT_PROBLEM:
Só health.

PROPOSED_CHANGE:
php artisan scribe:generate; commit.

ARCHITECTURE:
Constitution Scribe gate.

SECURITY:
Não embutir token real nos exemplos (placeholder {YOUR_TOKEN} já existe).

DATA_ACCESS:
N/A.

AUTHORIZATION:
N/A.

INPUT / OUTPUT:
N/A.

DEPENDENCIES:
Scribe.

LINES:
Gerados.

TESTS:
N/A.

RISKS:
Diff grande de HTML — esperado.
```

```text
FILE: tests/Feature/Api/Auth/*.php (novos)
FILE: tests/Unit/UserRoleTest.php (novo)

RESPONSIBILITY:
Testes ANTES do código (Test Agent).

CURRENT_PROBLEM:
Só HealthTest e examples.

PROPOSED_CHANGE:
Ver Required Tests. RefreshDatabase. Sanctum::actingAs ou createToken.

ARCHITECTURE:
Test-first gate.

SECURITY:
Cobre IDOR-ish, mass assignment, privilege escalation, brute force, data exposure.

DATA_ACCESS:
sqlite testing.

AUTHORIZATION:
Casos Admin vs Owner no perfil (ambos só self neste PR).

INPUT / OUTPUT:
HTTP JSON.

DEPENDENCIES:
PHPUnit.

LINES:
Novos arquivos.

TESTS:
São os testes.

RISKS:
Throttle test flaky se limiter compartilhado — usar RateLimiter::clear ou email único.
```

Não alterar `app/Http/Controllers/Api/HealthController.php` nem `tests/Feature/Api/HealthTest.php` salvo regressão.

Não alterar `bootstrap/app.php` salvo se Sanctum exigir middleware extra (API token guard padrão não exige `statefulApi()` neste PR).

## Line-by-line Analysis

### Arquivos existentes

```text
FILE: app/Models/User.php

LINE 6–11 (imports):
Problema: Sem Sanctum nem UserRole.
Alteração: use Laravel\Sanctum\HasApiTokens; use App\Enums\UserRole;
Motivo: Tokens e cast de papel.
Pattern: N/A.
SOLID: S.
SECURITY: Trait não expõe password.
TEST: Login emite token.

LINE 13:
Problema: Fillable sem role — CORRETO; não mudar para incluir role.
Alteração: Nenhuma em Fillable.
Motivo: Mass assignment / FR-007.
Pattern: Allowlist.
SOLID: N/A.
SECURITY: Cliente não define role.
TEST: User::factory()->create(['role' => ...] via create array ainda seta atributos não fillable no Eloquent create() — ATENÇÃO: Eloquent create() usa fill() então role no array seria IGNORADO. Factory definition() seta attributes direto (bypass fillable). Seeder deve usar factory state ou forceFill, NÃO User::create(['role' => admin]) se fillable omitir role.
TEST: assert Database role after factory->admin(); assert create from HTTP cannot.

LINE 14:
Problema: Hidden ok.
Alteração: Nenhuma (password já hidden).
Motivo: Data exposure.
Pattern: N/A.
SOLID: N/A.
SECURITY: Password fora de JSON acidental toArray.
TEST: assertJsonMissing password.

LINE 18:
Problema: Sem HasApiTokens.
Alteração: use HasApiTokens, HasFactory, Notifiable;
Motivo: createToken / currentAccessToken.
Pattern: N/A.
SOLID: N/A.
SECURITY: Token por user.
TEST: logout revokes.

LINE 25–30 casts():
Problema: Sem role.
Alteração: 'role' => UserRole::class junto de password hashed.
Motivo: Enum fail-closed.
Pattern: N/A.
SOLID: L — enum contract.
SECURITY: String lixo ≠ Admin.
TEST: Unit isAdmin false se role null/invalid (se possível).
```

```text
FILE: routes/api.php

LINE 3:
Problema: Só HealthController import.
Alteração: Import LoginController, LogoutController, CurrentUserController.
Motivo: Novas rotas.
Pattern: N/A.
SOLID: N/A.
SECURITY: N/A.
TEST: Feature.

LINE 6:
Problema: Só health.
Alteração: Manter Route::get('/health', ...).
  Route::post('/login', LoginController::class)->middleware('throttle:login');
  Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', CurrentUserController::class);
    Route::post('/logout', LogoutController::class);
  });
Motivo: FR-001–005; health público.
Pattern: N/A.
SOLID: N/A.
SECURITY: authn no group; throttle no login.
TEST: health unauthenticated; user 401; login 200.
```

```text
FILE: database/factories/UserFactory.php

LINE 27–33 definition():
Problema: Sem role; default viraria null após migration se omitido — default SQL owner, mas factory deve ser explícita.
Alteração: 'role' => UserRole::Owner,
Motivo: Testes determinísticos.
Pattern: Factory.
SOLID: N/A.
SECURITY: Default não-admin.
TEST: created user isOwner.

LINES após unverified() (~L39):
Problema: Sem states de papel.
Alteração: admin(): state role Admin; owner(): state role Owner.
Motivo: Seed e testes de privilege.
Pattern: Factory states.
SOLID: S.
SECURITY: Admin só quando pedido.
TEST: factory()->admin()->create()->isAdmin().
```

```text
FILE: database/seeders/DatabaseSeeder.php

LINE 20–23:
Problema: Test User único sem papel Admin/Owner nomeados; e-mail test@example.com.
Alteração: Substituir por dois updateOrCreate/factory states lendo env SEED_*.
Motivo: FR-010, SC-005.
Pattern: N/A.
SOLID: S.
SECURITY: role via state/forceFill, não request; passwords env.
TEST: After seed, login both accounts (quickstart + optional SeederTest).
```

```text
FILE: app/Providers/AppServiceProvider.php

LINE 20–23 boot():
Problema: Sem rate limiter nomeado.
Alteração: Registrar RateLimiter login 5/min by email|ip.
Motivo: FR-011.
Pattern: N/A.
SOLID: S.
SECURITY: Brute force.
TEST: 429 após 5 falhas (usar o mesmo e-mail).
```

```text
FILE: .env.example

LINE 8 (após CORS):
Problema: Sem variáveis de seed.
Alteração: Quatro SEED_* com defaults locais.
Motivo: Documentar contas; não hardcode só no PHP.
Pattern: N/A.
SOLID: N/A.
SECURITY: Placeholders; .env gitignored.
TEST: N/A.
```

```text
FILE: composer.json

LINE 8–12 require:
Problema: Sem Sanctum.
Alteração: Adicionar laravel/sanctum.
Motivo: Sessão Bearer revogável.
Pattern: N/A.
SOLID: N/A.
SECURITY: Pacote mantido.
TEST: Feature login.
```

```text
FILE: tests/Feature/Api/HealthTest.php

LINE 9–15:
Problema: Nenhum se health permanecer público.
Alteração: Nenhuma.
Motivo: Regressão: auth não pode exigir token em /health.
Pattern: N/A.
SOLID: N/A.
SECURITY: Superfície pública mínima.
TEST: Já existe; deve continuar PASS.
```

### Arquivos novos

```text
FILE: app/Enums/UserRole.php
LINES 1–20:
Responsabilidade: Cases Admin, Owner.
Dependencies: Nenhuma.
Security: Sem case default admin.
Tests: UserRoleTest.

FILE: app/Services/Auth/LoginUser.php
LINES 1–60:
Responsabilidade: Autenticar e emitir token; 401 genérico.
Dependencies: User, Hash, AuthenticationException.
Security: Não vazar existência; não aceitar role; não logar secrets.
Tests: LoginTest (success, unknown, wrong password same JSON message, ignore extra fields).

FILE: app/Http/Requests/Api/LoginRequest.php
LINES 1–30:
Responsabilidade: rules email/password.
Dependencies: FormRequest.
Security: 422 só formato.
Tests: LoginValidationTest.

FILE: app/Http/Resources/UserResource.php
LINES 1–25:
Responsabilidade: Allowlist JSON.
Dependencies: JsonResource, User.
Security: Sem password/tokens.
Tests: LoginTest / CurrentUserTest json paths.

FILE: app/Http/Controllers/Api/LoginController.php
LINES 1–40:
Responsabilidade: HTTP login + Scribe @unauthenticated.
Dependencies: LoginRequest, LoginUser, UserResource.
Security: Sem create($request->all()).
Tests: LoginTest.

FILE: app/Http/Controllers/Api/CurrentUserController.php
LINES 1–30:
Responsabilidade: Perfil da sessão; Scribe @authenticated.
Dependencies: Request, UserResource.
Security: Nunca find($request->id).
Tests: CurrentUserTest.

FILE: app/Http/Controllers/Api/LogoutController.php
LINES 1–25:
Responsabilidade: Delete current token; 204.
Dependencies: Request.
Security: Só token atual.
Tests: LogoutTest.

FILE: database/migrations/YYYY_MM_DD_HHMMSS_add_role_to_users_table.php
LINES 1–30:
Responsabilidade: users.role string default owner.
Dependencies: Migration, Blueprint.
Security: Default owner.
Tests: RefreshDatabase.

FILE: database/migrations (Sanctum personal_access_tokens)
LINES: vendor publish.
Responsabilidade: Guardar hashes de tokens.
Dependencies: Sanctum.
Security: Token plaintext só na resposta de login; DB guarda hash.
Tests: Logout invalidation.

FILE: tests/Feature/Api/Auth/LoginTest.php
LINES 1–XX:
Responsabilidade: Contratos de login e mass-assignment.
Dependencies: RefreshDatabase, User factory.
Security: Mensagem idêntica; role ignorado.
Tests: (este arquivo).

FILE: tests/Feature/Api/Auth/CurrentUserTest.php
FILE: tests/Feature/Api/Auth/LogoutTest.php
FILE: tests/Feature/Api/Auth/LoginRateLimitTest.php
FILE: tests/Unit/UserTest.php (isAdmin/isOwner)
FILE: tests/Unit/UserRoleTest.php
LINES 1–XX:
Responsabilidade: Ver Required Tests.
Dependencies: PHPUnit, Sanctum.
Security: Lista abaixo.
Tests: Gate Test Agent.
```

## Required Tests

Criar **antes** do Code Agent (`TESTS_CREATED`):

**Feature — Login**
- Credenciais válidas: 200, token não vazio, user id/name/email/role, sem password/remember_token.
- `role`, `is_admin`, `user_id` no body: 200 e `users.role` inalterado (Owner permanece owner).
- E-mail desconhecido: 401 `{message: Invalid credentials.}`.
- Senha errada (e-mail existente): **mesmo** status e message.
- Body vazio / e-mail inválido: 422, sem token.
- Health ainda 200 sem auth.

**Feature — Current user**
- Sem Bearer: 401.
- Com token: 200 perfil do dono do token.
- Query `user_id` de outro usuário: ainda o dono do token (anti-IDOR).

**Feature — Logout**
- Com token: 204; GET /user com o mesmo token: 401.
- Sem token: 401.

**Feature — Privilege / seeder**
- Factory admin() → isAdmin true; owner → isOwner true, isAdmin false.
- Seed (ou create via seeder in test): login admin@ e owner@ com env defaults.

**Feature — Rate limit**
- 5 POSTs falhos iguais → o seguinte 429.

**Unit**
- UserRole cases admin/owner.
- isAdmin() false quando role é Owner.
- Fillable não contém `role` (reflection ou attempt `$user->fill(['role' => admin])` e assert role inalterado se já era owner).

**Não** enfraquecer HealthTest.

## Remaining Risks

- Defaults `password` no seed são fracos por desenho (desafio local). Produção exigiria secrets reais — fora do prazo.
- Dois tokens se o usuário loga duas vezes: esperado; logout só revoga o Bearer enviado.
- Sem `GET /users/{id}` agora; o próximo PR de investimentos **deve** policies + testes IDOR — este plan não cobre Investment.
- Timing attack leve em `first()` vs password check: aceitável no desafio; mensagem única no body.
- Sanctum stateful SPA não configurada: a UI futura deve usar Bearer em memória (já decisão de produto), não cookie, ou então um PR de CSRF separado.
- `User::create(['role' => ...])` ignora role se não fillable — seeder **obrigado** a factory state / forceFill; Test Agent deve cobrir seeder.

---

**Handoff**: Test Agent cria os testes acima. Code Agent só depois de `TESTS_CREATED`. Não implementar neste gate.
