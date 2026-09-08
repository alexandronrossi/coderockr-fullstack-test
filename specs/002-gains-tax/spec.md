# Feature Specification: Ganho composto no dia civil e imposto no resgate

**Feature Branch**: `002-gains-tax`

**Created**: 2026-09-08

**Status**: Draft

**Input**: User description: "Etapa de domínio do plano Coderockr: calcular ganho composto de 0,52% a cada mês completo no mesmo dia civil da criação (se o dia não existir no mês, último dia possível; sempre somar N meses a partir da data original, não da data já ajustada); período incompleto não rende; imposto só sobre o ganho, conforme idade do investimento (<1 ano 22,5%, 1–2 anos 18,5%, >2 anos 15%); exemplo 1000 / saldo 1200 / ganho 200 / IR 45 / líquido 1155. Sem API de criar/listar/resgatar e sem tela nesta etapa."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Saldo esperado com ganho composto (Priority: P1)

Uma pessoa (ou o próprio produto, ao consultar um investimento) precisa saber quanto o valor inicial vale numa data de referência: o principal mais os ganhos dos **meses civis completos** desde a criação, cada um a 0,52% sobre o saldo anterior (composto).

**Why this priority**: Sem saldo correto, visualização, resgate e imposto saem errados. É o MVP desta etapa.

**Independent Test**: Dado valor inicial, data de criação e data de referência, o saldo e o ganho batem com os cenários abaixo, sem persistir investimento nem chamar a API.

**Acceptance Scenarios**:

1. **Given** 1000,00 criados numa data D, **When** a data de referência é o mesmo dia D (nenhum mês completo), **Then** o ganho é 0,00 e o saldo esperado é 1000,00.
2. **Given** 1000,00 criados em D, **When** a data de referência é exatamente um mês civil completo depois (mesmo dia do mês seguinte, ou último dia se o dia não existir), **Then** o saldo esperado é 1005,20 (1000 × 1,0052) e o ganho é 5,20.
3. **Given** 1000,00 e dois meses civis completos, **When** o ganho é composto, **Then** o saldo é 1000 × 1,0052 × 1,0052, arredondado a duas casas no resultado apresentado.
4. **Given** um investimento cujo aniversário mensal ainda não chegou na data de referência, **When** se calcula o saldo, **Then** o mês incompleto **não** entra no ganho.

---

### User Story 2 - Meses com dias que não existem (Priority: P1)

O rendimento cai no **mesmo dia civil** da criação. Se aquele dia não existe no mês (ex.: 31 em fevereiro), usa-se o **último dia daquele mês**. O próximo período continua sendo contado a partir da **data original de criação**, não a partir da data já “encolhida”.

**Why this priority**: Sem esta regra, janeiro 31 vira 28 de fevereiro para sempre e o saldo diverge do combinado.

**Independent Test**: Casos de 28–31 de janeiro em ano comum e bissexto, e 31 de março → 30 de abril, sem API.

**Acceptance Scenarios**:

1. **Given** criação em 31 de janeiro de um ano comum, **When** se pede o primeiro aniversário mensal, **Then** o mês completa em 28 de fevereiro (último dia de fevereiro).
2. **Given** a mesma criação em 31 de janeiro, **When** se pede o segundo aniversário mensal, **Then** o mês completa em 31 de março (31 existe), **não** em 28 de março.
3. **Given** criação em 29 de janeiro de ano bissexto, **When** o primeiro aniversário é fevereiro, **Then** completa em 29 de fevereiro (o dia existe).
4. **Given** criação em 30 ou 31 de janeiro de ano bissexto, **When** o primeiro aniversário é fevereiro, **Then** completa em 29 de fevereiro (último dia de fevereiro naquele ano).
5. **Given** criação em 31 de março, **When** o primeiro aniversário é abril, **Then** completa em 30 de abril.

---

### User Story 3 - Imposto só sobre o ganho no resgate (Priority: P1)

Quando se avalia um resgate numa data, o imposto incide **somente sobre o ganho** (saldo esperado nessa data menos o valor inicial), nunca sobre o principal. A alíquota depende da **idade** do investimento nessa data.

**Why this priority**: O README fixa o exemplo 1000 → 1200 → IR 45; erro aqui é erro de negócio visível na entrega.

**Independent Test**: Com principal, saldo esperado e idade, o imposto e o líquido batem; principal 1000 e ganho 0 → imposto 0.

**Acceptance Scenarios**:

1. **Given** inicial 1000,00 e saldo esperado 1200,00 com idade menor que um ano, **When** se calcula o resgate, **Then** o ganho é 200,00, o imposto é 45,00 (22,5%) e o líquido é 1155,00.
2. **Given** o mesmo ganho de 200,00 com idade de um ano inclusive até dois anos inclusive, **When** se calcula o imposto, **Then** a alíquota é 18,5% (37,00) e o líquido é 1163,00.
3. **Given** o mesmo ganho de 200,00 com idade maior que dois anos, **When** se calcula o imposto, **Then** a alíquota é 15% (30,00) e o líquido é 1170,00.
4. **Given** saldo esperado igual ao inicial, **When** se calcula o imposto, **Then** o imposto é 0,00 e o líquido é o inicial.

---

### User Story 4 - Ganhos param na data de um resgate já ocorrido (Priority: P2)

Se o investimento **já foi resgatado**, o saldo “de então” congela: os ganhos são os da data do resgate, não os de “hoje”. Esta etapa define a regra; gravar o resgate na API fica para depois.

**Why this priority**: O README exige que investimento já sacado não continue rendendo na visualização.

**Independent Test**: Mesmo principal e criação; data de resgate no passado vs data de referência posterior → saldo idêntico ao da data do resgate.

**Acceptance Scenarios**:

1. **Given** um investimento resgatado na data R, **When** a data de referência é posterior a R, **Then** ganho e saldo são os calculados em R, não na data posterior.
2. **Given** um investimento ainda não resgatado, **When** a data de referência é hoje (ou outra data válida), **Then** o ganho usa essa data de referência.

---

### Edge Cases

- Data de referência anterior à criação: não há ganho; o cálculo de domínio recusa ou devolve ganho 0 com indicação de data inválida (a API de resgate, depois, rejeitará a operação).
- Data de referência no futuro: fora das regras de resgate do README; o cálculo de **visualização** usa no máximo “hoje” quando a etapa de API existir. Nesta etapa, o serviço de domínio aceita uma data de referência explícita para os testes.
- Valor inicial zero ou negativo: fora desta etapa (criação na API); o cálculo assume valor inicial **maior que zero**.
- Arredondamento: valores monetários apresentados com duas casas; composto usa 0,52% por período completo.
- Idade **exatamente** um ano → faixa 18,5%. Idade **exatamente** dois anos → faixa 18,5%. Acima de dois anos → 15%.
- Ano bissexto só altera fevereiro; demais meses seguem o calendário civil.
- Esta etapa **não** cria, lista, detalha nem resgata via HTTP e **não** inclui a SPA.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: O sistema MUST calcular o ganho composto de 0,52% para cada mês civil **completo** entre a data de criação e a data de referência.
- **FR-002**: Um mês só é completo quando a data de referência é igual ou posterior ao aniversário daquele período (mesmo dia civil, ou último dia do mês se o dia não existir).
- **FR-003**: Período incompleto MUST NOT aumentar o saldo.
- **FR-004**: Cada período MUST aplicar 0,52% sobre o saldo já acrescido dos períodos anteriores (composto), não sobre o inicial isolado após o primeiro mês.
- **FR-005**: Aniversários MUST ser obtidos somando N meses à **data original de criação**, depois ajustando para o último dia do mês destino se necessário. MUST NOT encadear o ajuste (não usar a data já recuada como base do mês seguinte).
- **FR-006**: O saldo esperado MUST ser inicial + ganhos na data de referência (ou na data de resgate, se já resgatado — FR-010).
- **FR-007**: O imposto de resgate MUST incidir só sobre (saldo esperado − valor inicial), nunca sobre o principal.
- **FR-008**: Alíquotas MUST ser: idade &lt; 1 ano → 22,5%; 1 ano ≤ idade ≤ 2 anos → 18,5%; idade &gt; 2 anos → 15%. Idade medida da criação até a data do resgate (ou a data em que o resgate é avaliado).
- **FR-009**: O valor líquido do resgate MUST ser saldo esperado − imposto.
- **FR-010**: Se já houver data de resgate, ganhos e saldo MUST congelar nessa data.
- **FR-011**: Resultados monetários apresentados MUST ter duas casas decimais.
- **FR-012**: Esta feature MUST NOT expor endpoints de investimento nem telas. Persistência do investimento (model/migration) fica para a etapa seguinte, que reutilizará estas regras.

### Key Entities

- **Investimento (conceitual)**: Valor inicial positivo, data de criação, opcionalmente data de resgate. Nesta etapa existe como dado de entrada do cálculo, não como registro obrigatório de banco.
- **Saldo esperado**: Valor inicial mais ganhos compostos até a data efetiva (referência ou resgate).
- **Ganho**: Saldo esperado menos valor inicial (≥ 0).
- **Avaliação de imposto**: Ganho, idade, alíquota, valor do imposto, líquido.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: O exemplo do desafio (inicial 1000, saldo 1200, idade &lt; 1 ano) produz imposto 45,00 e líquido 1155,00 em 100% das execuções do cálculo.
- **SC-002**: 100% dos casos de mês incompleto produzem o mesmo saldo do último aniversário completo (zero ganho extra).
- **SC-003**: 100% dos casos “31 de janeiro → março” usam 31 de março no segundo aniversário, não 28 de março, em ano comum.
- **SC-004**: Imposto sobre ganho 0,00 é 0,00; imposto nunca reduz o principal abaixo do inicial quando o ganho é 0.
- **SC-005**: Um avaliador consegue verificar os cenários desta spec só com os testes de domínio, em menos de dois minutos de execução da suíte correspondente.

## Assumptions

- Calendário civil gregoriano (fuso não altera o **dia** combinado; datas são calendário, não instantes com hora).
- “Entre um e dois anos” inclui os aniversários de 1 ano e de 2 anos na alíquota 18,5%.
- Arredondamento comercial a duas casas no valor **apresentado** de saldo, ganho, imposto e líquido; o plan da implementação detalha se o composto intermediário usa mais precisão.
- Criação, listagem, detalhe HTTP, resgate persistido, paginação e SPA ficam em specs posteriores.
- Autenticação (Admin/Owner) já existe; esta etapa não altera login.
- Valor inicial negativo ou zero é rejeitado na etapa de criação, não aqui.
- O README ilustra 1200/200/45 como exemplo de **imposto**, não como resultado obrigatório de N meses a 0,52%.
