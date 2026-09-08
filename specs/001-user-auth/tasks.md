# Tasks: Autenticação e papéis Admin / Owner

**Input**: Design documents from `/specs/001-user-auth/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/auth.yaml, quickstart.md, architecture-security-plan.md

**Tests**: Obrigatórios (SDD / constituição III). Em cada história: criar testes → confirmar FAIL → implementar → validar PASS. Código de produto nunca antes dos testes daquela fatia.

**Organization**: Gates SDD embutidos. Setup/fundação compartilhada, depois US1 (MVP), US3, US2, US4, polish (review + PR).

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: US1 login, US2 logout, US3 papel no servidor, US4 seed
- Include exact file paths in descriptions

## Path Conventions

Laravel no root: `app/`, `database/`, `routes/`, `tests/`. Sem `frontend/` neste PR.

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Sanctum e env locais. Architecture-security-plan já escrito — não reabrir o gate 2.

- [x] T001 Add `laravel/sanctum` to `composer.json`, install, and publish `personal_access_tokens` migration under `database/migrations/`
- [x] T002 [P] Add `SEED_ADMIN_EMAIL`, `SEED_ADMIN_PASSWORD`, `SEED_OWNER_EMAIL`, `SEED_OWNER_PASSWORD` (local defaults) to `.env.example`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Papel + User + factory. Testes unitários primeiro.

**⚠️ CRITICAL**: Não começar US1 HTTP até T011 PASS

### Tests (FAIL first)

- [x] T003 [P] Write `tests/Unit/UserRoleTest.php` covering `admin` and `owner` cases on `app/Enums/UserRole.php`
- [x] T004 [P] Write `tests/Unit/UserTest.php` asserting `app/Models/User.php` fillable excludes `role`, `fill(['role' => …])` does not promote Owner, `isAdmin()` / `isOwner()` fail-closed
- [x] T005 Run `php artisan test tests/Unit/UserRoleTest.php tests/Unit/UserTest.php` and confirm FAIL

### Implementation

- [x] T006 Create `app/Enums/UserRole.php` (`admin`, `owner`)
- [x] T007 Create `database/migrations/` `*_add_role_to_users_table.php` (`role` string, default `owner`; do not edit `database/migrations/0001_01_01_000000_create_users_table.php`)
- [x] T008 Update `app/Models/User.php`: `HasApiTokens`, cast `role` → `UserRole`, `isAdmin()` / `isOwner()`; keep Fillable `name,email,password` only
- [x] T009 Update `database/factories/UserFactory.php`: default `UserRole::Owner`, states `admin()` and `owner()`
- [x] T010 Re-run `php artisan test tests/Unit/UserRoleTest.php tests/Unit/UserTest.php` and confirm PASS

**Checkpoint**: Foundation ready — user story HTTP can begin

---

## Phase 3: User Story 1 - Entrar na aplicação (Priority: P1) 🎯 MVP

**Goal**: Login com e-mail/senha, token Bearer, perfil próprio, throttle, health público.

**Independent Test**: `POST /api/login` válido → token + user sem senha; inválido → 401 mesma mensagem; `GET /api/user` sem token → 401; com token → self; `GET /api/health` → 200.

### Tests for User Story 1 ⚠️ FAIL first

- [x] T011 [P] [US1] Write `tests/Feature/Api/Auth/LoginTest.php` per `specs/001-user-auth/contracts/auth.yaml` and architecture-security-plan Required Tests (success, unknown email, wrong password same body, 422, ignore `role`/`user_id` in body)
- [x] T012 [P] [US1] Write `tests/Feature/Api/Auth/CurrentUserTest.php` (401, 200 self, ignore `user_id` query)
- [x] T013 [P] [US1] Write `tests/Feature/Api/Auth/LoginRateLimitTest.php` (6th failed login → 429)
- [x] T014 [US1] Run those Feature tests plus `tests/Feature/Api/HealthTest.php` and confirm auth tests FAIL and health still PASS

### Implementation for User Story 1

- [x] T015 [P] [US1] Create `app/Http/Requests/Api/LoginRequest.php` (`email` required|email, `password` required|string)
- [x] T016 [P] [US1] Create `app/Http/Resources/UserResource.php` allowlist `id,name,email,role`
- [x] T017 [US1] Create `app/Services/Auth/LoginUser.php` (generic 401 `Invalid credentials.`; `createToken('api')`; never persist request `role`)
- [x] T018 [US1] Create `app/Http/Controllers/Api/LoginController.php` (Scribe `@unauthenticated`, `@group Authentication`)
- [x] T019 [US1] Create `app/Http/Controllers/Api/CurrentUserController.php` (`$request->user()` only; never `User::find($id)`)
- [x] T020 [US1] Register named limiter `login` (5/min by email|ip) in `app/Providers/AppServiceProvider.php`
- [x] T021 [US1] Wire `POST /login` (`throttle:login`) and `GET /user` (`auth:sanctum`) in `routes/api.php`; keep `GET /health` public
- [x] T022 [US1] Run `php artisan test tests/Feature/Api/Auth/LoginTest.php tests/Feature/Api/Auth/CurrentUserTest.php tests/Feature/Api/Auth/LoginRateLimitTest.php tests/Feature/Api/HealthTest.php` and confirm PASS

**Checkpoint**: US1 independently testable (MVP login + me)

---

## Phase 4: User Story 3 - Papel só no servidor (Priority: P1)

**Goal**: Cliente não eleva papel; predicados Admin/Owner corretos.

**Independent Test**: Owner envia `role=admin` no login/perfil e permanece owner; `isAdmin()` só com enum Admin.

### Tests for User Story 3 ⚠️ FAIL first if gaps remain

- [x] T023 [P] [US3] Write `tests/Feature/Api/Auth/PrivilegeEscalationTest.php` (login extra fields ignored; `GET /api/user` extra fields ignored; factory `admin()` vs `owner()`)
- [x] T024 [US3] Run `php artisan test tests/Feature/Api/Auth/PrivilegeEscalationTest.php tests/Unit/UserTest.php` — FAIL then, after T008/T017, PASS (no new production types unless a gap vs architecture-security-plan)

**Checkpoint**: Privilege escalation tests green; role still not fillable

---

## Phase 5: User Story 2 - Encerrar a sessão (Priority: P2)

**Goal**: Logout revoga o Bearer atual.

**Independent Test**: Login → logout 204 → mesmo token em `GET /api/user` → 401; logout sem token → 401.

### Tests for User Story 2 ⚠️ FAIL first

- [x] T025 [P] [US2] Write `tests/Feature/Api/Auth/LogoutTest.php`
- [x] T026 [US2] Run it and confirm FAIL

### Implementation for User Story 2

- [x] T027 [US2] Create `app/Http/Controllers/Api/LogoutController.php` (delete `currentAccessToken()`, 204)
- [x] T028 [US2] Add `POST /logout` inside `auth:sanctum` group in `routes/api.php`
- [x] T029 [US2] Run `php artisan test tests/Feature/Api/Auth/LogoutTest.php` and confirm PASS

**Checkpoint**: US1 + US2 sessions work independently

---

## Phase 6: User Story 4 - Contas iniciais (Priority: P2)

**Goal**: Seed Admin + Owner via env, factory states / `forceFill`, never HTTP.

**Independent Test**: `db:seed` then login both accounts; roles match `specs/001-user-auth/data-model.md`.

### Tests for User Story 4 ⚠️ FAIL first

- [x] T030 [P] [US4] Write `tests/Feature/Api/Auth/DatabaseSeederAuthTest.php` (run `DatabaseSeeder`, login seed emails from `.env.example` defaults, assert roles)
- [x] T031 [US4] Run it and confirm FAIL

### Implementation for User Story 4

- [x] T032 [US4] Replace `test@example.com` in `database/seeders/DatabaseSeeder.php` with Admin + Owner `updateOrCreate` / factory states using `SEED_*` env
- [x] T033 [US4] Run `php artisan test tests/Feature/Api/Auth/DatabaseSeederAuthTest.php` and confirm PASS

**Checkpoint**: All four stories independently functional

---

## Phase 7: Polish, validation, reviews, PR

**Purpose**: Scribe, suite completa, gates 6–8 SDD. Sem pular `TESTS_PASSED` → review → security → git.

- [x] T034 Generate Scribe docs (`composer docs` / `php artisan scribe:generate`) and commit output under `public/docs/`
- [x] T035 Run full `php artisan test` (Test Agent validate). If FAIL → Code Agent only; do not review yet
- [x] T036 Code Review per `.cursor/skills/sdd-code-review/SKILL.md` (file-by-file vs `specs/001-user-auth/architecture-security-plan.md`)
- [x] T037 Security Review per `.cursor/skills/sdd-security-review/SKILL.md` (IDOR, mass assignment, privilege escalation, secrets)
- [x] T038 Run `graphify update .` and include `graphify-out/` except `cost.json`
- [x] T039 Assertive commit + PR targeting `development` per `.cursor/skills/sdd-git-pr/SKILL.md` (title e.g. `feat(auth): issue Sanctum tokens and keep Admin/Owner roles server-side`); follow `specs/001-user-auth/quickstart.md`

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: Starts immediately
- **Foundational (Phase 2)**: After T001 (Sanctum). **BLOCKS** all user stories. Tests T003–T005 before code T006–T009
- **US1 (Phase 3)**: After T010. Tests T011–T014 before T015–T021
- **US3 (Phase 4)**: After US1 login exists (T022). Mostly security tests on the same stack
- **US2 (Phase 5)**: After Sanctum tokens (T008). Can follow US1 sequentially
- **US4 (Phase 6)**: After factory states (T009) and login (T022)
- **Polish (Phase 7)**: After T033; T035 PASS required before T036–T039. Forbidden: review fail → code → PR without re-test

### User Story Dependencies

- **US1 (P1)**: After Phase 2 — MVP
- **US3 (P1)**: After US1 HTTP — independently testable via PrivilegeEscalationTest
- **US2 (P2)**: After tokens — independently testable via LogoutTest
- **US4 (P2)**: After User factory + login — independently testable via seeder test

### Within Each User Story

- Tests MUST be written and FAIL before implementation
- Models/enum before services
- Services before endpoints
- Story PASS before next priority when sequential

### Parallel Opportunities

- T002 // T001 after composer lock not conflicting
- T003 // T004
- T011 // T012 // T013
- T015 // T016
- T023 after T022; T025 can be written in parallel with US3 tests (different files)

---

## Parallel Example: User Story 1

```text
Task: Write tests/Feature/Api/Auth/LoginTest.php
Task: Write tests/Feature/Api/Auth/CurrentUserTest.php
Task: Write tests/Feature/Api/Auth/LoginRateLimitTest.php
```

Then implement `LoginRequest.php` and `UserResource.php` in parallel; `LoginUser.php` after Request; controllers after service/resource; routes last.

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Phase 1 Setup (Sanctum)
2. Phase 2 Foundational tests → enum/User/factory → unit PASS
3. Phase 3 US1 tests FAIL → login/me/throttle → Feature PASS
4. **STOP and VALIDATE** US1 (quickstart steps 1–5 minus logout)

### Incremental Delivery

1. Setup + Foundational
2. US1 → demo login/me
3. US3 → privilege tests
4. US2 → logout
5. US4 → seed
6. Scribe + reviews + PR `development`

### Parallel Team Strategy

One implementer expected. If split: A writes all FAILING tests first (Test Agent); B does not start production files until those tests exist.

---

## Notes

- State after this file: still `ARCHITECTURE_SECURITY_PLAN_READY` until Test Agent completes T003–T005 and US tests → `TESTS_CREATED`
- Do not weaken assertions; do not add `role` to Fillable
- Do not implement `GET /api/users/{id}`
- `REVIEW_CHANGES_REQUESTED` → Code → Test → `TESTS_PASSED` → Code Review again — never straight to T039
- Commit per logical group if desired; PR only after T035–T038
