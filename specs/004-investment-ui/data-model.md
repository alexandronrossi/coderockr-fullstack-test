# Data Model: Interface (visão cliente)

O frontend **não** persiste investimentos. Espelha o JSON da API (`003-investment-api`). Tipos TypeScript em `frontend/src/types/api.ts`.

## User (perfil na sessão)

| Campo | Tipo UI | Origem | Notas |
|-------|---------|--------|--------|
| id | number | `POST /login` / `GET /user` | |
| name | string | idem | |
| email | string | idem | |
| role | `'admin' \| 'owner'` | idem | **Informativo**; não autoriza sozinho |
| password | — | nunca | Nunca armazenar |

## Session

| Campo | Storage | Regras |
|-------|---------|--------|
| token | `sessionStorage` | Bearer; limpar no logout e em 401 global |
| user | `sessionStorage` (JSON) | Copiar do login; refresh opcional via `GET /user` |

## Investment (visão)

| Campo | Tipo UI | Notas |
|-------|---------|--------|
| id | number | |
| owner | `{ id, name, email }` | Sem password |
| amount | string (`1000.00`) | Exibir; não parsear para float de cálculo |
| created_on | string `Y-m-d` | |
| status | `'active' \| 'withdrawn'` | |
| withdrawn_on | string \| null | |
| expected_balance | string | Do servidor |
| gain | string | Do servidor |
| tax | string | Do servidor |
| net | string | Do servidor |
| rate | string | Opcional na UI |
| complete_months | number | Opcional na UI |

**Proibido no cliente**: derivar `expected_balance` / `tax` / `net` a partir de `amount` e datas.

## InvestmentPage

| Campo | Tipo |
|-------|------|
| data | Investment[] |
| meta.current_page | number |
| meta.per_page | number |
| meta.total | number |
| meta.last_page | number (se presente) |

## Formulários (só input)

### Login

- `email`, `password` — required
- Sem campos `role` / `user_id` na UI

### Create

- `amount` — string duas casas, &gt; `0.00`
- `created_on` — `Y-m-d`, ≤ hoje
- Sem `user_id`, `status`, `expected_balance`, `tax`

### Withdraw

- `withdrawn_on` — `Y-m-d`, ≤ hoje, ≥ `created_on` (validação UX; servidor confirma)
- Sem `tax`, `net`, `rate`, `expected_balance`

## State transitions (UI)

```text
logged_out --login ok--> logged_in (token)
logged_in --logout / 401--> logged_out
investment active --withdraw 200--> withdrawn (dados do response)
```

## Out of scope

Persistência local de investimentos, cadastro de usuário, edição de amount, cálculo de IR offline.
