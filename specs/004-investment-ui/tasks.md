# Tasks: Interface web de investimentos

**Input**: Design documents from `/specs/004-investment-ui/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/ui.md, quickstart.md, architecture-security-plan.md

**Tests**: Obrigatórios (SDD III). Em cada fatia: testes Vitest → FAIL → implementação → PASS. Sem código de produto da fatia antes dos testes. A UI **não** calcula 0,52% nem IR. Sem novos endpoints Laravel → sem Scribe. API (`routes/api.php`, `app/Domain`) intocada.

**Organization**: Setup SPA → fundação (client/session/types/router) → US1 login+lista (MVP) → US2 criar → US3 detalhe → US4 resgate → US5 logout → polish/screenshots/reviews/PR.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: parallel (different files, no unfinished deps)
- **[Story]**: US1 login+lista, US2 criar, US3 detalhe, US4 resgate, US5 logout
- Exact file paths in every task

## Path Conventions

SPA: `frontend/`. Screenshots: `screenshots/`. Do not modify `app/Domain/` or `routes/api.php`.

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Scaffold Vite React TS e ignore files. Architecture-security-plan já escrito.

- [X] T001 Confirm `specs/004-investment-ui/architecture-security-plan.md` exists; do not change `routes/api.php` or `app/Domain/Investment/*`
- [X] T002 Scaffold `frontend/` with Vite + React + TypeScript (`frontend/package.json`, `frontend/vite.config.ts`, `frontend/tsconfig.json`, `frontend/index.html`, `frontend/src/main.tsx`)
- [X] T003 [P] Add `frontend/.env.example` with `VITE_API_URL=http://localhost:8000/api`
- [X] T004 [P] Ensure root `.gitignore` includes `frontend/node_modules/`, `frontend/dist/`, `frontend/.env`
- [X] T005 Add Vitest + Testing Library deps and `test` script in `frontend/package.json`; configure Vitest in `frontend/vite.config.ts` (jsdom)
- [X] T006 Run `npm install` in `frontend/`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Tipos, session, HTTP client, shell de rotas. Bloqueia histórias.

**⚠️ CRITICAL**: Não começar US1 até T014 PASS

### Tests (FAIL first)

- [X] T007 [P] Write `frontend/src/auth/session.test.ts` (set/get/clear token+user in sessionStorage)
- [X] T008 [P] Write `frontend/src/api/client.test.ts` (Bearer header; 401 clears session)
- [X] T009 [P] Write `frontend/src/api/auth.test.ts` asserting login body contains only `email` and `password` (no `role` / `user_id`)
- [X] T010 Run `npm test` in `frontend/` for the three files above and confirm FAIL

### Implementation

- [X] T011 Create `frontend/src/types/api.ts` (User, Investment, InvestmentPage; money fields as string) per `specs/004-investment-ui/data-model.md`
- [X] T012 Create `frontend/src/auth/session.ts` (namespaced sessionStorage get/set/clear)
- [X] T013 Create `frontend/src/api/client.ts` (fetch wrapper, `VITE_API_URL`, Bearer, 401 → clearSession)
- [X] T014 Create `frontend/src/api/auth.ts` (`login`, `logout`; allowlist credentials only)
- [X] T015 Create `frontend/src/styles/tokens.css` (CSS variables; brand-forward; avoid generic AI theme defaults)
- [X] T016 Create `frontend/src/auth/RequireAuth.tsx` and wire routes stub in `frontend/src/App.tsx` (`/login` public; `/investments*` protected)
- [X] T017 Re-run `npm test` for session/client/auth tests and confirm PASS

**Checkpoint**: Foundation ready — HTTP + session + router shell

---

## Phase 3: User Story 1 - Entrar e ver a lista (Priority: P1) 🎯 MVP

**Goal**: Login → lista paginada com dono, data, valor, saldo, status. Sem token → login. 401 genérico.

**Independent Test**: Mock API — Owner lista um item; Admin mock com dois owners sem filtro client; senha errada mensagem genérica; rota `/investments` redireciona se sem sessão.

### Tests for User Story 1 ⚠️ FAIL first

- [X] T018 [P] [US1] Write `frontend/src/auth/RequireAuth.test.tsx` (no token → redirect login; token → children)
- [X] T019 [P] [US1] Write `frontend/src/pages/LoginPage.test.tsx` (401 → generic message; success stores session; no role field in UI/payload)
- [X] T020 [P] [US1] Write `frontend/src/api/investments.test.ts` for `list` (calls GET with page/per_page; **never** sends `user_id` query)
- [X] T021 [P] [US1] Write `frontend/src/pages/InvestmentsListPage.test.tsx` (renders owner/date/amount/expected_balance/status from mock; pagination control; empty state)
- [X] T022 [US1] Run `npm test` for US1 test files and confirm FAIL

### Implementation for User Story 1

- [X] T023 [US1] Implement `list` in `frontend/src/api/investments.ts`
- [X] T024 [US1] Create `frontend/src/components/InvestmentRow.tsx` and `frontend/src/components/Pagination.tsx`
- [X] T025 [US1] Create `frontend/src/components/AppShell.tsx` (brand/logo, user name/email/role display only, nav links)
- [X] T026 [US1] Create `frontend/src/pages/LoginPage.tsx` (brand-first composition; email/password; PT errors)
- [X] T027 [US1] Create `frontend/src/pages/InvestmentsListPage.tsx` (fetch list; no client-side owner filter; link to create/detail)
- [X] T028 [US1] Wire `/login` and `/investments` in `frontend/src/App.tsx` with `RequireAuth` + `AppShell`
- [X] T029 [US1] Re-run `npm test` for US1 tests and confirm PASS

**Checkpoint**: US1 MVP independently testable

---

## Phase 4: User Story 2 - Criar um investimento (Priority: P1)

**Goal**: Form só `amount` + `created_on`; POST allowlist; redirect detalhe ou lista.

**Independent Test**: Payload keys only those two; UX rejects `0.00`; no `user_id`/`expected_balance` fields in DOM.

### Tests for User Story 2 ⚠️ FAIL first

- [X] T030 [P] [US2] Extend `frontend/src/api/investments.test.ts` for `create` (body keys only `amount`, `created_on`)
- [X] T031 [P] [US2] Write `frontend/src/pages/CreateInvestmentPage.test.tsx` (no owner/status/balance inputs; submit allowlist; validation empty/zero)
- [X] T032 [US2] Run those tests and confirm FAIL

### Implementation for User Story 2

- [X] T033 [US2] Implement `create` in `frontend/src/api/investments.ts`
- [X] T034 [US2] Create `frontend/src/pages/CreateInvestmentPage.tsx` and route `/investments/new` in `frontend/src/App.tsx`
- [X] T035 [US2] Re-run US2 tests and confirm PASS

**Checkpoint**: US2 independently testable

---

## Phase 5: User Story 3 - Ver o detalhe com ganhos (Priority: P1)

**Goal**: GET detalhe; mostrar gain/balance do servidor; 404 sem inventar dados.

**Independent Test**: Mock 1005.20/5.20 displayed as-is; 404 shows not-found; no client Math on amount.

### Tests for User Story 3 ⚠️ FAIL first

- [X] T036 [P] [US3] Extend `frontend/src/api/investments.test.ts` for `show`
- [X] T037 [P] [US3] Write `frontend/src/pages/InvestmentDetailPage.test.tsx` (renders amount/expected_balance/gain/status from mock; 404 state; withdrawn shows tax/net from mock **without** computing)
- [X] T038 [US3] Run those tests and confirm FAIL

### Implementation for User Story 3

- [X] T039 [US3] Implement `show` in `frontend/src/api/investments.ts`
- [X] T040 [US3] Create `frontend/src/pages/InvestmentDetailPage.tsx` and route `/investments/:id` in `frontend/src/App.tsx`
- [X] T041 [US3] Re-run US3 tests and confirm PASS

**Checkpoint**: US3 independently testable

---

## Phase 6: User Story 4 - Resgatar e ver o valor líquido (Priority: P1)

**Goal**: Form `withdrawn_on` no detalhe se active; POST withdraw allowlist; mostrar tax/net do response.

**Independent Test**: Body só `withdrawn_on`; após sucesso status withdrawn e tax do mock; sem campo tax no form.

### Tests for User Story 4 ⚠️ FAIL first

- [X] T042 [P] [US4] Extend `frontend/src/api/investments.test.ts` for `withdraw` (body only `withdrawn_on`)
- [X] T043 [P] [US4] Write `frontend/src/components/WithdrawForm.test.tsx` and extend `InvestmentDetailPage.test.tsx` (active shows form; submit allowlist; after success shows server tax/net)
- [X] T044 [US4] Run those tests and confirm FAIL

### Implementation for User Story 4

- [X] T045 [US4] Implement `withdraw` in `frontend/src/api/investments.ts`
- [X] T046 [US4] Create `frontend/src/components/WithdrawForm.tsx` and integrate into `frontend/src/pages/InvestmentDetailPage.tsx` (only when `status === 'active'`)
- [X] T047 [US4] Re-run US4 tests and confirm PASS

**Checkpoint**: US4 independently testable

---

## Phase 7: User Story 5 - Encerrar a sessão (Priority: P2)

**Goal**: Logout limpa sessão; lista inacessível.

**Independent Test**: Logout clears storage; RequireAuth redirects; AppShell logout button.

### Tests for User Story 5 ⚠️ FAIL first

- [X] T048 [P] [US5] Write `frontend/src/components/AppShell.test.tsx` (logout calls API + clearSession + navigates login)
- [X] T049 [US5] Run AppShell/logout tests and confirm FAIL

### Implementation for User Story 5

- [X] T050 [US5] Wire logout in `frontend/src/components/AppShell.tsx` using `frontend/src/api/auth.ts` `logout` + `session.clear`
- [X] T051 [US5] Re-run US5 tests + full `npm test` in `frontend/` and confirm PASS

**Checkpoint**: All five stories independently functional

---

## Phase 8: Polish, screenshots, reviews, PR

**Purpose**: Gates 5–8. Sem Scribe. Screenshots + README. PR → `development`.

- [X] T052 Update root `README.md` (how to run API + `frontend/`, SPA libs, link to `/docs`, `screenshots/`)
- [X] T053 Capture ≥2 PNGs into `screenshots/` (list + detail or post-withdraw) per FR-013 — no passwords visible
- [X] T054 Confirm no changes to `routes/api.php` / `app/Domain/Investment/*` and no client gain/tax formula (grep `0.52` / `1.0052` / `0.225` under `frontend/src` must be empty or comments-only)
- [X] T055 Run full `npm test` in `frontend/` and `php artisan test` at repo root (API regression). If FAIL → Code Agent only
- [X] T056 Code Review per `.cursor/skills/sdd-code-review/SKILL.md` vs `specs/004-investment-ui/architecture-security-plan.md` (fail if client computes tax or filters list by role as security)
- [X] T057 Security Review per `.cursor/skills/sdd-security-review/SKILL.md` (frontend_permissions PASS as UX-only; IDOR relies on API 404; no secrets in bundle)
- [X] T058 Run `graphify update .` and include `graphify-out/` except `cost.json`
- [X] T059 Assertive commit + PR targeting `development` per `.cursor/skills/sdd-git-pr/SKILL.md` (title e.g. `feat(ui): add React SPA for login, investments, and taxed withdrawal`)

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup**: immediate
- **Foundational**: after T006; **blocks** US1–US5 until T017 PASS
- **US1**: after T017
- **US2**: after T029 (needs auth shell + list nav)
- **US3**: after T029 (detail from list); can parallel US2 if staffed on different files
- **US4**: after T041 (withdraw on detail)
- **US5**: after T029 (AppShell exists); can parallel US2–US4 if logout stubbed
- **Polish**: after T051; T055 PASS before T056–T059
- Forbidden: review fail → code → PR without re-test

### User Story Dependencies

- **US1 (P1) MVP**: after Foundational
- **US2 (P1)**: after US1
- **US3 (P1)**: after US1
- **US4 (P1)**: after US3
- **US5 (P2)**: after US1 (shell)

### Within Each User Story

- Tests FAIL before implementation
- No client-side compound/tax math
- Payload allowlists only

### Parallel Opportunities

- T003/T004 after T002
- T007/T008/T009 after T006
- T018–T021 after T017
- T030/T031 after T029
- T036/T037 after T029 (US3 tests can start while US2 implements if careful)

---

## Parallel Example: User Story 1 tests

```text
Task: Write frontend/src/pages/LoginPage.test.tsx
Task: Write frontend/src/pages/InvestmentsListPage.test.tsx
Task: Write frontend/src/api/investments.test.ts (list)
Task: Write frontend/src/auth/RequireAuth.test.tsx
```

Then implement pages/components sequentially where they share `App.tsx`.

---

## Implementation Strategy

### MVP First (User Story 1)

1. Phase 1–2 foundation
2. Phase 3 login + list
3. **STOP and VALIDATE** `npm test` for US1

### Incremental Delivery

1. Scaffold + client/session
2. US1 login + list
3. US2 create
4. US3 detail
5. US4 withdraw
6. US5 logout
7. Screenshots + README + reviews + PR `development`

### Parallel Team Strategy

One implementer expected. Test Agent writes FAILING Vitest files per slice first.

---

## Notes

- State after this file: `ARCHITECTURE_SECURITY_PLAN_READY` until first FAILING frontend tests → `TESTS_CREATED`
- Do not regenerate Scribe
- `REVIEW_CHANGES_REQUESTED` → Code → Test → `TESTS_PASSED` → Code Review — never T059 directly
- Follow `specs/004-investment-ui/quickstart.md` after T055
- Logo: prefer `/images/...` from Laravel `public/images` or copy reference into `frontend/public`
