# Feature Specification: Autenticação e papéis Admin / Owner

**Feature Branch**: `001-user-auth`

**Created**: 2026-09-08

**Status**: Draft

**Input**: User description: "Etapa de auth do plano Coderockr: pessoas se identificam com e-mail e senha; Administrador vê todos os registros futuros e Owner só os seus; o papel vive só no servidor; ambiente inicial cria um Administrador; o cliente nunca atribui o próprio papel."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Entrar na aplicação (Priority: P1)

Uma pessoa com conta existente informa e-mail e senha e passa a ser reconhecida nas operações seguintes, até encerrar a sessão.

**Why this priority**: Sem identidade, nenhuma operação protegida de investimento pode ser autorizada. É o MVP desta etapa.

**Independent Test**: Com uma conta já provisionada, a pessoa entra com credenciais válidas e consulta o próprio perfil; com credenciais inválidas, não entra.

**Acceptance Scenarios**:

1. **Given** uma conta ativa com e-mail e senha corretos, **When** a pessoa envia essas credenciais, **Then** o sistema reconhece a sessão e devolve o perfil (nome, e-mail e papel) sem a senha.
2. **Given** e-mail inexistente ou senha errada, **When** a pessoa tenta entrar, **Then** o acesso é recusado e a mensagem não revela se o e-mail existe.
3. **Given** uma pessoa sem sessão, **When** ela pede o próprio perfil, **Then** o sistema recusa o acesso.

---

### User Story 2 - Encerrar a sessão (Priority: P2)

Uma pessoa autenticada encerra a sessão e deixa de ser reconhecida até entrar de novo.

**Why this priority**: Impede reuso da sessão em um dispositivo compartilhado; independente da criação de investimentos.

**Independent Test**: Entrar, encerrar a sessão, tentar de novo o perfil com a sessão anterior.

**Acceptance Scenarios**:

1. **Given** uma sessão válida, **When** a pessoa encerra a sessão, **Then** a mesma sessão não acessa mais o perfil.
2. **Given** uma pessoa sem sessão, **When** ela tenta encerrar sessão, **Then** o sistema recusa (não há sessão para encerrar).

---

### User Story 3 - Papel atribuído só pelo servidor (Priority: P1)

O sistema distingue **Administrador** e **Owner**. O papel determina, nas etapas seguintes, quem vê todos os investimentos e quem vê só os próprios. Nesta etapa o papel já existe, é visível no perfil e **não** pode ser escolhido ou alterado pelo cliente.

**Why this priority**: Autenticação sem autorização correta seria falsa segurança. O desafio pede lista “de uma pessoa” na API e “todos” na UI; a decisão de produto é Admin = todos, Owner = só os seus, sempre no servidor.

**Independent Test**: Owner tenta promover-se no pedido de login ou de perfil e permanece Owner; Administrador provisionado no ambiente inicial continua Administrador.

**Acceptance Scenarios**:

1. **Given** um Owner autenticado, **When** o cliente envia um campo de papel, flag de administrador ou identificador de usuário no login ou na consulta de perfil, **Then** o papel persistido não muda e o perfil continua Owner.
2. **Given** um ambiente recém-preparado (seed), **When** a equipe usa a conta de Administrador prevista, **Then** essa conta entra e o perfil indica Administrador.
3. **Given** um Owner autenticado, **When** ele consulta o próprio perfil, **Then** vê apenas os dados da própria conta, nunca os de outra pessoa.

---

### User Story 4 - Contas iniciais para operar sem cadastro público (Priority: P2)

O desafio não pede cadastro aberto. O ambiente inicial já contém um Administrador e pelo menos um Owner de demonstração, com credenciais documentadas só para desenvolvimento local (nunca embutidas no produto como segredo de produção).

**Why this priority**: Sem contas, ninguém testa login nem as etapas seguintes.

**Independent Test**: Preparar o ambiente e entrar com as duas contas seed.

**Acceptance Scenarios**:

1. **Given** o banco recém-preparado, **When** a equipe entra com a conta Administrador seed, **Then** o perfil é Administrador.
2. **Given** o mesmo ambiente, **When** a equipe entra com a conta Owner seed, **Then** o perfil é Owner.

---

### Edge Cases

- Credenciais vazias, e-mail malformado ou senha em branco: recusa, sem criar sessão.
- Muitas tentativas falhas seguidas no mesmo identificador: o sistema atrasa ou bloqueia temporariamente novas tentativas (proteção contra força bruta).
- Sessão inválida, expirada ou adulterada: tratado como não autenticado.
- Pessoa autenticada consulta o perfil: nunca recebe a senha nem o segredo usado para verificá-la.
- Dois papéis apenas; qualquer valor desconhecido de papel no armazenamento é recusado na autorização (não “cai” para Admin).
- Esta etapa não cria, lista, detalha nem resgata investimentos; tentativas de usar regras de investimento ainda não existentes estão fora de escopo.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: O sistema MUST autenticar pessoas com e-mail e senha já existentes (não há cadastro público nesta etapa).
- **FR-002**: Após autenticação bem-sucedida, o sistema MUST reconhecer a pessoa nas operações protegidas até a sessão ser encerrada ou expirar.
- **FR-003**: O sistema MUST recusar operações protegidas (perfil e, no futuro, investimentos) quando não houver sessão válida.
- **FR-004**: Uma pessoa autenticada MUST poder obter o próprio perfil: identificador, nome, e-mail e papel. Senha e equivalentes MUST NOT ser devolvidos.
- **FR-005**: Uma pessoa autenticada MUST poder encerrar a própria sessão.
- **FR-006**: Existem exatamente dois papéis: **Administrador** e **Owner**. Administrador, nas etapas de investimento, poderá ver e operar registros de qualquer pessoa. Owner poderá ver e operar apenas os próprios. Nesta etapa o papel já é persistido e exposto no perfil.
- **FR-007**: O papel MUST ser definido apenas no servidor (provisionamento / seed). O cliente NEVER escolhe, altera ou eleva o papel, uma flag de administrador, o identificador de usuário ou o status via login, perfil ou qualquer campo enviado.
- **FR-008**: Autenticar-se MUST NOT equivaler a estar autorizado sobre o registro de outra pessoa. Esconder um botão na interface MUST NOT ser tratado como controle de acesso.
- **FR-009**: Falha de login MUST usar a mesma mensagem genérica, independentemente de o e-mail existir.
- **FR-010**: O ambiente inicial MUST criar pelo menos um Administrador e um Owner de demonstração.
- **FR-011**: Tentativas de login MUST ser limitadas contra força bruta (atraso ou bloqueio temporário após falhas repetidas).
- **FR-012**: Erros apresentados à pessoa MUST NOT expor detalhes internos (pilha, caminhos, credenciais).
- **FR-013**: Esta feature MUST NOT incluir telas da interface web de investimento nem cadastro aberto, recuperação de senha, login social ou múltiplos fatores.

### Key Entities

- **Pessoa (User)**: Conta com nome, e-mail único, senha secreta e um papel (Administrador ou Owner). A senha nunca aparece em respostas.
- **Sessão**: Vínculo temporário entre uma pessoa autenticada e as operações seguintes; pode ser encerrada.
- **Papel**: Atributo de autorização. Não é escolhido pelo cliente. Administrador ≠ Owner.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Uma pessoa com credenciais válidas conclui o acesso e vê o próprio perfil em menos de um minuto, na primeira tentativa.
- **SC-002**: 100% das tentativas sem sessão válida são recusadas em operações protegidas (perfil nesta etapa).
- **SC-003**: 100% das tentativas de um Owner de se tornar Administrador via campos enviados pelo cliente falham; o papel no perfil permanece Owner.
- **SC-004**: Após encerrar a sessão, 100% das tentativas de reutilizar essa sessão no perfil são recusadas.
- **SC-005**: Em um ambiente recém-preparado, a equipe entra com a conta Administrador seed e com a conta Owner seed sem cadastro manual.
- **SC-006**: Em falhas de login, a pessoa não consegue distinguir “e-mail inexistente” de “senha errada” pela mensagem.

## Assumptions

- O README do desafio não pede login; a decisão de produto é exigir autenticação para qualquer operação de investimento futura, com dois papéis no servidor.
- Não há cadastro público, SSO, recuperação de senha nem MFA nesta etapa (e não no prazo do desafio, salvo spec futura).
- A interface web de login (SPA) fica para a etapa de UI; esta spec cobre só o comportamento do produto no lado servidor e a identidade persistida.
- Criar, listar, detalhar e resgatar investimentos ficam em specs posteriores; elas reutilizarão esta identidade e estes papéis.
- Haverá um Owner seed além do Administrador para testes de isolamento nas etapas seguintes.
- Credenciais seed existem apenas para desenvolvimento e testes locais; produção real exigiria segredos fora do código (fora do escopo deste desafio).
- “Mesmo dia civil / IR / resgate” não se aplica a esta feature.
- Checagem de saúde pública da API, se existir, permanece acessível sem sessão.
