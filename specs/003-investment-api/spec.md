# Feature Specification: Criar, listar, detalhar e resgatar investimentos

**Feature Branch**: `003-investment-api`

**Created**: 2026-09-08

**Status**: Draft

**Input**: User description: "Etapa de API autenticada do plano Coderockr: persistir investimentos (dono, data de criação, valor inicial); listar com paginação; detalhar com saldo esperado; resgatar o valor integral com imposto só sobre o ganho; Administrador vê e opera todos, Owner só os próprios; saldo e IR reutilizam as regras já definidas (mês civil composto e faixas de idade); cliente nunca envia saldo esperado, imposto, status nem dono. Sem telas nesta etapa."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Registrar um investimento (Priority: P1)

Uma pessoa autenticada registra um investimento com valor inicial positivo e data de criação (hoje ou no passado). O registro fica associado a ela como dona. A partir daí o produto consegue calcular saldo esperado.

**Why this priority**: Sem registro persistido, lista, detalhe e resgate não existem. É o MVP desta etapa.

**Independent Test**: Entrar como Owner, registrar um valor e uma data válida, e obter de volta o mesmo valor, a mesma data, a dona correta e status ativo — sem resgate.

**Acceptance Scenarios**:

1. **Given** uma pessoa autenticada, **When** ela registra valor 1000,00 e data de criação hoje ou no passado, **Then** o investimento existe, pertence a ela, o valor inicial é 1000,00, o status é ativo e o saldo esperado no dia da criação é 1000,00 (ganho 0).
2. **Given** uma pessoa autenticada, **When** ela tenta registrar valor zero, negativo ou data de criação no futuro, **Then** o registro é recusado e nenhum investimento novo é criado.
3. **Given** uma pessoa autenticada, **When** o pedido inclui dono, status, saldo esperado, ganho, imposto ou data de resgate, **Then** esses campos são ignorados ou recusados: o dono continua sendo a pessoa autenticada, o status nasce ativo e os valores calculados vêm só das regras de negócio.
4. **Given** uma pessoa sem sessão, **When** ela tenta registrar um investimento, **Then** a operação é recusada.

---

### User Story 2 - Ver o investimento com saldo esperado (Priority: P1)

A pessoa consulta um investimento específico e vê valor inicial, saldo esperado, ganho, status e, se já resgatado, os valores do resgate (imposto e líquido) congelados na data do saque.

**Why this priority**: O desafio exige visualizar inicial e saldo esperado; sem isso a lista sozinha não demonstra o ganho.

**Independent Test**: Criar um investimento cuja data de criação complete pelo menos um mês civil até “hoje”; consultar o detalhe e conferir saldo 1005,20 para inicial 1000,00 no primeiro aniversário.

**Acceptance Scenarios**:

1. **Given** um investimento ativo de 1000,00 criado exatamente um mês civil completo antes de hoje, **When** a dona consulta o detalhe, **Then** o valor inicial é 1000,00, o saldo esperado é 1005,20 e o ganho é 5,20.
2. **Given** um investimento já resgatado na data R, **When** alguém autorizado consulta o detalhe depois de R, **Then** saldo, ganho e imposto são os de R, não os de uma data posterior.
3. **Given** um Owner autenticado, **When** ele pede o detalhe de um investimento de outra pessoa, **Then** o acesso é recusado e os dados desse investimento não são revelados.
4. **Given** um Administrador autenticado, **When** ele pede o detalhe de um investimento de um Owner, **Then** vê os mesmos dados que a dona veria.
5. **Given** um identificador inexistente, **When** uma pessoa autenticada pede o detalhe, **Then** a consulta é recusada como não encontrado, sem vazar existência de registros de outras pessoas para um Owner.

---

### User Story 3 - Listar investimentos com paginação (Priority: P1)

A pessoa vê uma lista paginada: dono, data, valor inicial, saldo esperado atual (ou congelado se resgatado) e status. Owner vê só os próprios. Administrador vê os de todas as pessoas.

**Why this priority**: O desafio pede lista da pessoa com paginação; é o fluxo principal da UI futura.

**Independent Test**: Criar mais investimentos do que cabe em uma página; Owner vê só os seus; Administrador vê também os de outro Owner; mudar de página altera o conjunto sem misturar donos indevidos.

**Acceptance Scenarios**:

1. **Given** um Owner com vários investimentos próprios e outro Owner com os dele, **When** o primeiro lista, **Then** só aparecem os dele, cada item com dono, data, valor inicial, saldo esperado e status.
2. **Given** o mesmo conjunto, **When** um Administrador lista, **Then** a lista inclui investimentos de todas as pessoas, ainda paginada.
3. **Given** mais registros visíveis do que o tamanho de uma página, **When** a pessoa pede a página seguinte, **Then** recebe o próximo lote, o total de registros visíveis e não recebe itens da página anterior.
4. **Given** um Owner, **When** ele tenta listar filtrando ou forçando o identificador de outra pessoa, **Then** continua vendo apenas os próprios (o filtro não amplia o alcance).

---

### User Story 4 - Resgatar o valor integral com imposto (Priority: P1)

A pessoa informa a data do resgate (hoje ou no passado, não antes da criação e não no futuro). O saque é sempre o saldo esperado integral dessa data; não há saque parcial. O imposto incide só sobre o ganho, nas faixas já definidas. Depois disso o investimento fica resgatado e para de render.

**Why this priority**: Fecha o ciclo de vida do investimento no desafio; depende das regras de domínio já aceitas.

**Independent Test**: Investimento 1000,00 cujo saldo esperado na data de resgate seja 1200,00 com idade menor que um ano → imposto 45,00 e líquido 1155,00; segunda tentativa de resgate recusada.

**Acceptance Scenarios**:

1. **Given** um investimento ativo com inicial 1000,00 e saldo esperado 1200,00 na data R com idade menor que um ano, **When** a dona resgata em R, **Then** o ganho é 200,00, o imposto é 45,00, o líquido é 1155,00 e o status passa a resgatado.
2. **Given** um investimento já resgatado, **When** alguém tenta resgatar de novo, **Then** a operação é recusada e os valores do primeiro resgate não mudam.
3. **Given** um investimento ativo, **When** a data de resgate é anterior à criação ou no futuro, **Then** o resgate é recusado e o investimento permanece ativo.
4. **Given** um Owner, **When** ele tenta resgatar o investimento de outra pessoa, **Then** a operação é recusada.
5. **Given** um Administrador, **When** ele resgata o investimento de um Owner com data válida, **Then** o resgate é gravado para aquele investimento (o dono não muda).
6. **Given** um pedido de resgate, **When** o cliente envia imposto, líquido, saldo esperado ou alíquota, **Then** esses valores são ignorados; o produto calcula com as regras de domínio.

---

### Edge Cases

- Valor inicial com mais de duas casas, vazio ou não numérico: recusa, sem criar registro.
- Data de criação ou de resgate malformada: recusa.
- Página inválida (zero, negativa ou além da última): lista vazia ou recusa clara, sem vazar dados de outro dono.
- Tamanho de página excessivo: limitado a um máximo; o cliente não obtém “todos os registros do sistema” numa única página descontrolada.
- Duas tentativas simultâneas de resgatar o mesmo investimento ativo: no máximo uma sucede; a outra é recusada como já resgatado (ou equivalente).
- Investimento ativo consultado no mês incompleto: saldo igual ao do último aniversário completo (zero ganho extra no período corrente), conforme regras já definidas.
- Identificador de investimento adulterado ou de outro dono, para Owner: recusa sem revelar o conteúdo.
- Erros apresentados à pessoa não expõem pilha, caminhos, SQL nem segredos.
- Esta etapa não inclui telas da interface web, cadastro de usuários, alteração de papel, saque parcial nem data de resgate futura.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: O sistema MUST exigir sessão autenticada para criar, listar, detalhar e resgatar investimentos.
- **FR-002**: Ao criar, o sistema MUST persistir dono (a pessoa autenticada), data de criação (hoje ou passado, nunca futuro) e valor inicial estritamente maior que zero, com duas casas no valor apresentado.
- **FR-003**: O cliente NEVER escolhe ou altera dono, papel, status, saldo esperado, ganho, imposto, líquido ou data de resgate na criação. Data de resgate só entra pela operação de resgate.
- **FR-004**: Owner MUST ver e operar apenas investimentos cujo dono é ele. Administrador MUST poder ver e resgatar investimentos de qualquer pessoa. Esconder um controle na interface MUST NOT ser o controle de acesso.
- **FR-005**: Consultar ou resgatar, como Owner, um investimento de outra pessoa MUST ser recusado sem revelar os dados desse registro.
- **FR-006**: O detalhe MUST devolver valor inicial, saldo esperado, ganho e status (ativo ou resgatado). Se resgatado, MUST devolver também data do resgate, imposto e líquido dessa data.
- **FR-007**: Saldo esperado, ganho e imposto MUST ser calculados pelas regras já definidas (0,52% composto por mês civil completo; imposto só sobre o ganho; faixas 22,5% / 18,5% / 15%). O cliente NEVER envia esses resultados como fonte da verdade.
- **FR-008**: Para investimento ativo, a data efetiva do saldo é “hoje” no calendário do servidor. Para investimento resgatado, a data efetiva é a data do resgate (ganhos congelados).
- **FR-009**: A lista MUST ser paginada (página e tamanho de página, com tamanho máximo) e MUST incluir, por item, identificação do dono, data de criação, valor inicial, saldo esperado efetivo e status.
- **FR-010**: Owner MUST NOT ampliar a lista para registros de outras pessoas via parâmetros de filtro, ordenação ou identificador.
- **FR-011**: O resgate MUST ser integral (saldo esperado na data informada). Saque parcial MUST NOT ser suportado.
- **FR-012**: A data de resgate MUST ser informada pela pessoa, MUST ser hoje ou passado, MUST NOT ser anterior à criação e MUST NOT ser futura.
- **FR-013**: Um investimento resgatado MUST NOT ser resgatado de novo; ganhos posteriores MUST NOT acumular.
- **FR-014**: Erros MUST NOT expor detalhes internos. Mensagens de negócio (validação, não encontrado, já resgatado) MUST ser compreensíveis.
- **FR-015**: Esta feature MUST NOT incluir a interface web de investimentos. Login, papéis e o cálculo de domínio MUST ser reutilizados, não reimplementados com outra fórmula.
- **FR-016**: As operações desta etapa MUST ser documentadas para o avaliador (o que cada operação exige, quem pode usá-la e o que devolve), no mesmo canal de documentação das operações já existentes.

### Key Entities

- **Investimento**: Registro persistido com dono (pessoa), valor inicial positivo, data de criação, status (ativo ou resgatado) e, se resgatado, data de resgate. Saldo esperado, ganho, imposto e líquido são derivados, não informados pelo cliente.
- **Pessoa**: Conta autenticada com papel Administrador ou Owner (já existente).
- **Avaliação**: Resultado de aplicar as regras de ganho e imposto numa data efetiva (hoje ou data de resgate).
- **Página de lista**: Subconjunto ordenado dos investimentos que a pessoa autenticada tem permissão de ver, com total e posição da página.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Uma pessoa autenticada registra um investimento válido e o vê no detalhe em menos de um minuto, na primeira tentativa.
- **SC-002**: 100% das tentativas sem sessão são recusadas nas quatro operações (criar, listar, detalhar, resgatar).
- **SC-003**: 100% das tentativas de um Owner de detalhar ou resgatar investimento de outra pessoa são recusadas; 0% dessas tentativas devolvem valor inicial, saldo ou dono alheio.
- **SC-004**: Com mais itens visíveis do que uma página, 100% das páginas seguintes devolvem um lote distinto e o total corresponde à quantidade que aquela pessoa pode ver.
- **SC-005**: O exemplo do desafio persistido (inicial 1000, saldo esperado 1200, idade menor que um ano) produz imposto 45,00 e líquido 1155,00 no resgate, em 100% das execuções.
- **SC-006**: Após um resgate bem-sucedido, 100% das consultas posteriores mostram o mesmo saldo da data do resgate (não o saldo “de hoje”).
- **SC-007**: 100% das tentativas de o cliente definir dono, status, saldo esperado ou imposto na criação ou no resgate falham em alterar a verdade do servidor.

## Assumptions

- Autenticação e papéis Administrador / Owner já existem e não mudam nesta etapa.
- As regras de ganho composto, aniversário civil e IR da etapa anterior são a única fonte de cálculo; esta etapa só persiste e autoriza.
- Na criação, o dono é sempre a pessoa autenticada com papel Owner. Administrador **não** cria investimentos (só lista, detalha e resgata os de qualquer dono).
- “Hoje” e “futuro” usam o calendário civil do servidor, no mesmo espírito da etapa de domínio (dia, não instante com fuso de negócio).
- Ordenação padrão da lista: mais recentemente criados primeiro.
- Tamanho de página padrão 15, máximo 100 (detalhe de entrega no plan; o produto limita abuso).
- Não há filtro obrigatório por dono na lista do Administrador nesta etapa (ele vê todos, paginado). Owner não filtra por outras pessoas.
- Não há edição de valor ou data depois de criado, nem exclusão, nem saque parcial, nem agendamento futuro.
- A SPA (lista, formulário, detalhe visual) fica para a etapa de interface; esta spec cobre o comportamento no lado servidor.
- Documentar as operações no canal já usado pela aplicação (documentação da API existente) faz parte desta entrega, porque o desafio pede API documentada e há operações novas.
- Concorrência de resgate: a invariante de negócio é “no máximo um resgate por investimento”; o plan descreve o mecanismo.
