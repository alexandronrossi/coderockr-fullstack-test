# Feature Specification: Interface web de investimentos

**Feature Branch**: `004-investment-ui`

**Created**: 2026-09-08

**Status**: Draft

**Input**: User description: "Etapa de interface do plano Coderockr: pessoa entra com e-mail e senha; vê lista paginada (dono, data, valor, saldo atual, status); abre detalhe com ganhos e saldo; cria investimento; resgata e vê o valor líquido com imposto. Administrador vê todos; Owner só os próprios. Esconder botão na tela NÃO é o controle de acesso. Cliente não calcula ganho nem IR — só exibe o que o servidor devolve. Referência visual Figma, sem pixel-perfect. Incluir evidências visuais (capturas) para o avaliador."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Entrar e ver a lista (Priority: P1) 🎯 MVP

Uma pessoa com conta existente informa e-mail e senha e chega a uma lista paginada dos investimentos que tem permissão de ver: dono, data, valor inicial, saldo esperado atual (ou congelado se resgatado) e status.

**Why this priority**: Sem entrar e listar, as outras telas não demonstram o produto. É o MVP desta etapa.

**Independent Test**: Entrar como Owner com a conta de demonstração; ver só os próprios itens; entrar como Administrador e ver também os de outras pessoas; recusar senha errada sem revelar se o e-mail existe.

**Acceptance Scenarios**:

1. **Given** credenciais válidas de Owner, **When** a pessoa entra, **Then** vê a lista só com investimentos cujo dono é ela, cada linha com dono, data, valor, saldo e status.
2. **Given** credenciais válidas de Administrador, **When** entra, **Then** a lista inclui investimentos de todas as pessoas (ainda paginada).
3. **Given** mais itens do que cabe em uma página, **When** a pessoa pede a página seguinte, **Then** vê o próximo lote e o total permanece o que ela tem permissão de ver.
4. **Given** e-mail ou senha inválidos, **When** tenta entrar, **Then** permanece fora da lista e a mensagem não distingue “e-mail inexistente” de “senha errada”.
5. **Given** uma pessoa sem sessão, **When** tenta abrir lista, detalhe, criação ou resgate, **Then** é levada a entrar antes de ver dados de investimento.

---

### User Story 2 - Criar um investimento (Priority: P1)

A pessoa autenticada preenche valor inicial positivo e data de criação (hoje ou passado) e confirma. Não escolhe dono, status nem saldo esperado.

**Why this priority**: O desafio pede formulário de criação; fecha o ciclo da lista vazia.

**Independent Test**: Owner autenticado cria 1000,00 com data válida e o item aparece na lista/detalhe como ativo, dono = ela.

**Acceptance Scenarios**:

1. **Given** uma pessoa autenticada, **When** envia valor 1000,00 e data hoje ou no passado, **Then** o investimento aparece na lista dela (e no detalhe) com status ativo e saldo no dia da criação igual ao valor inicial.
2. **Given** valor zero, negativo, vazio ou data futura, **When** tenta confirmar, **Then** a criação não ocorre e a pessoa vê um erro compreensível.
3. **Given** o formulário, **When** a pessoa tenta indicar outro dono, papel, status ou saldo esperado, **Then** esses campos não existem na tela ou não alteram o resultado: o dono é sempre quem está autenticado.

---

### User Story 3 - Ver o detalhe com ganhos (Priority: P1)

A pessoa abre um investimento e vê valor inicial, saldo esperado, ganho, status e, se resgatado, data do resgate, imposto e líquido — os números vêm do servidor, não de uma conta feita na tela.

**Why this priority**: O desafio pede visualização detalhada incluindo ganhos e saldo final.

**Independent Test**: Abrir um investimento ativo com pelo menos um mês civil completo (ex. 1000,00 → 1005,20) e conferir os valores na tela.

**Acceptance Scenarios**:

1. **Given** um investimento ativo da pessoa com um mês civil completo, **When** ela abre o detalhe, **Then** vê inicial, saldo esperado, ganho e status ativo alinhados ao servidor.
2. **Given** um investimento já resgatado, **When** abre o detalhe, **Then** saldo, ganho e imposto são os da data do resgate (congelados), não um recálculo “de agora” na tela.
3. **Given** um Owner, **When** tenta abrir o detalhe de um investimento de outra pessoa (endereço direto), **Then** não vê os valores alheios (não encontrado ou equivalente); um Administrador vê o detalhe de qualquer dono.

---

### User Story 4 - Resgatar e ver o valor líquido (Priority: P1)

A pessoa informa a data do resgate (hoje ou passado, não antes da criação e não futura), confirma o saque integral e vê ganho, imposto e líquido devolvidos pelo servidor. Depois o item fica resgatado na lista e no detalhe.

**Why this priority**: Fecha o ciclo de vida na UI; o avaliador precisa ver o imposto aplicado.

**Independent Test**: Resgatar um ativo com data válida e ver imposto e líquido na confirmação/detalhe; segunda tentativa recusada; Owner não resgata o de outra pessoa mesmo se o botão fosse visível.

**Acceptance Scenarios**:

1. **Given** um investimento ativo, **When** a dona (ou um Administrador) confirma resgate com data válida, **Then** vê o valor líquido com imposto calculado no servidor e o status passa a resgatado.
2. **Given** data anterior à criação ou no futuro, **When** tenta confirmar, **Then** o resgate não ocorre e a pessoa vê um erro claro.
3. **Given** um investimento já resgatado, **When** tenta resgatar de novo, **Then** a operação é recusada.
4. **Given** um Owner, **When** tenta resgatar investimento de outra pessoa (mesmo forçando a ação), **Then** não obtém os dados nem conclui o saque.

---

### User Story 5 - Encerrar a sessão (Priority: P2)

A pessoa autenticada encerra a sessão e deixa de ver lista/detalhe até entrar de novo.

**Why this priority**: Dispositivo compartilhado; independente de criar/resgatar.

**Independent Test**: Entrar, sair, tentar abrir a lista de novo → pedido de autenticação.

**Acceptance Scenarios**:

1. **Given** uma sessão válida, **When** a pessoa encerra a sessão, **Then** a lista de investimentos deixa de ser acessível até novo login.
2. **Given** sessão encerrada, **When** usa o “voltar” do navegador para uma tela anterior de investimento, **Then** não continua autenticada de forma efetiva (é pedida nova entrada).

---

### Edge Cases

- Lista vazia: mensagem clara, com caminho para criar (se autenticada).
- Falha de rede ou servidor indisponível: erro visível, sem dados inventados na tela.
- Paginação: página inválida não mostra registros de outro dono.
- Campos de criação/resgate malformados: validação visível antes ou junto da recusa do servidor.
- Papel exibido no perfil/cabeçalho é informativo; esconder “Resgatar” para Owner em item alheio **não** substitui a recusa do servidor.
- Layout utilizável em tela estreita (telefone) e larga (desktop); a referência Figma guia estrutura, não pixel a pixel.
- Esta etapa não altera as regras de ganho/IR no servidor nem cria cadastro público de usuários.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: A interface MUST exigir autenticação (e-mail e senha já existentes) para lista, detalhe, criação e resgate.
- **FR-002**: Após login bem-sucedido, a pessoa MUST ver a lista paginada dos investimentos que o servidor a autoriza a ver (Owner = próprios; Administrador = todos).
- **FR-003**: Cada item da lista MUST mostrar dono, data, valor inicial, saldo esperado efetivo e status.
- **FR-004**: A pessoa MUST poder abrir o detalhe e ver inicial, saldo esperado, ganho e status; se resgatado, também data do resgate, imposto e líquido.
- **FR-005**: A pessoa autenticada MUST poder criar investimento informando apenas valor inicial (&gt; 0) e data de criação (hoje ou passado). MUST NOT escolher dono, status, saldo esperado, ganho ou imposto.
- **FR-006**: A pessoa autorizada MUST poder resgatar informando a data do resgate e MUST ver imposto e líquido vindos do servidor.
- **FR-007**: A interface MUST NOT calcular ganho composto nem alíquota de IR por conta própria. Números apresentados MUST ser os devolvidos pelo servidor.
- **FR-008**: Esconder um botão ou rota na tela MUST NOT ser tratado como autorização. Owner MUST NOT obter dados de outro dono mesmo com endereço direto.
- **FR-009**: Pessoa sem sessão MUST NOT permanecer em telas de investimento; login inválido MUST usar mensagem genérica (sem enumerar contas).
- **FR-010**: Pessoa autenticada MUST poder encerrar a sessão.
- **FR-011**: Erros de validação, não encontrado, já resgatado e falha de comunicação MUST ser compreensíveis e MUST NOT expor pilha, tokens ou senha.
- **FR-012**: A estrutura das telas MUST seguir a referência visual do desafio (lista, detalhe, criação, resgate), podendo melhorar espaçamento, estados vazios e comportamento em tela estreita.
- **FR-013**: O repositório MUST incluir pelo menos duas capturas de tela da interface em uso (lista e pelo menos detalhe ou resgate), para o avaliador.
- **FR-014**: Esta feature MUST NOT reimplementar a API de investimentos nem alterar papéis no servidor. MUST reutilizar login, lista, detalhe, criação e resgate já existentes no servidor.

### Key Entities

- **Sessão na interface**: Reconhecimento da pessoa após login até logout; o papel vem do servidor.
- **Investimento (visão)**: Os mesmos dados da API (dono, valores, status, avaliação). A tela é apresentação, não fonte da verdade.
- **Página de lista**: Subconjunto paginado do que a pessoa pode ver.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Uma pessoa com credenciais válidas entra e vê a lista em menos de um minuto na primeira tentativa.
- **SC-002**: 100% das tentativas sem sessão de abrir lista/detalhe/criação/resgate resultam em pedido de autenticação, sem dados de investimento.
- **SC-003**: 100% das tentativas de um Owner de ver ou resgatar investimento de outra pessoa pela interface (incluindo endereço direto) não revelam valor inicial, saldo nem dono alheio.
- **SC-004**: Administrador vê na lista investimentos de mais de um dono no mesmo ambiente de demonstração.
- **SC-005**: Após criar 1000,00 com data válida, 100% das listas/detalhes seguintes da dona mostram o novo item sem a pessoa ter digitado saldo esperado.
- **SC-006**: Após um resgate bem-sucedido, 100% das telas de detalhe mostram imposto e líquido iguais aos do servidor (não um recálculo local).
- **SC-007**: O avaliador encontra pelo menos duas capturas no repositório mostrando lista e outra tela principal.

## Assumptions

- A API autenticada (login, investimentos, papéis Admin/Owner) já existe e é a única fonte de números e autorização.
- Contas seed locais (Administrador e Owner) servem para demonstrar a UI.
- Token/sessão no navegador: detalhe no plan; MUST NOT ir para repositório como segredo e MUST NOT colocar o papel como único gate de API.
- Idioma da interface: português, alinhado às specs anteriores; mensagens do servidor em inglês podem ser traduzidas ou exibidas de forma amigável.
- Referência Figma: https://www.figma.com/design/jkilpjx9Q6hZnnCBHZcAWG/Coderockr-Fullstack---Test — estrutura e hierarquia, não fidelidade pixel.
- Capturas em pasta `screenshots/` na raiz do repositório (exigência do desafio).
- E-mails de notificação continuam fora de escopo.
- README do projeto (instruções de build da UI, bibliotecas, link da documentação da API) pode ser atualizado nesta etapa para o avaliador subir API + interface.
- Entrega final `development` → `main` fica para depois desta UI estar em `development`.
