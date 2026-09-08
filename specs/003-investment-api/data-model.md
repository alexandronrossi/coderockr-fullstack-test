# Data Model: Investimentos

## Investment

Tabela nova `investments`.

| Campo | Tipo | Regras | Origem |
|-------|------|--------|--------|
| id | bigint PK | auto | servidor |
| user_id | FK users | required, index; **não fillable** | `$request->user()->id` na criação |
| amount_cents | unsigned int | &gt; 0 | `Money::fromDecimalString(amount)` |
| created_on | date | ≤ hoje; ≥ não se aplica futuro | body `created_on` validado |
| withdrawn_on | date nullable | null = ativo; se set, ≥ `created_on` e ≤ hoje | só `WithdrawInvestment` |
| timestamps | datetime | | servidor (auditoria; **não** são a data civil de rendimento) |

**Fillable**: nenhum campo de controle. Serviço atribui atributos explicitamente (`user_id`, `amount_cents`, `created_on`). Nunca `Investment::create($request->all())`.

**Hidden**: nenhum secret; não persistir token.

**Casts**: `created_on` / `withdrawn_on` → `date`; `amount_cents` → `integer`.

**Atributos derivados (não colunas)**:

| Conceito | Regra |
|----------|--------|
| status | `withdrawn_on === null` → `active`; senão `withdrawn` |
| principal | `Money::fromCents(amount_cents)` |
| avaliação | `InvestmentValuation::evaluate(...)` |

**Relação**: `belongsTo(User)`; `User::investments()` hasMany.

**Scope**: `visibleTo(User $user)` — Admin vê todos; senão `user_id = $user->id`.

### Factory

- default: `user_id` via `User::factory()`, amount 100000 cents, `created_on` = today, `withdrawn_on` null
- `withdrawn()`: `withdrawn_on` = `created_on` (ou data explícita)
- `forUser(User)`: associa dono

## User (existente)

Sem nova coluna. Acrescentar `investments(): HasMany`. `role` continua **não** fillable.

## Evaluation (não persistida)

Mesmo contrato de `002-gains-tax`: `expectedBalance`, `gain`, `tax`, `net`, `rate`, `completeMonths`, `effectiveAsOf`. Gravado só `withdrawn_on`; os valores monetários da resposta são recalculados.

## Validation rules

**Store**: `amount` required, regex `^\d+\.\d{2}$`, não `0.00`; `created_on` required, `date_format:Y-m-d`, `before_or_equal:today`. Demais chaves ignoradas.

**Index**: `page` optional integer min 1; `per_page` optional integer min 1 max 100. `user_id` **não** entra nas rules (ignorado).

**Withdraw**: `withdrawn_on` required, `date_format:Y-m-d`, `before_or_equal:today`. Comparação com `created_on` no service (precisa do registro). Extra `tax` / `net` ignorados.

## State transitions

```text
(nenhum) --create--> active (withdrawn_on null)
active --withdraw válido--> withdrawn (withdrawn_on set)
withdrawn --withdraw--> recusa 422 (sem mudança)
active --withdraw data < created_on ou futuro--> recusa 422 (permanece active)
```

Não há edição de amount/created_on, nem exclusão, nem retorno a active.

## Out of scope

SPA, cadastro de usuários, filtro Admin por dono, saque parcial, coluna `expected_balance`.
