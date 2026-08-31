# SDD Multi-Agent — Architecture, SOLID, Design Patterns & Security

## 0. Objetivo

Este SDD utiliza múltiplos agentes especializados para transformar uma specification em código implementado, testado, revisado e entregue em Pull Request.

Nenhuma implementação pode ser considerada concluída sem passar pelos seguintes gates:

1. Specification Analysis
2. Architecture & Security Analysis
3. Test Creation
4. Implementation
5. Test Validation
6. Code Review
7. Security Review
8. Git / Pull Request

O workflow deve ser orientado por estado e não pode pular etapas.

---

# 1. Workflow principal

```text
SPECIFICATION
      │
      ▼
┌─────────────────────────────┐
│ ARCHITECTURE / SECURITY     │
│ AGENT                       │
└──────────────┬──────────────┘
               │
               ▼
┌─────────────────────────────┐
│ TEST AGENT                  │
│ cria testes ANTES do código │
└──────────────┬──────────────┘
               │
               ▼
┌─────────────────────────────┐
│ CODE AGENT                  │
│ implementação               │
└──────────────┬──────────────┘
               │
               ▼
┌─────────────────────────────┐
│ TEST AGENT                  │
│ executa validação            │
└──────────────┬──────────────┘
               │
        ┌──────┴──────┐
        │             │
     FAILED         PASSED
        │             ▼
   CODE AGENT    CODE REVIEW
        │             │
        └──────┐  ┌───┴────┐
               │  │        │
               │ FAIL    PASS
               │  │        │
               │  ▼        ▼
               │ CODE     SECURITY
               │ AGENT    REVIEW
               │  │        │
               │  │     ┌──┴─────┐
               │  │    FAIL     PASS
               │  │     │         │
               │  └─────┘         ▼
               │              GIT / PR
               │                   │
               └───────────────────┘
```

---

# 2. Architecture / Security Agent

Este agente deve ser executado ANTES do Test Agent.

Sua responsabilidade é analisar a specification e determinar:

* arquitetura necessária;
* responsabilidades;
* boundaries;
* SOLID;
* design patterns;
* strategy patterns;
* autenticação;
* autorização;
* validação de entrada;
* segurança de banco;
* exposição de dados;
* exposição de secrets;
* segurança de APIs;
* riscos de IDOR;
* XSS;
* CSRF quando aplicável;
* mass assignment;
* SQL injection;
* privilege escalation;
* race conditions;
* concorrência;
* locking;
* transações;
* cache;
* filas;
* observabilidade;
* tratamento de erros.

Este agente não implementa a solução.

Ele produz um architecture-security-plan.

---

# 3. Análise arquivo por arquivo

Para cada arquivo que será criado ou modificado, o agente deve produzir:

```text
FILE: caminho/do/arquivo

RESPONSIBILITY:
Responsabilidade deste arquivo.

CURRENT_PROBLEM:
Problema existente, caso seja arquivo já existente.

PROPOSED_CHANGE:
Alteração planejada.

ARCHITECTURE:
Como o arquivo se encaixa na arquitetura.

SOLID:
Princípios SOLID envolvidos.

DESIGN_PATTERN:
Design pattern utilizado, quando aplicável.

SECURITY:
Riscos de segurança avaliados.

DATA_ACCESS:
Como os dados são acessados.

AUTHORIZATION:
Como é garantido que o usuário possui permissão.

INPUT:
Como os inputs são validados/sanitizados.

OUTPUT:
Como os dados retornados são protegidos.

DEPENDENCIES:
Dependências utilizadas.

LINES:
Linhas específicas que precisam ser criadas/modificadas.

TESTS:
Testes necessários.

RISKS:
Riscos restantes.
```

---

# 4. Análise linha por linha

Quando um arquivo existente for alterado, o agente deve identificar exatamente as linhas afetadas.

Formato:

```text
FILE: app/Services/PaymentService.php

LINE 42:
Problema:
Acesso direto ao gateway.

Alteração:
Extrair comportamento para uma Strategy.

Motivo:
Evitar acoplamento e permitir novos gateways sem modificar
a regra principal.

Pattern:
Strategy.

SOLID:
Open/Closed Principle.
Dependency Inversion Principle.

SECURITY:
Nenhum segredo pode ser armazenado neste arquivo.

TEST:
Testar cada Strategy individualmente.
```

Quando um novo arquivo for criado:

```text
FILE: app/Strategies/Payment/AppmaxPaymentStrategy.php

LINES 1-XX:

Responsabilidade:
Implementar somente o comportamento específico do Appmax.

Dependencies:
PaymentGatewayClient.

Security:
Credenciais devem ser obtidas através de configuração/secret manager.

Tests:
AppmaxPaymentStrategyTest.
```

Não utilizar descrições genéricas como:

> "Alterar o serviço de pagamento."

A análise deve indicar *arquivo + linha + alteração + motivo*.

---

# 5. SOLID obrigatório

Toda implementação deve avaliar os cinco princípios.

## S — Single Responsibility Principle

Cada classe deve possuir uma responsabilidade clara.

Detectar especialmente:

* Services gigantes;
* Controllers contendo regra de negócio;
* Models contendo regras demais;
* classes que fazem persistência + integração externa + validação;
* métodos excessivamente grandes.

Quando houver múltiplas responsabilidades:

```text
Controller
    ↓
Application Service
    ↓
Domain Service
    ↓
Repository / Gateway
```

quando isso fizer sentido para a arquitetura existente.

---

## O — Open/Closed Principle

Sempre avaliar se novas variações podem ser adicionadas sem modificar uma classe central.

Exemplos:

* gateways;
* tipos de pagamento;
* tipos de venda;
* notificações;
* provedores;
* estratégias de cálculo;
* integrações externas.

Quando houver múltiplos comportamentos variantes, avaliar Strategy.

---

## L — Liskov Substitution Principle

Quando houver interfaces/classes abstratas:

* todas as implementações devem respeitar o contrato;
* nenhuma implementação deve quebrar expectativas da interface;
* evitar subclasses que lançam NotImplementedException para comportamentos obrigatórios;
* validar contratos através de testes.

---

## I — Interface Segregation Principle

Evitar interfaces gigantes.

Preferir:

```text
PaymentProcessor
PaymentRefund
PaymentCapture
PaymentTokenization
```

quando os consumidores não necessitam de todas as operações.

---

## D — Dependency Inversion Principle

Regras de negócio não devem depender diretamente de implementações concretas.

Preferir:

```text
Domain/Application
        ↓
Interface
        ↓
Infrastructure
```

em vez de:

```text
Business Logic
        ↓
Concrete API Client
```

---

# 6. Strategy Pattern

O Architecture Agent deve procurar automaticamente situações em que exista:

```text
if (...)
elseif (...)
elseif (...)
elseif (...)
```

ou:

```text
switch (...)
```

representando comportamentos diferentes.

Antes de adicionar novas condicionais, avaliar Strategy.

Exemplo:

```text
PaymentStrategy
├── AppmaxPaymentStrategy
├── StripePaymentStrategy
└── FirepayPaymentStrategy
```

O agente deve explicar:

* por que Strategy é apropriado;
* qual é a interface;
* quais são as implementações;
* quem resolve a Strategy;
* como os testes serão estruturados.

Não utilizar Strategy artificialmente apenas para cumprir uma regra.

---

# 7. Outros Design Patterns

O Architecture Agent deve avaliar, quando aplicável:

* Strategy;
* Factory;
* Repository;
* Adapter;
* Observer;
* Command;
* State;
* Decorator;
* Builder;
* Specification;
* Chain of Responsibility.

O pattern somente deve ser utilizado quando resolver um problema real.

É proibido adicionar design patterns apenas para aumentar abstração.

---

# 8. DATABASE SECURITY — RLS / Authorization

Toda operação de banco deve ser analisada.

O agente deve verificar:

* quem pode acessar o registro;
* quem pode criar;
* quem pode atualizar;
* quem pode excluir;
* se o usuário pode acessar registros de outro usuário;
* tenant isolation;
* ownership;
* scopes;
* policies;
* authorization server-side.

## Regra crítica

Nunca confiar em:

```javascript
if (user.canEdit) {
    // frontend
}
```

como mecanismo de segurança.

Permissões devem ser verificadas no servidor.

---

# 9. Row-Level Security / Database Locking

Quando o banco utilizar Row-Level Security (RLS), policies ou mecanismo equivalente, o agente deve verificar:

* RLS habilitado quando necessário;
* policies existentes;
* SELECT;
* INSERT;
* UPDATE;
* DELETE;
* isolamento entre tenants;
* bypass de RLS;
* conexões privilegiadas;
* migrations que alteram policies.

Quando o problema for concorrência/transação, avaliar também:

```text
transaction
SELECT ... FOR UPDATE
optimistic locking
pessimistic locking
unique constraints
foreign keys
atomic updates
```

Não assumir que uma verificação prévia em código evita race conditions.

Exemplo inseguro:

```text
if balance >= amount:
    balance -= amount
```

Sem proteção transacional/concorrente.

O agente deve avaliar o comportamento sob requisições simultâneas.

---

# 10. IDOR — Insecure Direct Object Reference

Toda rota que recebe identificadores deve ser auditada.

Exemplos:

```text
/users/{id}

/orders/{id}

/transactions/{id}

/customers/{id}
```

Pergunta obrigatória:

> O usuário autenticado consegue alterar o ID e acessar o recurso de outro usuário?

Nunca considerar o fato de o usuário estar autenticado como suficiente.

Deve existir autorização sobre o recurso.

Preferir mecanismos equivalentes a:

```text
currentUser
    ↓
authorized resource
    ↓
requested ID
```

em vez de simplesmente:

```text
Model::find($id)
```

seguido de retorno do objeto.

---

# 11. Browser Permissions NÃO são Security Boundaries

Qualquer permissão determinada no navegador deve ser considerada manipulável pelo usuário.

Exemplos inseguros:

```javascript
isAdmin = true
```

```javascript
canEdit = true
```

```javascript
role = "admin"
```

```javascript
if (user.permissions.includes("delete")) {
    showDeleteButton();
}
```

Esses mecanismos podem controlar UI/UX, mas nunca autorização real.

O agente deve verificar se a mesma autorização existe no backend.

Regra:

```text
Frontend permission
        ≠
Security permission
```

---

# 12. Hardcoded Secrets

Procurar automaticamente por:

```text
API keys
tokens
passwords
private keys
JWT secrets
database passwords
OAuth secrets
webhook secrets
credentials
```

Também procurar padrões como:

```text
API_KEY = "..."
SECRET = "..."
PASSWORD = "..."
TOKEN = "..."
```

Não permitir secrets no código-fonte.

Preferir:

```text
environment variables
secret manager
application configuration
vault
```

Dependendo da infraestrutura do projeto.

O agente deve verificar também:

* .env;
* .env.example;
* logs;
* frontend bundles;
* Dockerfiles;
* CI/CD;
* configuração de deploy.

## Regra adicional

Qualquer segredo exposto em código client-side deve ser tratado como potencialmente público.

---

# 13. XSS / Input Handling

Todo input externo deve ser considerado não confiável.

Fontes incluem:

* request;
* query parameters;
* route parameters;
* headers;
* cookies;
* uploads;
* webhook payloads;
* banco de dados quando o conteúdo originalmente veio do usuário;
* APIs externas.

O agente deve verificar:

```text
Validation
Sanitization
Encoding
Output escaping
Content-Type
CSP
```

Não confiar somente em sanitização.

A proteção deve acontecer no ponto correto, especialmente na saída.

Exemplo:

```text
User Input
    ↓
Validation
    ↓
Business Logic
    ↓
Storage
    ↓
Context-aware Output Encoding
    ↓
Browser
```

---

# 14. SQL Injection

Toda query dinâmica deve ser analisada.

Procurar:

```text
raw SQL
string concatenation
interpolated queries
dynamic WHERE
dynamic ORDER BY
dynamic column names
```

Preferir:

```text
parameter binding
query builder
ORM
allowlists
```

quando apropriado.

Apenas escapar strings não deve ser considerado estratégia suficiente.

---

# 15. Mass Assignment

Verificar objetos criados a partir de request/input.

Exemplos de risco:

```php
Model::create($request->all());
```

ou equivalentes.

Verificar:

* campos permitidos;
* DTOs;
* validation;
* allowlists;
* protected fields;
* ownership fields;
* role/permission fields.

Nunca permitir que o usuário defina diretamente:

```text
user_id
tenant_id
role
is_admin
approved
status
payment_status
```

quando esses campos deveriam ser controlados pelo backend.

---

# 16. Authentication

Para qualquer endpoint protegido verificar:

* autenticação obrigatória;
* sessão/token;
* expiração;
* refresh;
* logout;
* invalidation;
* brute force;
* rate limiting;
* permissões.

---

# 17. Authorization

Toda operação sensível deve possuir autorização server-side.

Verificar:

```text
Authentication
        ↓
Authorization
        ↓
Resource ownership
        ↓
Business rules
        ↓
Mutation
```

Não permitir:

```text
Authentication
        ↓
Mutation
```

sem autorização quando o recurso for protegido.

---

# 18. API Security

Para cada endpoint:

```text
METHOD
ROUTE
AUTHENTICATION
AUTHORIZATION
INPUT
OUTPUT
RATE LIMIT
ERROR HANDLING
LOGGING
```

deve ser documentado.

Exemplo:

```text
POST /api/transactions/{id}/refund

Authentication:
required

Authorization:
transaction owner OR authorized role

Input:
validated DTO

Resource:
must belong to authorized tenant

Mutation:
transactional

Concurrency:
must prevent duplicate refund

Output:
never expose internal provider credentials
```

---

# 19. Data Exposure

Verificar se APIs retornam dados desnecessários.

Nunca retornar automaticamente:

```text
password
password_hash
tokens
API keys
internal IDs
internal credentials
security metadata
private customer data
```

quando não forem necessários.

Preferir DTOs/Resources/ViewModels para definir explicitamente o contrato de saída.

---

# 20. Error Handling

Erros não devem expor:

* stack traces em produção;
* SQL;
* credentials;
* filesystem paths;
* internal service URLs;
* secrets;
* informações sensíveis.

O agente deve verificar logs separadamente da resposta enviada ao cliente.

---

# 21. Frontend Security

Para código frontend, verificar:

* permissões manipuláveis;
* dados vindos diretamente da URL;
* XSS;
* innerHTML;
* HTML não confiável;
* tokens armazenados de forma insegura;
* secrets em bundle;
* chamadas de API sem autorização server-side;
* exposição de IDs;
* controle de acesso baseado somente na UI.

---

# 22. Test Agent — Security Tests

O Test Agent deve criar testes para os riscos encontrados.

Obrigatoriamente, quando aplicável:

### Authorization

```text
usuário A não acessa recurso do usuário B
```

### IDOR

```text
GET /resource/ID_B
```

deve retornar:

```text
403
```

ou:

```text
404
```

conforme a política da aplicação.

### Privilege escalation

Usuário comum não pode executar operação administrativa.

### Tenant isolation

Tenant A não pode acessar dados de Tenant B.

### Mass assignment

Campos protegidos enviados pelo usuário devem ser ignorados/rejeitados.

### XSS

Payloads maliciosos não podem resultar em execução de HTML/JS no contexto vulnerável.

### Injection

Inputs maliciosos não podem alterar a estrutura da query.

### Secrets

Nenhum secret deve aparecer em resposta, log ou código client-side.

### Concurrency

Operações concorrentes devem respeitar invariantes de negócio.

---

# 23. Code Agent — Implementation Rules

O Code Agent deve implementar seguindo o architecture-security-plan.

Ele não pode:

* remover testes;
* enfraquecer assertions;
* desabilitar segurança;
* ignorar authorization;
* colocar secrets no código;
* confiar em permissões do frontend;
* usar find($id) sem analisar authorization;
* aceitar request->all() em operações sensíveis;
* criar queries inseguras;
* ignorar problemas de concorrência;
* introduzir abstrações desnecessárias.

---

# 24. Test Gate

Após qualquer alteração do Code Agent:

```text
CODE AGENT
    ↓
TEST AGENT
```

O Test Agent deve executar:

1. testes novos;
2. testes da área alterada;
3. testes de segurança relevantes;
4. testes de integração relevantes;
5. suite completa quando viável.

Se qualquer teste obrigatório falhar:

```text
TESTS_FAILED
```

e voltar para:

```text
CODE AGENT
```

---

# 25. Code Review Gate

Somente depois de:

```text
TESTS_PASSED
```

o Code Review Agent pode executar.

O reviewer deve revisar:

### Architecture

* SOLID;
* patterns;
* coupling;
* cohesion;
* abstrações;
* boundaries.

### Security

* RLS;
* authorization;
* IDOR;
* privilege escalation;
* XSS;
* injection;
* secrets;
* mass assignment;
* authentication;
* API exposure.

### Code Quality

* naming;
* readability;
* complexity;
* duplication;
* maintainability;
* error handling;
* performance.

---

# 26. Security Review Gate

A revisão de segurança é obrigatória.

O reviewer deve produzir:

```yaml
security_review:
  rls: PASS
  authorization: PASS
  idor: PASS
  frontend_permissions: PASS
  hardcoded_secrets: PASS
  xss: PASS
  sql_injection: PASS
  mass_assignment: PASS
  authentication: PASS
  data_exposure: PASS
  concurrency: PASS
```

Caso qualquer item seja:

```text
FAIL
```

o review deve ser:

```text
REVIEW_CHANGES_REQUESTED
```

---

# 27. File-by-file Review

O Code Review Agent deve revisar os arquivos alterados individualmente.

Formato obrigatório:

```text
FILE: app/Http/Controllers/TransactionController.php

LINES 35-48

Finding:
...

Severity:
CRITICAL / HIGH / MEDIUM / LOW

Category:
IDOR / Authorization / SOLID / Architecture / XSS / etc.

Problem:
...

Why:
...

Required change:
...

Test required:
...
```

Depois:

```text
FILE: app/Services/TransactionService.php

LINES 72-104

Finding:
...

Severity:
...

Category:
...

Required change:
...
```

Nenhum arquivo alterado pode ser ignorado.

---

# 28. Severity

Usar:

```text
CRITICAL
HIGH
MEDIUM
LOW
INFO
```

## CRITICAL

* authentication bypass;
* authorization bypass;
* IDOR com dados sensíveis;
* exposição de secrets;
* RLS bypass;
* remote code execution;
* corrupção grave de dados.

## HIGH

* privilege escalation;
* XSS relevante;
* SQL injection;
* tenant isolation failure;
* exposição significativa de dados.

## MEDIUM

* validação insuficiente;
* ausência de rate limiting relevante;
* problema arquitetural com impacto de segurança;
* race condition potencial.

## LOW

* melhoria de hardening;
* pequena exposição;
* problema de manutenção sem exploração significativa.

## INFO

Melhoria ou observação sem bloqueio.

Qualquer CRITICAL ou HIGH bloqueia o PR.

---

# 29. Review Loop

Quando o review encontrar problemas:

```text
REVIEW_CHANGES_REQUESTED
        ↓
CODE AGENT
        ↓
TEST AGENT
        ↓
TESTS_PASSED
        ↓
CODE REVIEW AGENT
```

Nunca:

```text
REVIEW_CHANGES_REQUESTED
        ↓
CODE AGENT
        ↓
PR
```

---

# 30. Diff Security Review

Antes do PR, o Git Agent deve analisar:

```bash
git diff
```

e:

```bash
git status
```

Verificar:

* arquivos inesperados;
* secrets;
* .env;
* credentials;
* debug code;
* dumps;
* logs;
* arquivos temporários;
* migrations inesperadas;
* alterações de permissões;
* alterações de configuração;
* dependências adicionadas.

---

# 30.1 Commit e Pull Request — Boas práticas (obrigatório)

O Git Agent segue boas práticas de GitHub. Títulos e descrições devem ser **assertivos**: dizem o que a mudança entrega e por quê, sem vaguidade.

Não é aceitável:

```text
update
fix
ajustes
melhorias
WIP
various changes
update files
```

## Commit

* Conventional Commits: `tipo(escopo): resumo no imperativo`.
* Tipos: `feat`, `fix`, `docs`, `test`, `refactor`, `chore`, `security`.
* O assunto foca o **porquê**, não o inventário de arquivos.
* Um assunto, no máximo ~72 caracteres, sem ponto final.
* Corpo (quando necessário): 1–2 frases com o motivo e o efeito. Sem repetir o diff.
* Um commit = uma mudança lógica. Evitar commits gigantes que escondem o progresso.
* Não pular hooks. Não commitar `.env`, secrets, dumps, logs ou arquivos temporários.

Exemplos:

```text
# ruim
update payment
ajustes no código

# assertivo
feat(payments): isolate gateways behind a payment strategy

Prevent new providers from changing the core charge flow.
```

## Pull Request

* Título assertivo no mesmo padrão do commit: o que o PR entrega, não "changes" ou o nome da branch.
* Descrição obrigatória com:

```text
## Summary
- [1–3 bullets: resultado e motivo]

## Test plan
- [como validar: testes, rotas, casos de borda]
```

* Incluir o vínculo com a specification / feature quando existir.
* O título do PR não pode ser genérico; a descrição não pode ser vazia nem "see commits".

## Branching (obrigatório)

```text
main
  └── development          ← criada a partir de main; base de integração
        └── feature/…      ← trabalho; origem de todo PR
```

* O Git Agent cria `development` a partir de `main` se ela ainda não existir e faz push da branch.
* Feature branches nascem de `development`, nunca de `main`.
* **Todo PR aponta para `development`.** Nunca abrir PR contra `main` ou `master`.
* Não push direto em `main`/`master`. Não push direto em `development` com trabalho de feature (usar PR). Sem force push nessas branches.

Tarefas só de configuração SDD/tooling (sem código de aplicação) podem ser executadas somente pelo Git Agent, ainda assim com commit e PR assertivos contra `development`.

O estado `PR_CREATED` só vale quando título e descrição do commit e do PR forem assertivos **e** o PR tiver base `development`.

---

# 31. Dependency Security

Quando uma dependência nova for adicionada, avaliar:

* necessidade;
* manutenção;
* licença;
* versão;
* vulnerabilidades conhecidas;
* impacto no bundle;
* permissões;
* supply-chain risk.

Não adicionar dependências apenas para resolver problemas simples.

---

# 32. Definition of Done

A implementação somente pode ser considerada concluída quando:

```text
[PASS] Specification
[PASS] Architecture
[PASS] SOLID
[PASS] Design Patterns
[PASS] Security Architecture
[PASS] Tests Created
[PASS] Implementation
[PASS] Tests
[PASS] Security Tests
[PASS] Code Review
[PASS] Security Review
[PASS] Git Diff Review
[PASS] Secrets Check
[PASS] Commit e PR assertivos
[PASS] PR
```

O estado final deve ser:

```text
PR_CREATED
```

---

# 33. Final Report

Ao terminar, gerar:

```text
IMPLEMENTATION SUMMARY

Specification:
...

Files created:
...

Files modified:
...

Architecture:
...

SOLID:
...

Design Patterns:
...

Security:
- RLS:
- Authorization:
- IDOR:
- Frontend permissions:
- Hardcoded secrets:
- XSS:
- SQL Injection:
- Mass Assignment:
- Authentication:
- Data exposure:
- Concurrency:

Tests:
...

Code Review:
APPROVED

Commit:
[título assertivo + corpo com o porquê]

PR:
[título assertivo]
[URL]
[Summary + Test plan]
```

---

# 34. Regra absoluta

O objetivo não é apenas fazer os testes passarem.

O objetivo é produzir código:

```text
CORRETO
+
TESTADO
+
SEGURO
+
MANUTENÍVEL
+
ARQUITETURALMENTE CONSISTENTE
```

Um teste passando não significa que a implementação está correta.

Um code review aprovado não significa que os testes podem ser ignorados.

Uma implementação funcionando no frontend não significa que a autorização está correta.

Um usuário autenticado não significa que ele pode acessar qualquer ID.

Uma variável de ambiente no frontend não deve ser considerada secreta.

E uma operação aparentemente simples de banco não deve ser considerada segura sem analisar concorrência, autorização e isolamento de dados.
