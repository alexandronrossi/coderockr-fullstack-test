# Tasks: Criar, listar, detalhar e resgatar investimentos

**Input**: Design documents from `/specs/003-investment-api/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/investments.yaml, quickstart.md, architecture-security-plan.md

**Tests**: Obrigatórios (SDD III). Em cada fatia: testes → FAIL → implementação → PASS. Sem código de produto da fatia antes dos testes. Reutilizar `app/Domain/Investment/*` (não reescrever 0,52% nem IR). SPA fora deste PR.

**Organization**: Fundação (model/policy/rotas autenticadas) → US1 criar (MVP) → US2 detalhe → US3 lista paginada → US4 resgate → Scribe/reviews/PR.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: parallel (different files, no unfinished deps)
- **[Story]**: US1 criar, US2 detalhe, US3 lista, US4 resgate
- Exact file paths in every task

## Path Conventions

Laravel root: `app/`, `database/`, `routes/api.php`, `tests/Feature/Api/Investments/`. Do not add `frontend/`.

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Confirmar architecture-security-plan e pastas. Sem pacote Composer novo.

- [X] T001 Confirm `specs/003-investment-api/architecture-security-plan.md` exists; do not change gain/tax formulas in `app/Domain/Investment/Money.php`, `CompoundGainCalculator.php`, `WithdrawalTaxCalculator.php`, `CivilMonthAnniversary.php`, or `InvestmentValuation.php`
- [X] T002 Create directory `tests/Feature/Api/Investments/`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Tabela, model, policy, 401 nas quatro rotas. Bloqueia todas as histórias.

**⚠️ CRITICAL**: Não começar US1 até T016 PASS

### Tests (FAIL first)

- [X] T003 [P] Write `tests/Feature/Api/Investments/InvestmentUnauthenticatedTest.php` asserting 401 on `POST /api/investments`, `GET /api/investments`, `GET /api/investments/{id}`, `POST /api/investments/{id}/withdraw` per `specs/003-investment-api/contracts/investments.yaml`
- [X] T004 Run `php artisan test tests/Feature/Api/Investments/InvestmentUnauthenticatedTest.php` and confirm FAIL

### Implementation

- [X] T005 Create `database/migrations/` `*_create_investments_table.php` (`user_id` FK, `amount_cents` unsigned int, `created_on` date, `withdrawn_on` date nullable, index on `user_id`) per `specs/003-investment-api/data-model.md`
- [X] T006 Create `app/Models/Investment.php` (`visibleTo` fail-closed, belongsTo User, date casts; do **not** fillable `user_id` / `withdrawn_on` / status)
- [X] T007 Create `database/factories/InvestmentFactory.php` (default active, `forUser`, `withdrawn` state)
- [X] T008 Add `investments()` HasMany to `app/Models/User.php` without changing `#[Fillable]`
- [X] T009 Create `app/Policies/InvestmentPolicy.php` (`viewAny` authenticated; `view`/`withdraw` Admin or owner; `create` authenticated)
- [X] T010 Create `app/Domain/Investment/InvestmentAlreadyWithdrawn.php` (DomainException, message `This investment has already been withdrawn.`)
- [X] T011 Map `InvalidInvestmentDate` and `InvestmentAlreadyWithdrawn` to HTTP 422 `{ message }` in `bootstrap/app.php` without weakening JSON rendering for `api/*`
- [X] T012 Add named limiter `investments` (60/min by user id + IP) in `app/Providers/AppServiceProvider.php` without changing the `login` limiter
- [X] T013 Create stub invocables `app/Http/Controllers/Api/StoreInvestmentController.php`, `IndexInvestmentController.php`, `ShowInvestmentController.php`, `WithdrawInvestmentController.php` (`abort(501)` until story implementation) and register them in `routes/api.php` inside `auth:sanctum` with `throttle:investments` (`GET/POST /investments`, `GET /investments/{investment}`, `POST /investments/{investment}/withdraw`). Keep `GET /health` public
- [X] T014 Re-run `php artisan test tests/Feature/Api/Investments/InvestmentUnauthenticatedTest.php` and confirm PASS

**Checkpoint**: Foundation ready — 401 sem token; Auth/Health/Domain regressão ainda verde se corrida à parte

---

## Phase 3: User Story 1 - Registrar um investimento (Priority: P1) 🎯 MVP

**Goal**: `POST /api/investments` persiste dono = autenticado, amount string duas casas &gt; 0, `created_on` ≤ hoje; ignora `user_id` / saldo / IR / status.

**Independent Test**: Owner autenticado cria 1000.00; 201 com dono correto, status `active`, ganho 0 no dia da criação; extra `user_id` ignorado.

### Tests for User Story 1 ⚠️ FAIL first

- [X] T015 [P] [US1] Write `tests/Feature/Api/Investments/StoreInvestmentTest.php` (201 valid create; 422 zero/negative/future `created_on`; 401 already covered) per `specs/003-investment-api/contracts/investments.yaml`
- [X] T016 [P] [US1] Write `tests/Feature/Api/Investments/InvestmentMassAssignmentTest.php` asserting `POST /api/investments` ignores `user_id`, `status`, `expected_balance`, `tax`, `withdrawn_on`, `role`
- [X] T017 [US1] Run `php artisan test tests/Feature/Api/Investments/StoreInvestmentTest.php tests/Feature/Api/Investments/InvestmentMassAssignmentTest.php` and confirm FAIL

### Implementation for User Story 1

- [X] T018 [US1] Create `app/Http/Requests/Api/StoreInvestmentRequest.php` (allowlist `amount` regex `^\d+\.\d{2}$` &gt; 0.00, `created_on` `Y-m-d` `before_or_equal:today`)
- [X] T019 [US1] Create `app/Http/Resources/InvestmentResource.php` (allowlist id, owner id/name/email, amount, dates, status, valuation fields; never password)
- [X] T020 [US1] Create `app/Services/Investment/CreateInvestment.php` (`user_id` = actor, `Money::fromDecimalString`, `InvestmentValuation` asOf = created_on; never `$request->all()`)
- [X] T021 [US1] Implement `app/Http/Controllers/Api/StoreInvestmentController.php` (authorize `create`, 201 Resource, Scribe `@group Investments` `@authenticated`)
- [X] T022 [US1] Re-run `php artisan test tests/Feature/Api/Investments/StoreInvestmentTest.php tests/Feature/Api/Investments/InvestmentMassAssignmentTest.php tests/Feature/Api/Investments/InvestmentUnauthenticatedTest.php` and confirm PASS

**Checkpoint**: US1 independently testable (criar + 401)

---

## Phase 4: User Story 2 - Ver o investimento com saldo esperado (Priority: P1)

**Goal**: `GET /api/investments/{id}` com valuation; Owner ID alheio → 404; Admin vê o do Owner; 1 mês civil → 1005.20.

**Independent Test**: `Carbon::setTestNow`; criar em D, mostrar em D+1 mês civil → `expected_balance` 1005.20; segundo Owner no mesmo id → 404 sem amount.

### Tests for User Story 2 ⚠️ FAIL first

- [X] T023 [P] [US2] Write `tests/Feature/Api/Investments/ShowInvestmentTest.php` (own 200 + 1005.20 after one complete civil month; 404 unknown id)
- [X] T024 [P] [US2] Write `tests/Feature/Api/Investments/InvestmentAuthorizationTest.php` (Owner GET other owner's id → 404 empty of amount; Admin GET that id → 200)
- [X] T025 [US2] Run `php artisan test tests/Feature/Api/Investments/ShowInvestmentTest.php tests/Feature/Api/Investments/InvestmentAuthorizationTest.php` and confirm FAIL

### Implementation for User Story 2

- [X] T026 [US2] Create `app/Services/Investment/ShowInvestment.php` (`visibleTo($user)->findOrFail($id)` then `InvestmentValuation`; never global `Investment::find($id)`)
- [X] T027 [US2] Implement `app/Http/Controllers/Api/ShowInvestmentController.php` (authorize `view`, 200 Resource, Scribe)
- [X] T028 [US2] Re-run `php artisan test tests/Feature/Api/Investments/ShowInvestmentTest.php tests/Feature/Api/Investments/InvestmentAuthorizationTest.php tests/Feature/Api/Investments/StoreInvestmentTest.php` and confirm PASS

**Checkpoint**: US2 independently testable (detalhe + IDOR GET)

---

## Phase 5: User Story 3 - Listar investimentos com paginação (Priority: P1)

**Goal**: `GET /api/investments?page&per_page` scoped; default 15 max 100; query `user_id` não amplia Owner.

**Independent Test**: Dois Owners com registros; lista Owner A só A; Admin vê ambos; página 2 distinta; `user_id` na query não vaza B.

### Tests for User Story 3 ⚠️ FAIL first

- [X] T029 [P] [US3] Write `tests/Feature/Api/Investments/ListInvestmentsTest.php` (Owner isolation; Admin sees all; pagination `meta.total`; `per_page` 101 → 422; `user_id` query ignored for Owner) per contracts
- [X] T030 [US3] Run `php artisan test tests/Feature/Api/Investments/ListInvestmentsTest.php` and confirm FAIL

### Implementation for User Story 3

- [X] T031 [US3] Create `app/Http/Requests/Api/IndexInvestmentRequest.php` (`page` min 1, `per_page` 1–100; no `user_id` rule)
- [X] T032 [US3] Create `app/Services/Investment/ListInvestments.php` (`visibleTo`, `with('user')`, `created_at desc, id desc`, paginate, valuation per item)
- [X] T033 [US3] Implement `app/Http/Controllers/Api/IndexInvestmentController.php` (authorize `viewAny`, Resource collection, Scribe)
- [X] T034 [US3] Re-run `php artisan test tests/Feature/Api/Investments/ListInvestmentsTest.php` and confirm PASS

**Checkpoint**: US3 independently testable (lista paginada)

---

## Phase 6: User Story 4 - Resgatar o valor integral com imposto (Priority: P1)

**Goal**: `POST /api/investments/{id}/withdraw` com `withdrawn_on`; lock; IR pelo domínio; segundo resgate 422; freeze no GET.

**Independent Test**: Resgate válido → `status` withdrawn e tax do servidor; body `tax` ignorado; segundo POST 422; GET posterior mesmo saldo; Owner withdraw ID alheio 404.

### Tests for User Story 4 ⚠️ FAIL first

- [X] T035 [P] [US4] Write `tests/Feature/Api/Investments/WithdrawInvestmentTest.php` (200 freeze; 422 already withdrawn; 422 `withdrawn_on` before `created_on` or future; ignore client `tax`/`net`/`expected_balance`)
- [X] T036 [US4] Extend `tests/Feature/Api/Investments/InvestmentAuthorizationTest.php` (Owner POST withdraw on other owner's id → 404; Admin withdraw Owner's investment → 200, owner unchanged)
- [X] T037 [US4] Extend `tests/Feature/Api/Investments/InvestmentMassAssignmentTest.php` (withdraw ignores `tax`, `net`, `rate`, `expected_balance`, `user_id`)
- [X] T038 [US4] Run `php artisan test tests/Feature/Api/Investments/WithdrawInvestmentTest.php tests/Feature/Api/Investments/InvestmentAuthorizationTest.php tests/Feature/Api/Investments/InvestmentMassAssignmentTest.php` and confirm FAIL (new assertions)

### Implementation for User Story 4

- [X] T039 [US4] Create `app/Http/Requests/Api/WithdrawInvestmentRequest.php` (allowlist `withdrawn_on` `Y-m-d` `before_or_equal:today`)
- [X] T040 [US4] Create `app/Services/Investment/WithdrawInvestment.php` (`visibleTo` + `lockForUpdate` in transaction; `InvestmentAlreadyWithdrawn` if already set; `InvalidInvestmentDate` if before `created_on`; persist only `withdrawn_on`; valuation from domain)
- [X] T041 [US4] Implement `app/Http/Controllers/Api/WithdrawInvestmentController.php` (authorize `withdraw`, 200 Resource, Scribe)
- [X] T042 [US4] Extend `tests/Feature/Api/Investments/ShowInvestmentTest.php` freeze-after-withdraw (GET after later `setTestNow` equals valuation at `withdrawn_on`)
- [X] T043 [US4] Run `php artisan test tests/Feature/Api/Investments` and confirm PASS

**Checkpoint**: Four stories independently functional

---

## Phase 7: Polish, validation, reviews, PR

**Purpose**: Gates 5–8. Scribe obrigatório (endpoints novos). PR contra `development`.

- [X] T044 Run `vendor/bin/pint --dirty` on PHP touched under `app/`, `tests/Feature/Api/Investments/`, `routes/api.php`, `bootstrap/app.php`, `app/Providers/AppServiceProvider.php`
- [X] T045 Run `php artisan scribe:generate` (or `composer docs`) and commit generated `public/docs` for group Investments
- [X] T046 Run full `php artisan test` (Investments + Auth + Health + `tests/Unit/Domain`). If FAIL → Code Agent only
- [X] T047 Confirm no SPA/`frontend/` changes and no rewrite of domain rates in `app/Domain/Investment/CompoundGainCalculator.php` / `WithdrawalTaxCalculator.php`
- [X] T048 Code Review per `.cursor/skills/sdd-code-review/SKILL.md` vs `specs/003-investment-api/architecture-security-plan.md` (fail PR if global `find($id)` or client-supplied tax persisted)
- [X] T049 Security Review per `.cursor/skills/sdd-security-review/SKILL.md` (IDOR 404, mass assignment, privilege, secrets, lock on withdraw)
- [X] T050 Run `graphify update .` and include `graphify-out/` except `cost.json`
- [X] T051 Assertive commit + PR targeting `development` per `.cursor/skills/sdd-git-pr/SKILL.md` (title e.g. `feat(api): persist investments with owner-scoped list and taxed withdrawal`)

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup**: immediate
- **Foundational**: after T002; **blocks** US1–US4 until T014 PASS
- **US1**: after T014
- **US2**: after T022 (precisa existir investimento)
- **US3**: after T022 (lista de criados); pode seguir US2 em paralelo só se staffed em arquivos distintos
- **US4**: after T028 (show freeze) e T022
- **Polish**: after T043; T046 PASS before T048–T051
- Forbidden: review fail → code → PR without re-test

### User Story Dependencies

- **US1 (P1) MVP**: after Foundational
- **US2 (P1)**: after US1
- **US3 (P1)**: after US1 (isolation precisa de dois donos com creates)
- **US4 (P1)**: after US1 + US2 (freeze no GET)

### Within Each User Story

- Tests FAIL before implementation
- `visibleTo` + Policy, never `Investment::find($id)` sozinho
- Scribe annotations on controllers as they are implemented; generate docs in T045

### Parallel Opportunities

- T015/T016 after T014
- T023/T024 after T022
- T013 stubs are one file-set; do not parallel with T014

---

## Parallel Example: User Story 1 tests

```text
Task: Write tests/Feature/Api/Investments/StoreInvestmentTest.php
Task: Write tests/Feature/Api/Investments/InvestmentMassAssignmentTest.php
```

Then implement Request, Resource, CreateInvestment, StoreInvestmentController sequentially (same HTTP path).

---

## Implementation Strategy

### MVP First (User Story 1)

1. Phase 1–2 foundation + 401
2. Phase 3 create
3. **STOP and VALIDATE** `php artisan test tests/Feature/Api/Investments/StoreInvestmentTest.php`

### Incremental Delivery

1. 401 + schema
2. US1 create
3. US2 show + IDOR GET
4. US3 list
5. US4 withdraw + freeze
6. Scribe + full suite + reviews + PR `development`

### Parallel Team Strategy

One implementer expected. Test Agent writes FAILING Feature tests per slice first.

---

## Notes

- State after this file: `ARCHITECTURE_SECURITY_PLAN_READY` until first FAILING Feature tests → `TESTS_CREATED`
- Canonical 1000/1200/45 remains `tests/Unit/Domain/WithdrawalTaxCalculatorTest.php`; HTTP uses real compound (1005.20)
- `REVIEW_CHANGES_REQUESTED` → Code → Test → `TESTS_PASSED` → Code Review — never T051 directly
- Follow `specs/003-investment-api/quickstart.md` after T046
