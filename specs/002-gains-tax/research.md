# Research: Ganho composto e imposto

## 1. Onde vive o cálculo

**Decision**: Classes em `app/Domain/Investment/`, invocadas só por testes nesta etapa. Sem Eloquent `Investment`, sem HTTP.

**Rationale**: FR-012 e o plano de PRs (domínio antes do model). A API futura injeta estes serviços; não copiar a fórmula nos controllers.

**Alternatives considered**: Trait no model futuro (mistura persistência e regra). Stored procedure (SQLite, difícil de testar). Lib `brick/money` (dependência extra para um único currency).

## 2. Representação de dinheiro

**Decision**: Value object `Money` em **centavos inteiros** (`int`). Entrada/saída em string ou int de centavos; apresentação `number_format` 2 casas. Sem `float`.

**Rationale**: `1000 * 1.0052` em float é arriscado; o spec pede duas casas no resultado apresentado.

**Alternatives considered**: `BCMath` strings (ok, mais verboso). `float` (rejeitado).

## 3. Compostagem e arredondamento

**Decision**: Taxa **0,52%** = multiplicar o saldo em centavos por `10052 / 10000` a cada período completo, com `PHP_ROUND_HALF_UP` para o centavo **após cada mês**. Resultado final já está em centavos.

**Rationale**: Cada “pagamento” do README é um evento mensal; arredondar por período evita arrastar frações invisíveis. 1000,00 × 1,0052 = 1005,20 exatamente.

**Alternatives considered**: Precisão extra até o fim e um único round (pode divergir do “pago todo mês”). Round bankers (atípico para dinheiro de varejo no Brasil).

## 4. Aniversário civil (clamp)

**Decision**: Para o período N (N ≥ 1), `anniversary = originalCreated->addMonthsNoOverflow(N)` (Carbon), **sempre a partir da data original**, nunca a partir do aniversário anterior. Equivale a: ano/mês = original + N meses; dia = `min(diaOriginal, últimoDiaDoMêsAlvo)`.

**Rationale**: Spec US2: 31/jan + 2 meses = 31/mar, não 28/mar. Encadear `addMonthsNoOverflow(1)` N vezes **quebra** a regra.

**Alternatives considered**: `modify('+N months')` do PHP (overflow 31/jan → 2 ou 3/mar). Loop de +1 mês com clamp (viola FR-005).

Contagem de meses completos: maior N ≥ 0 tal que `anniversary(N) <= asOf` e `anniversary(0) = createdOn`. N=0 → zero ganhos.

## 5. Imposto (faixas)

**Decision**: Um calculador com três constantes. Sem Strategy.

```text
asOf < created + 1 year          → 22,5%
created + 1 year ≤ asOf ≤ created + 2 years → 18,5%
asOf > created + 2 years         → 15%
```

Anos civis via `addYearsNoOverflow` a partir da criação (mesmo espírito do mês). Imposto = round half up de `gain_cents * rate`. Líquido = saldo − imposto. Imposto 0 se ganho 0.

**Rationale**: Uma política fixa do README; Strategy inflaria abstração (constituição).

**Alternatives considered**: Strategy por faixa (rejeitado). Tabela no banco (overkill).

## 6. Data inválida e freeze

**Decision**: `asOf < createdOn` → `InvalidInvestmentDate`. Se `withdrawnOn` preenchido, a data efetiva do ganho é `min(asOf, withdrawnOn)`; se `withdrawnOn < createdOn` → mesma exceção.

**Rationale**: Spec permite recusar. Exceção de domínio é testável; a API futura mapeia para 422.

**Alternatives considered**: Retornar ganho 0 silenciosamente (esconde bug de data).

## 7. HTTP / Scribe / Auth

**Decision**: Não alterar rotas. Não `scribe:generate`. Não tocar em User/Sanctum.

**Rationale**: FR-012.

## Resoluções NEEDS CLARIFICATION

Nenhum item do Technical Context ficou em aberto.
