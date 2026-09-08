# Research: API de investimentos

## 1. Camadas

**Decision**: Controller invocável → Application Service → Eloquent + `InvestmentValuation`. Policy Laravel para `view` / `viewAny` / `create` / `withdraw`. Sem Repository. Sem Strategy.

**Rationale**: Constituição (Controller → Application → Domain). Auth já usa invocáveis + um service de login. Ganho/IR já estão no domínio; persistir e autorizar não pedem nova família de algoritmos.

**Alternatives considered**: Resource controller com quatro métodos (aceitável, pior SRP vs invocáveis atuais). Repository interface (YAGNI, um model). Strategy Admin/Owner (são permissões, não estratégias de cálculo).

## 2. Autorização e IDOR (FR-004, FR-005, SC-003)

**Decision**: Scope `Investment::visibleTo($user)`: Admin = sem filtro; qualquer outro papel = `where user_id = $user->id` (fail-closed). `findOrFail` **depois** do scope. Owner que pede ID alheio recebe **404**, igual a ID inexistente — não 403, para não enumerar existência. Policy ainda autoriza o model carregado (defesa em profundidade). Query `user_id` na listagem é **ignorada**.

**Rationale**: Spec: recusar sem revelar dados nem existência para Owner. `Model::find($id)` + 403 vaza que o ID existe.

**Alternatives considered**: 403 Forbidden (vaza existência). Soft-hide só no Resource (inseguro: o load já ocorreu).

## 3. Mass assignment e dono (FR-003)

**Decision**: `user_id`, `withdrawn_on`, `status` **não** fillable. Serviço de criação faz `user_id = $request->user()->id`. Admin também cria só para si (assumption da spec). Campos `expected_balance`, `gain`, `tax`, `net`, `rate`, `role` no body são ignorados (`validated()` só `amount` + `created_on` / `withdrawn_on`).

**Rationale**: Constituição: cliente nunca define `user_id` / `status`.

**Alternatives considered**: Admin escolhe `user_id` (spec rejeitou). Coluna `status` persistida (redundante com `withdrawn_on`).

## 4. Dinheiro e datas

**Decision**: Persistir `amount_cents` (int). Input JSON `amount` como **string** `^\d+\.\d{2}$` (mesmo contrato do domínio). `created_on` e `withdrawn_on` tipo `date` (civil). “Hoje” = `CarbonImmutable::today()` no timezone da app (`APP_TIMEZONE`, default UTC no skeleton — documentar no quickstart). Avaliação: ativo → `asOf = today`, `withdrawnOn = null`; resgatado → `withdrawnOn` persistido. Resgate: `evaluate(principal, created_on, withdrawn_on, null)` **antes** de gravar, depois persiste `withdrawn_on`.

**Rationale**: Evitar float JSON. Reuso de `Money` / `InvestmentValuation`.

**Alternatives considered**: Decimal SQL (ainda converte). Aceitar number JSON (float). Recalcular imposto no Resource sem passar pelo valuation (duplicaria regra).

## 5. Exemplo 1000 / 1200 / 45 no HTTP

**Decision**: O exemplo canônico de IR (saldo 1200 com idade &lt; 1 ano) **permanece** nos unitários de `WithdrawalTaxCalculator`. Feature HTTP **não** fabrica saldo 1200: o saldo vem do composto real (ex. 1000 → 1005.20 em um mês civil). Testes Feature usam `Carbon::setTestNow` para aniversários.

**Rationale**: 1000 × 1,0052^n = 1200 implica ~35 meses e idade &gt; 2 anos (alíquota 15%), incompatível com 22,5%. Inventar 1200 na API violaria FR-007.

**Alternatives considered**: Stub do valuation nos Feature tests (enfraquece o contrato HTTP). Persistência de `expected_balance` (proibido).

## 6. Paginação (FR-009)

**Decision**: `page` (default 1) e `per_page` (default 15, max 100) via `IndexInvestmentRequest`. `LengthAwarePaginator`. Ordenação `created_at desc, id desc`. JSON padrão Laravel Resource collection (`data` + `meta.total` / `current_page` / `per_page`). Página &lt; 1 ou `per_page` inválido → 422. Página além da última → `data: []` com `total` correto (não vaza outros donos).

**Rationale**: Spec pede páginas e teto. Owner scoped antes de paginar.

**Alternatives considered**: Cursor pagination (desnecessário no desafio). Sem teto (abuso).

## 7. Concorrência de resgate (FR-013)

**Decision**: `DB::transaction` + `lockForUpdate()` na linha. Se `withdrawn_on` já preenchido → `InvestmentAlreadyWithdrawn` → 422. Não há unique parcial SQLite confiável o bastante para substituir o lock neste desafio.

**Rationale**: `if (!withdrawn)` sem lock perde o race de dois POSTs.

**Alternatives considered**: Unique `(id) WHERE withdrawn_on IS NOT NULL` (SQLite limitado). Fila (overkill).

## 8. HTTP status

| Caso | Status |
|------|--------|
| Criado | 201 + InvestmentResource |
| Lista / detalhe | 200 |
| Resgate ok | 200 + Resource (status withdrawn) |
| Sem token | 401 |
| Validação (amount, datas) | 422 |
| Já resgatado / data inválida de domínio | 422 |
| ID inexistente ou IDOR Owner | 404 |
| Throttle | 429 |

**Decision**: Não usar 403 em IDOR de investimento (ver §2).

## 9. Rate limit

**Decision**: Named limiter `investments`: 60/min por `user id` + IP, nas quatro rotas. Login permanece `throttle:login`. Health intacto.

**Rationale**: Superfície autenticada nova; evita flood de create/withdraw no desafio sem afetar login.

## 10. Scribe

**Decision**: `@group Investments`, `@authenticated` nos quatro invocáveis. Regenerar `public/docs` neste PR.

## Resoluções NEEDS CLARIFICATION

Nenhum item do Technical Context ficou aberto. Defaults da spec (dono = autenticado, per_page 15/100, 404 no IDOR) adotados.
