# Implementation Plan: Ganho composto no dia civil e imposto no resgate

**Branch**: `feat/gains-tax` (`002-gains-tax`) | **Date**: 2026-09-08 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/002-gains-tax/spec.md`

**Note**: This template is filled in by the `/speckit-plan` command; its definition describes the execution workflow.

## Summary

Calcular, só no domínio, o saldo esperado (composto 0,52% por mês civil completo, aniversário a partir da data original com clamp no último dia do mês) e o imposto de resgate só sobre o ganho (22,5% / 18,5% / 15%). Sem HTTP, sem model Eloquent de investimento, sem SPA.

Abordagem: value object de dinheiro em centavos; serviço de aniversários civis; calculadora de ganho composto; calculadora de IR; fachada de avaliação que congela na data de resgate. Testes unitários primeiro.

## Technical Context

**Language/Version**: PHP 8.3, Laravel 13 (host; domínio sem Framework no núcleo além de DateTime/Carbon já no projeto)

**Primary Dependencies**: `laravel/framework` (Carbon disponível); **nenhuma** lib nova de money/tax

**Storage**: N/A nesta etapa (sem migration de investments)

**Testing**: PHPUnit 12, testes unitários em `tests/Unit/Domain/` (não precisam de `RefreshDatabase`)

**Target Platform**: Regras reutilizáveis pela API futura; esta entrega é biblioteca interna

**Project Type**: Domain module inside existing Laravel app

**Performance Goals**: Avaliação de um investimento em tempo desprezível (SC-005: suíte &lt; 2 min)

**Constraints**: Sem float para dinheiro; sem `+1 month` encadeado; sem endpoints novos (Scribe inalterado); sem secrets

**Scale/Scope**: Um principal, um calendário, três alíquotas; dezenas de casos de teste

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Gate | Status | Notes |
|------|--------|--------|
| I. Specification-first | PASS | `specs/002-gains-tax/spec.md` |
| II. Architecture before tests | PASS | Este plan + `architecture-security-plan.md` |
| III. Test-first | PASS | Test Agent antes do Code Agent |
| IV. No skipped gates | PASS | Estado final deste comando: `ARCHITECTURE_SECURITY_PLAN_READY` |
| V. Server-side authz | PASS | Sem rotas; futuras APIs não aceitarão ganho/IR do cliente (documentado) |
| SOLID | PASS | VO + serviços de domínio estreitos |
| Strategy | PASS | **Não** usar Strategy: uma política de ganho e uma tabela fixa de IR |
| Scribe | PASS | Nenhum endpoint novo → não regenerar docs |
| Graphify | PASS | Rebuild no Git/PR |
| Secrets | PASS | Sem credenciais |

**Post-design re-check**: Sem violação. Complexity Tracking vazio.

## Project Structure

### Documentation (this feature)

```text
specs/002-gains-tax/
├── plan.md
├── architecture-security-plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
└── tasks.md                      # /speckit-tasks
```

### Source Code (repository root)

```text
app/Domain/Investment/
├── Money.php                         # novo — centavos, 2 casas
├── CivilMonthAnniversary.php         # novo — add N months from original + clamp
├── CompoundGainCalculator.php        # novo — 0.52% por mês completo
├── WithdrawalTaxCalculator.php       # novo — faixas de IR
├── InvestmentValuation.php           # novo — orquestra + freeze na data de resgate
└── InvalidInvestmentDate.php         # novo — asOf < createdOn

tests/Unit/Domain/
├── MoneyTest.php
├── CivilMonthAnniversaryTest.php
├── CompoundGainCalculatorTest.php
├── WithdrawalTaxCalculatorTest.php
└── InvestmentValuationTest.php
```

**Structure Decision**: Módulo `app/Domain/Investment/` sem controllers. Auth (`app/Http`, `app/Models/User.php`) **não** muda. `routes/api.php` **não** muda.

## Complexity Tracking

> Sem violações a justificar.
