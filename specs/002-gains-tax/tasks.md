# Tasks: Ganho composto no dia civil e imposto no resgate

**Input**: Design documents from `/specs/002-gains-tax/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/domain.md, quickstart.md, architecture-security-plan.md

**Tests**: Obrigatórios (SDD III). Em cada fatia: testes → FAIL → implementação → PASS. Sem código de domínio antes dos testes daquela fatia. Sem HTTP, sem model Eloquent, sem Scribe.

**Organization**: Fundação (Money) → US2 calendário → US1 composto (MVP) → US3 IR → US4 freeze → reviews/PR.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: parallel (different files, no unfinished deps)
- **[Story]**: US1 ganho, US2 dias civis, US3 IR, US4 freeze
- Exact file paths in every task

## Path Conventions

Laravel root: `app/Domain/Investment/`, `tests/Unit/Domain/`. Do not add `frontend/` or `routes/api.php` changes.

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Pastas e confirmação do architecture-security-plan (já escrito). Sem pacote Composer novo.

- [X] T001 Create directories `app/Domain/Investment/` and `tests/Unit/Domain/`
- [X] T002 Confirm `specs/002-gains-tax/architecture-security-plan.md` exists and that `routes/api.php` / `app/Models/User.php` stay untouched this PR

---

## Phase 2: Foundational — Money (Blocking)

**Purpose**: Centavos e exceção de data. Bloqueia todas as histórias.

**⚠️ CRITICAL**: Não começar US2/US1 até T006 PASS

### Tests (FAIL first)

- [X] T003 [P] Write `tests/Unit/Domain/MoneyTest.php` (1000.00 ↔ 100000 cents; 0.52% → 100520 cents / 1005.20; reject negative cents) per `specs/002-gains-tax/contracts/domain.md`
- [X] T004 Run `php artisan test tests/Unit/Domain/MoneyTest.php` and confirm FAIL

### Implementation

- [X] T005 Create `app/Domain/Investment/InvalidInvestmentDate.php` (domain exception)
- [X] T006 Create `app/Domain/Investment/Money.php` (integer cents, half-up rate 10052/10000, no float)
- [X] T007 Re-run `php artisan test tests/Unit/Domain/MoneyTest.php` and confirm PASS

**Checkpoint**: Money ready

---

## Phase 3: User Story 2 - Meses com dias que não existem (Priority: P1)

**Goal**: Aniversário = original + N meses, clamp no último dia; **não** encadear clamp.

**Independent Test**: 31/01/2025+1 → 28/02; +2 → 31/03; bissextos e 31/03→30/04 em `CivilMonthAnniversaryTest`.

### Tests for User Story 2 ⚠️ FAIL first

- [X] T008 [P] [US2] Write `tests/Unit/Domain/CivilMonthAnniversaryTest.php` covering n=0 identity and all US2 cases in `specs/002-gains-tax/spec.md` / quickstart
- [X] T009 [US2] Run `php artisan test tests/Unit/Domain/CivilMonthAnniversaryTest.php` and confirm FAIL

### Implementation for User Story 2

- [X] T010 [US2] Create `app/Domain/Investment/CivilMonthAnniversary.php` using `CarbonImmutable` `addMonthsNoOverflow($n)` **from the original date only** (never loop `addMonth()`)
- [X] T011 [US2] Run `php artisan test tests/Unit/Domain/CivilMonthAnniversaryTest.php` and confirm PASS

**Checkpoint**: US2 independently testable

---

## Phase 4: User Story 1 - Saldo esperado com ganho composto (Priority: P1) 🎯 MVP

**Goal**: 0,52% por mês civil completo; período incompleto não rende.

**Independent Test**: 1000 no dia D → ganho 0; +1 mês → 1005.20; incompleto = 0 meses extras.

### Tests for User Story 1 ⚠️ FAIL first

- [X] T012 [P] [US1] Write `tests/Unit/Domain/CompoundGainCalculatorTest.php` (0 months, 1 month 1005.20, 2 months compound half-up, incomplete month, `asOf` before created → `InvalidInvestmentDate`) per `specs/002-gains-tax/contracts/domain.md`
- [X] T013 [US1] Run `php artisan test tests/Unit/Domain/CompoundGainCalculatorTest.php` and confirm FAIL

### Implementation for User Story 1

- [X] T014 [US1] Create `app/Domain/Investment/CompoundGainCalculator.php` (count N while `anniversary(created, N) <= asOf`; apply Money rate each month)
- [X] T015 [US1] Run `php artisan test tests/Unit/Domain/CompoundGainCalculatorTest.php tests/Unit/Domain/MoneyTest.php tests/Unit/Domain/CivilMonthAnniversaryTest.php` and confirm PASS

**Checkpoint**: MVP domain gain works without tax/HTTP

---

## Phase 5: User Story 3 - Imposto só sobre o ganho (Priority: P1)

**Goal**: IR só no ganho; 22,5% / 18,5% / 15%; exemplo 45 / 1155.

**Independent Test**: 100000/120000 cents &lt;1y → tax 4500 net 115500; ganho 0 → tax 0.

### Tests for User Story 3 ⚠️ FAIL first

- [X] T016 [P] [US3] Write `tests/Unit/Domain/WithdrawalTaxCalculatorTest.php` (45/37/30 brackets via `addYearsNoOverflow`; exact 1y and 2y → 18.5%; gain 0 → tax 0)
- [X] T017 [US3] Run `php artisan test tests/Unit/Domain/WithdrawalTaxCalculatorTest.php` and confirm FAIL

### Implementation for User Story 3

- [X] T018 [US3] Create `app/Domain/Investment/WithdrawalTaxCalculator.php` (tax only on max(expected−principal,0); rates not caller-supplied; no Strategy)
- [X] T019 [US3] Run `php artisan test tests/Unit/Domain/WithdrawalTaxCalculatorTest.php` and confirm PASS

**Checkpoint**: Canonical README tax example locked

---

## Phase 6: User Story 4 - Ganhos param na data de resgate (Priority: P2)

**Goal**: `effectiveAsOf = min(asOf, withdrawnOn)`.

**Independent Test**: withdrawnOn no passado + asOf posterior → mesmo saldo que avaliar em withdrawnOn.

### Tests for User Story 4 ⚠️ FAIL first

- [X] T020 [P] [US4] Write `tests/Unit/Domain/InvestmentValuationTest.php` (freeze; no withdrawnOn uses asOf; withdrawnOn &lt; created throws)
- [X] T021 [US4] Run `php artisan test tests/Unit/Domain/InvestmentValuationTest.php` and confirm FAIL

### Implementation for User Story 4

- [X] T022 [US4] Create `app/Domain/Investment/InvestmentValuation.php` orchestrating gain + tax on `effectiveAsOf`
- [X] T023 [US4] Run `php artisan test tests/Unit/Domain` and confirm PASS

**Checkpoint**: All four stories independently functional

---

## Phase 7: Polish, validation, reviews, PR

**Purpose**: Gates 5–8. Sem Scribe (nenhum endpoint). Sem alterar `routes/api.php`.

- [X] T024 Run full `php artisan test` including `tests/Feature/Api` (Auth/Health regression). If FAIL → Code Agent only
- [X] T025 Confirm no `Investment` Eloquent model/migration and no new routes (FR-012)
- [X] T026 Code Review per `.cursor/skills/sdd-code-review/SKILL.md` vs `specs/002-gains-tax/architecture-security-plan.md` (fail PR if chained `addMonth()` or float money)
- [X] T027 Security Review per `.cursor/skills/sdd-security-review/SKILL.md` (N/A HTTP; PASS tax rate not from client; no secrets)
- [X] T028 Run `graphify update .` and include `graphify-out/` except `cost.json`
- [X] T029 Assertive commit + PR targeting `development` per `.cursor/skills/sdd-git-pr/SKILL.md` (title e.g. `feat(domain): compound civil-month gains and tax only on profit`)

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup**: immediate
- **Foundational Money**: after T001; **blocks** stories that use Money
- **US2**: after T007 (Money optional for dates-only tests; anniversary has no Money dep — may start after T002)
- **US1**: after T007 and T011
- **US3**: after T007 (Money); independent of US1
- **US4**: after T015 and T019
- **Polish**: after T023; T024 PASS before T026–T029
- Forbidden: review fail → code → PR without re-test

### User Story Dependencies

- **US2 (P1)**: after Setup — calendar only
- **US1 (P1) MVP**: after Money + US2
- **US3 (P1)**: after Money — tax only
- **US4 (P2)**: after US1 + US3

### Within Each User Story

- Tests FAIL before implementation
- No HTTP, no Eloquent Investment

### Parallel Opportunities

- T003 after T001
- T008 can start after T002 (dates only) in parallel with T003 if staffed
- T016 after T007, parallel with US1 implementation if Money exists
- T012 after T011

---

## Parallel Example: User Story 2 + Money tests

```text
Task: Write tests/Unit/Domain/MoneyTest.php
Task: Write tests/Unit/Domain/CivilMonthAnniversaryTest.php
```

Then implement `Money.php` and `CivilMonthAnniversary.php` in different files.

---

## Implementation Strategy

### MVP First (User Stories 2 + 1)

1. Phase 1–2 Money
2. Phase 3 US2 calendar
3. Phase 4 US1 compound gain
4. **STOP and VALIDATE** `php artisan test tests/Unit/Domain/CompoundGainCalculatorTest.php`

### Incremental Delivery

1. Money
2. US2 calendar
3. US1 gain (MVP)
4. US3 tax
5. US4 freeze
6. Full suite + reviews + PR `development`

### Parallel Team Strategy

One implementer expected. Test Agent writes all FAILING `tests/Unit/Domain/*` first if splitting.

---

## Notes

- State after this file: `ARCHITECTURE_SECURITY_PLAN_READY` until Test Agent finishes first FAILING tests → `TESTS_CREATED`
- Do not add Strategy, Repository, or `GET /api/investments`
- Do not regenerate Scribe
- `REVIEW_CHANGES_REQUESTED` → Code → Test → `TESTS_PASSED` → Code Review — never T029 directly
- Follow `specs/002-gains-tax/quickstart.md` for the case table
