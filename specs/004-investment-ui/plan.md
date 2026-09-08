# Implementation Plan: Interface web de investimentos

**Branch**: `feat/investment-ui` (`004-investment-ui`) | **Date**: 2026-09-08 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/004-investment-ui/spec.md`

**Note**: This template is filled in by the `/speckit-plan` command; its definition describes the execution workflow.

## Summary

SPA autenticada que consome a API já entregue: login, lista paginada, detalhe, criação e resgate. Owner vê só os próprios; Admin vê todos — **só porque a API filtra**. A UI **não** calcula 0,52% nem IR; só exibe JSON do servidor. Esconder botão ≠ autorização.

Abordagem: app React + Vite + TypeScript em `frontend/`, `VITE_API_URL`, Bearer em `sessionStorage`, React Router, cliente HTTP fino, validação de formulário só de UX (servidor manda a verdade), capturas em `screenshots/`, README atualizado. Sem novos endpoints Laravel → **sem** Scribe neste PR.

## Technical Context

**Language/Version**: TypeScript 5.x; Node 20+; PHP/Laravel inalterados nesta feature

**Primary Dependencies**: React 19, Vite 6+, React Router 7, Vitest + Testing Library. Sem lib de “money”/tax no cliente. Sem UI kit pesado (CSS próprio alinhado à estrutura Figma + logo em `public/images`).

**Storage**: N/A no frontend (token em `sessionStorage`; dados só em memória/cache de página)

**Testing**: Vitest + Testing Library em `frontend/`; regressão `php artisan test` no root (API não muda)

**Target Platform**: Browser (desktop + mobile viewport); API em `http://localhost:8000`

**Project Type**: SPA em `frontend/` + API Laravel existente (monorepo)

**Performance Goals**: Login → lista em &lt; 1 min (SC-001); lista/detalhe em tempo de request local

**Constraints**: Sem fórmula de ganho/IR no cliente; `VITE_*` é público; sem secrets; CORS já permite `http://localhost:5173`; PR → `development`; sem alterar `routes/api.php` / domínio

**Scale/Scope**: 5 telas (login, lista, detalhe, criar, resgate implícito no detalhe); 2 papéis informativos; screenshots ≥ 2

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Gate | Status | Notes |
|------|--------|--------|
| I. Specification-first | PASS | `specs/004-investment-ui/spec.md` |
| II. Architecture before tests | PASS | Este plan + `architecture-security-plan.md` |
| III. Test-first | PASS | Test Agent depois deste comando |
| IV. No skipped gates | PASS | Estado: `ARCHITECTURE_SECURITY_PLAN_READY` |
| V. Server-side authz | PASS | Frontend permission ≠ security; role só UI; API já faz IDOR 404 |
| SOLID | PASS | Cliente HTTP / páginas / componentes estreitos; sem Strategy artificial |
| Strategy | PASS | **Não** Strategy Admin/Owner no cliente |
| Scribe | PASS | Nenhum endpoint novo → não regenerar docs |
| Graphify 30.2 | PASS | Consulta feita; rebuild no Git/PR |
| Secrets | PASS | Só `VITE_API_URL` (público); seed passwords só no backend `.env` |

**Post-design re-check**: Sem violação. Complexity Tracking vazio.

## Project Structure

### Documentation (this feature)

```text
specs/004-investment-ui/
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
frontend/                         # novo — SPA isolada
├── package.json
├── vite.config.ts
├── tsconfig.json
├── index.html
├── .env.example                  # VITE_API_URL=http://localhost:8000/api
├── src/
│   ├── main.tsx
│   ├── App.tsx
│   ├── api/
│   │   ├── client.ts             # fetch + Bearer; sem recalcular money
│   │   ├── auth.ts
│   │   └── investments.ts
│   ├── auth/
│   │   ├── session.ts            # sessionStorage token + user
│   │   └── RequireAuth.tsx
│   ├── types/
│   │   └── api.ts                # espelha Resource JSON (somente tipos)
│   ├── pages/
│   │   ├── LoginPage.tsx
│   │   ├── InvestmentsListPage.tsx
│   │   ├── InvestmentDetailPage.tsx
│   │   └── CreateInvestmentPage.tsx
│   ├── components/
│   │   ├── AppShell.tsx          # brand, user, logout
│   │   ├── InvestmentRow.tsx
│   │   ├── Pagination.tsx
│   │   └── WithdrawForm.tsx
│   └── styles/
│       └── tokens.css
└── tests/                        # Vitest (co-located ou src/**/*.test.tsx)

screenshots/                      # ≥2 PNGs (lista + detalhe ou resgate)
README.md                         # build UI + libs + link /docs
.env.example                      # (Laravel) já tem CORS_ALLOWED_ORIGINS; documentar VITE no frontend/.env.example
```

**Structure Decision**: SPA separada em `frontend/` (não misturar com Vite Laravel de `resources/`). API root permanece a fonte de verdade. Sem mudanças em `app/Domain` nem `routes/api.php`.

## Complexity Tracking

> Sem violações a justificar.
