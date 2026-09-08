# Data Model: Ganho composto e imposto

Nenhuma tabela nova. Tipos de domínio (não Eloquent).

## Money

| Campo | Tipo | Regras |
|-------|------|--------|
| cents | int ≥ 0 nesta etapa | 1000,00 → 100000 centavos |
| scale | 2 casas | apresentação |

Operações: `fromDecimalString` / `fromCents`; `add`; `subtract` (não negativo no ganho/imposto); `percentage(bps)` com half-up para centavos.

## Civil anniversary

| Entrada | Significado |
|---------|-------------|
| createdOn | Date (Y-m-d), dia civil da criação |
| periodIndex N | Inteiro ≥ 0; N=0 é a própria criação |

Saída: data do N-ésimo aniversário, clamp no último dia do mês se o dia original não existir.

## Compound gain input

| Campo | Regras |
|-------|--------|
| principal | Money &gt; 0 (assumido; API futura valida) |
| createdOn | date |
| asOf | date ≥ createdOn senão InvalidInvestmentDate |
| rate | 0,52% fixo |

Saída: `completeMonths` (int), `expectedBalance` (Money), `gain` (expected − principal ≥ 0).

## Withdrawal tax input

| Campo | Regras |
|-------|--------|
| principal | Money |
| expectedBalance | Money ≥ principal |
| createdOn | date |
| taxAsOf | date ≥ createdOn (data em que a idade é medida) |

Saída: `gain`, `rate` (0.225 \| 0.185 \| 0.15), `tax`, `net` (expected − tax).

## Investment valuation (orquestração)

| Campo | Regras |
|-------|--------|
| principal | Money |
| createdOn | date |
| asOf | date de referência pedida |
| withdrawnOn | date opcional |

`effectiveAsOf = withdrawnOn !== null ? min(asOf, withdrawnOn) : asOf`.

Ganho usa `effectiveAsOf`. Imposto usa idade até `effectiveAsOf` (resgate já ocorrido) ou até `asOf` quando se **avalia** um resgate ainda não persistido (mesma data).

## State

```text
ativo (sem withdrawnOn): rende até asOf
resgatado: rende até withdrawnOn (mesmo se asOf for depois)
```

Sem persistir `status`; o próximo PR mapeia isso para `active|withdrawn`.
