# Implementation Plan: Criar, listar, detalhar e resgatar investimentos

**Branch**: `feat/investment-api` (`003-investment-api`) | **Date**: 2026-09-08 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/003-investment-api/spec.md`

**Note**: This template is filled in by the `/speckit-plan` command; its definition describes the execution workflow.

## Summary

API JSON autenticada para persistir investimentos (dono = usuário do token, data civil, valor em centavos), listar com paginação, detalhar com saldo esperado e resgatar o valor integral com IR só sobre o ganho. Admin vê e resgata todos; Owner só os próprios. Cálculo **reutiliza** `InvestmentValuation` — o cliente nunca envia saldo, imposto, status nem `user_id`. Sem SPA.

Abordagem: model Eloquent + policy + query `visibleTo` (404 no IDOR, sem vazar existência); application services; Form Requests allowlist; Resource JSON; `lockForUpdate` no resgate; Scribe regenerado.

## Technical Context

**Language/Version**: PHP 8.3, Laravel 13

**Primary Dependencies**: `laravel/framework` ^13.17, `laravel/sanctum` ^4.3, `knuckleswtf/scribe` ^5.11; domínio `app/Domain/Investment/*` (já entregue). Sem lib nova.

**Storage**: SQLite local; tabela `investments` (FK `users`)

**Testing**: PHPUnit 12, `RefreshDatabase` em Feature; unitários de domínio já existentes não mudam de fórmula

**Target Platform**: API JSON (`Authorization: Bearer`); SPA em spec posterior

**Project Type**: Web service (Laravel root; sem `frontend/` nesta feature)

**Performance Goals**: CRUD/resgate em tempo de request local; lista paginada (default 15, máx. 100)

**Constraints**: Sem float para dinheiro; sem `request->all()`; sem `find($id)` sem policy/scope; Scribe obrigatório; PR contra `development`

**Scale/Scope**: Desafio fullstack; 4 endpoints novos; dois papéis; dezenas de testes Feature (IDOR, mass assignment, paginação, freeze)

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Gate | Status | Notes |
|------|--------|--------|
| I. Specification-first | PASS | `specs/003-investment-api/spec.md` |
| II. Architecture before tests | PASS | Este plan + `architecture-security-plan.md` |
| III. Test-first | PASS | Test Agent depois deste comando |
| IV. No skipped gates | PASS | Estado: `ARCHITECTURE_SECURITY_PLAN_READY` |
| V. Server-side authz | PASS | Policy + `visibleTo`; Admin/Owner no servidor; `user.role` no JSON não autoriza |
| SOLID | PASS | Controllers invocáveis finos; serviços de aplicação; domínio intacto |
| Strategy | PASS | **Não** Strategy: papéis são autorização (Policy), não algoritmos de ganho |
| Repository | PASS | **Não** Repository extra: um Eloquent + scope |
| Scribe | PASS | Endpoints novos → `php artisan scribe:generate` + `public/docs` |
| Graphify 30.2 | PASS | Consulta feita; rebuild no Git/PR |
| Secrets | PASS | Sem secrets novos; token não logado |

**Post-design re-check**: Sem violação. Complexity Tracking vazio.

## Project Structure

### Documentation (this feature)

```text
specs/003-investment-api/
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
app/
├── Domain/Investment/            # existente — NÃO alterar fórmulas
│   └── InvestmentAlreadyWithdrawn.php   # novo — invariante de resgate
├── Http/
│   ├── Controllers/Api/
│   │   ├── IndexInvestmentController.php
│   │   ├── ShowInvestmentController.php
│   │   ├── StoreInvestmentController.php
│   │   └── WithdrawInvestmentController.php
│   ├── Requests/Api/
│   │   ├── IndexInvestmentRequest.php
│   │   ├── StoreInvestmentRequest.php
│   │   └── WithdrawInvestmentRequest.php
│   └── Resources/
│       └── InvestmentResource.php
├── Models/
│   ├── User.php                  # + investments()
│   └── Investment.php            # novo
├── Policies/InvestmentPolicy.php # novo
└── Services/Investment/
    ├── CreateInvestment.php
    ├── ListInvestments.php
    ├── ShowInvestment.php
    └── WithdrawInvestment.php

database/
├── migrations/xxxx_create_investments_table.php
└── factories/InvestmentFactory.php

routes/api.php                    # 4 rotas no grupo auth:sanctum
bootstrap/app.php                 # mapear exceções de domínio → 422
app/Providers/AppServiceProvider.php  # limiter investments (opcional, 60/min)

tests/Feature/Api/Investments/    # novos
```

**Structure Decision**: Mesmo estilo da auth (invocáveis + application services). Sem pasta `frontend/`. Domínio de ganho/IR só é **chamado**, não reimplementado.

## Complexity Tracking

> Sem violações a justificar.
