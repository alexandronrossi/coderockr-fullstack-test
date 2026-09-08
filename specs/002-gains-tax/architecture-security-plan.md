# Architecture-Security Plan: Ganho composto no dia civil e imposto no resgate

**Branch**: `feat/gains-tax` (`002-gains-tax`) | **Date**: 2026-09-08 | **Spec**: [spec.md](./spec.md)

**Agent**: Architecture / Security Agent
**State**: ARCHITECTURE_SECURITY_PLAN_READY
**Implementation**: this agent MUST NOT implement the solution

**Input**: Feature specification from `/specs/002-gains-tax/spec.md`

## Architecture

```text
Test / (futuro) Application Service de investimento
        ↓
InvestmentValuation
        ↓
   ┌────┴────┐
   ↓         ↓
CompoundGainCalculator    WithdrawalTaxCalculator
   ↓
CivilMonthAnniversary
   ↓
Money (centavos)
```

**Boundaries**:
- **Domain**: único lugar da taxa 0,52%, faixas de IR e regra de calendário.
- **HTTP / Eloquent**: **não** nesta etapa. `routes/api.php` e `User` intocados.
- **API futura**: receberá principal + datas; **nunca** ganho, saldo esperado ou IR enviados pelo cliente.

**Não fazer**: Strategy, Repository, model `Investment`, Scribe, Sanctum.

## SOLID

| Princípio | Aplicação |
|-----------|-----------|
| S | Money só dinheiro; Anniversary só datas; Gain só composto; Tax só IR; Valuation só orquestra |
| O | Nova alíquota = constante/tabela no Tax calculator (uma política). Nova taxa de rendimento = constante no Gain (uma política) |
| L | Sem hierarquia de calculadoras |
| I | Sem interface gorda; se surgir interface, um método `evaluate` por calculadora |
| D | Valuation depende dos calculadores concretos (uma implementação cada); interface extra agora é YAGNI |

Controllers inexistentes → nada de regra HTTP.

## Design Patterns

- **Value Object**: `Money`.
- **Não** Strategy: uma política de ganho, uma de IR (tabela de três faixas). Constituição: pattern só com variação real de algoritmo.
- **Não** Repository / Factory de persistência.

## Security Architecture

**Authentication / Authorization / IDOR**: N/A (sem rotas, sem `{id}`). Testes de segurança HTTP não se aplicam; a ameaça futura é o cliente postar `expected_balance` — documentado para o PR de API.

**Validação**: datas e centavos ≥ 0 no VO; `asOf < createdOn` → `InvalidInvestmentDate`. Principal ≤ 0 não é o foco desta etapa (assumido &gt; 0).

**Banco / RLS / mass assignment / injection**: N/A.

**Secrets**: nenhum.

**XSS / CSRF**: N/A (sem HTML/cookie).

**Privilege escalation**: N/A.

**Concurrency / locking**: cálculo puro, sem escrita. Sem race.

**Cache / filas**: não.

**Erros**: exceção de domínio sem stack para o cliente (não há cliente HTTP). Testes assertam o tipo.

**Data exposure**: objetos de avaliação não incluem secrets.

**Frontend permission**: N/A.

## API Inventory

Nenhum endpoint. Health/login/logout permanecem como em `001-user-auth`. **Não** rodar Scribe.

## File-by-file Analysis

```text
FILE: app/Domain/Investment/Money.php

RESPONSIBILITY:
Valor monetário em centavos; arredondamento half-up.

CURRENT_PROBLEM:
Arquivo novo. Hoje não há tipo de dinheiro (risco de float na API futura).

PROPOSED_CHANGE:
fromCents, fromDecimalString, toDecimalString, add, subtract, cents(); applyRateHalfUp(int $numerator, int $denominator) para 10052/10000.

ARCHITECTURE:
Base do domínio.

SOLID:
S.

DESIGN_PATTERN:
Value Object.

SECURITY:
Imutável; rejeitar centavos negativos na construção desta etapa.

DATA_ACCESS:
Nenhum.

AUTHORIZATION:
N/A.

INPUT:
int cents ou string decimal com 2 casas.

OUTPUT:
int cents / string.

DEPENDENCIES:
Nenhuma (PHP).

LINES:
Novo arquivo.

TESTS:
MoneyTest — 1000.00 → 100000; 100000 * 1.0052 → 100520.

RISKS:
Parse de locale com vírgula: aceitar só ponto interno nos testes; decimal string com ponto.
```

```text
FILE: app/Domain/Investment/CivilMonthAnniversary.php

RESPONSIBILITY:
Data do N-ésimo aniversário a partir da criação original + clamp.

CURRENT_PROBLEM:
Novo. Carbon `addMonths(1)` em loop quebraria FR-005.

PROPOSED_CHANGE:
Método anniversary(CarbonImmutable $created, int $n): CarbonImmutable usando addMonthsNoOverflow($n) no clone da original. n=0 retorna created.

ARCHITECTURE:
Calendário isolado do dinheiro.

SOLID:
S.

DESIGN_PATTERN:
Nenhum.

SECURITY:
Datas civis, sem timezone de negócio (date only, startOfDay).

DATA_ACCESS:
N/A.

AUTHORIZATION:
N/A.

INPUT:
created, n ≥ 0.

OUTPUT:
date.

DEPENDENCIES:
Carbon\CarbonImmutable.

LINES:
Novo.

TESTS:
CivilMonthAnniversaryTest — todos os casos US2 do spec.

RISKS:
Usar addMonth() encadeado — proibido no code review.
```

```text
FILE: app/Domain/Investment/CompoundGainCalculator.php

RESPONSIBILITY:
Contar meses completos e aplicar composto 0,52%.

CURRENT_PROBLEM:
Novo.

PROPOSED_CHANGE:
Loop n=1,2,... enquanto anniversary(created, n) <= asOf; cada passo Money aplica 0,52%. asOf < created → throw InvalidInvestmentDate.

ARCHITECTURE:
Domain service.

SOLID:
S; usa Anniversary + Money.

DESIGN_PATTERN:
Nenhum (não Strategy).

SECURITY:
Não lê request. Principal não vem de mass assignment.

DATA_ACCESS:
Nenhum.

AUTHORIZATION:
N/A.

INPUT:
Money, created, asOf.

OUTPUT:
DTO/array completeMonths, expectedBalance, gain.

DEPENDENCIES:
CivilMonthAnniversary, Money.

LINES:
Novo.

TESTS:
CompoundGainCalculatorTest — US1 + mês incompleto.

RISKS:
Comparar datetime com hora; normalizar para data.
```

```text
FILE: app/Domain/Investment/WithdrawalTaxCalculator.php

RESPONSIBILITY:
IR só sobre o ganho; três faixas.

CURRENT_PROBLEM:
Novo.

PROPOSED_CHANGE:
gain = max(expected - principal, 0); idade via addYearsNoOverflow(1) e (2); tax = round half up gain * rate; net = expected - tax.

ARCHITECTURE:
Domain service.

SOLID:
S. O= tabela interna de faixas, não hierarquia.

DESIGN_PATTERN:
Não Strategy.

SECURITY:
Alíquota nunca vem do chamador.

DATA_ACCESS:
N/A.

AUTHORIZATION:
N/A.

INPUT:
principal, expected, created, taxAsOf.

OUTPUT:
gain, rate, tax, net.

DEPENDENCIES:
Money, CarbonImmutable.

LINES:
Novo.

TESTS:
WithdrawalTaxCalculatorTest — 45/37/30 e ganho 0 (SC-001, US3).

RISKS:
Usar diffInYears float do Carbon (pode errar no aniversário) — preferir comparar asOf com created->addYearsNoOverflow(k).
```

```text
FILE: app/Domain/Investment/InvestmentValuation.php

RESPONSIBILITY:
effectiveAsOf = min(asOf, withdrawnOn?); chama Gain + Tax.

CURRENT_PROBLEM:
Novo.

PROPOSED_CHANGE:
Orquestração FR-010.

ARCHITECTURE:
Application-lite / fachada de domínio.

SOLID:
S (sem fórmula duplicada).

DESIGN_PATTERN:
Facade leve, não God object.

SECURITY:
Não persiste; freeze impede “saldo vivo” após saque quando a API existir.

DATA_ACCESS:
N/A.

AUTHORIZATION:
N/A.

INPUT:
principal, created, asOf, withdrawnOn nullable.

OUTPUT:
valuation completa.

DEPENDENCIES:
CompoundGainCalculator, WithdrawalTaxCalculator.

LINES:
Novo.

TESTS:
InvestmentValuationTest — freeze e asOf após R.

RISKS:
Usar asOf no imposto e withdrawnOn no ganho de forma inconsistente — ambos effectiveAsOf.
```

```text
FILE: app/Domain/Investment/InvalidInvestmentDate.php

RESPONSIBILITY:
Exceção quando asOf ou withdrawnOn &lt; createdOn.

CURRENT_PROBLEM:
Novo.

PROPOSED_CHANGE:
extends DomainException (ou InvalidArgumentException).

ARCHITECTURE:
Erro de domínio.

SOLID:
S.

DESIGN_PATTERN:
N/A.

SECURITY:
Mensagem sem stack para HTTP futuro.

DATA_ACCESS:
N/A.

AUTHORIZATION:
N/A.

INPUT/OUTPUT:
N/A.

DEPENDENCIES:
SPL.

LINES:
Novo.

TESTS:
Assert throw nos calculadores.

RISKS:
Nenhum.
```

```text
FILE: tests/Unit/Domain/*.php

RESPONSIBILITY:
Testes ANTES do código (Test Agent). PHPUnit puro / Tests\TestCase sem DB.

CURRENT_PROBLEM:
Só exemplos e Auth.

PROPOSED_CHANGE:
Cinco arquivos listados no plan. Casos do quickstart.

ARCHITECTURE:
Gate III.

SECURITY:
Não HTTP; cobre invariantes de IR (não taxar principal).

DATA_ACCESS:
Nenhum.

AUTHORIZATION:
N/A.

INPUT/OUTPUT:
Dados de tabela.

DEPENDENCIES:
PHPUnit.

LINES:
Novos.

TESTS:
São os testes.

RISKS:
Acoplar teste a Carbon timezone — usar datas explícitas Y-m-d.
```

```text
FILE: routes/api.php
FILE: app/Models/User.php
FILE: public/docs/**

RESPONSIBILITY:
API existente.

CURRENT_PROBLEM:
Nenhum para esta feature.

PROPOSED_CHANGE:
Nenhuma. HealthTest deve continuar PASS.

ARCHITECTURE:
Isolamento.

SOLID:
N/A.

DESIGN_PATTERN:
N/A.

SECURITY:
Não alargar superfície HTTP.

DATA_ACCESS:
N/A.

AUTHORIZATION:
N/A.

INPUT/OUTPUT:
N/A.

DEPENDENCIES:
N/A.

LINES:
Não modificar.

TESTS:
Suíte completa no validate inclui Health + Auth regressão.

RISKS:
PR misturar model Investment — fora de escopo (FR-012).
```

## Line-by-line Analysis

Nenhum arquivo de produção existente é editado. Arquivos novos:

```text
FILE: app/Domain/Investment/Money.php
LINES 1–80:
Responsabilidade: centavos imutáveis; half-up na taxa mensal.
Dependencies: nenhuma.
Security: sem float; sem input HTTP.
Tests: MoneyTest.

FILE: app/Domain/Investment/CivilMonthAnniversary.php
LINES 1–40:
Responsabilidade: addMonthsNoOverflow a partir da original.
Dependencies: CarbonImmutable.
Security: date-only.
Tests: 31/jan→28/fev; 31/jan+2→31/mar; bissextos; 31/mar→30/abr.

FILE: app/Domain/Investment/CompoundGainCalculator.php
LINES 1–60:
Responsabilidade: contar N e composto.
Dependencies: Anniversary, Money.
Security: throw se asOf < created.
Tests: 0 meses, 1 mês 1005.20, incompleto, 2 meses.

FILE: app/Domain/Investment/WithdrawalTaxCalculator.php
LINES 1–70:
Responsabilidade: faixas 22.5 / 18.5 / 15; só ganho.
Dependencies: Money, CarbonImmutable.
Security: rate não é parâmetro do cliente.
Tests: 45, 37, 30, 0.

FILE: app/Domain/Investment/InvestmentValuation.php
LINES 1–50:
Responsabilidade: min(asOf, withdrawnOn) + delegar.
Dependencies: os dois calculadores.
Security: freeze de saldo.
Tests: withdrawnOn no passado.

FILE: app/Domain/Investment/InvalidInvestmentDate.php
LINES 1–15:
Responsabilidade: sinalizar data inválida.
Dependencies: DomainException.
Security: mensagem curta.
Tests: expectException.

FILE: tests/Unit/Domain/MoneyTest.php
FILE: tests/Unit/Domain/CivilMonthAnniversaryTest.php
FILE: tests/Unit/Domain/CompoundGainCalculatorTest.php
FILE: tests/Unit/Domain/WithdrawalTaxCalculatorTest.php
FILE: tests/Unit/Domain/InvestmentValuationTest.php
LINES 1–XX:
Responsabilidade: FAIL first, depois PASS.
Dependencies: PHPUnit.
Security: IR não come principal.
Tests: (estes arquivos).
```

Proibido no Code Agent (review deve falhar se aparecer):

```text
FILE: qualquer
LINE *:
Problema: $date->addMonth() em loop ou modify('+1 month') encadeado.
Alteração: sempre original + N via NoOverflow.
Motivo: FR-005 / SC-003.
```

## Required Tests

Criar **antes** do código (`TESTS_CREATED`):

**Money**
- 1000.00 ↔ 100000 cents
- aplicar 0,52% → 1005.20
- rejeitar cents negativos

**CivilMonthAnniversary**
- n=0 identidade
- 2025-01-31 + 1 → 2025-02-28 (2025 não bissexto)
- 2025-01-31 + 2 → 2025-03-31
- 2024-01-29 + 1 → 2024-02-29
- 2024-01-30 + 1 e 2024-01-31 + 1 → 2024-02-29
- 2025-03-31 + 1 → 2025-04-30

**CompoundGainCalculator**
- asOf = created → 0 meses, ganho 0
- 1 mês completo 1000 → 1005.20
- 2 meses compostos com half-up mensal
- asOf um dia antes do 1º aniversário → 0 meses
- asOf &lt; created → InvalidInvestmentDate

**WithdrawalTaxCalculator**
- 1000 / 1200 / &lt;1a → tax 45 net 1155
- mesmo, idade 1a inclusive e 2a inclusive → 37
- &gt;2a → 30
- expected = principal → tax 0 net = principal

**InvestmentValuation**
- withdrawnOn anterior a asOf → saldo do withdrawnOn
- sem withdrawnOn → usa asOf

Não enfraquecer testes de Auth/Health.

## Remaining Risks

- Próximo PR (model/API) pode reimplementar a fórmula no controller — o review desse PR deve exigir estes serviços.
- `addYearsNoOverflow` em 29/fev: documentado no teste de idade se um caso cruzar 29/fev.
- Arredondamento mensal pode divergir de uma planilha que só arredonda no fim — o research fixa mensal; testes pinam os números.
- Sem persistência, “já resgatado” é só parâmetro `withdrawnOn`.

---

**Handoff**: Test Agent cria `tests/Unit/Domain/*`. Code Agent só depois de `TESTS_CREATED`. Não implementar neste gate.
