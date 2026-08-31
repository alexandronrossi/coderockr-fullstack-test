# Coderockr Fullstack Constitution

## Core Principles

### I. Specification-First

Nenhuma implementação começa no código. Toda entrega parte de uma specification analisada. O estado inicial do workflow é `SPECIFICATION`. Sem spec, não há architecture-security-plan, testes nem código.

### II. Architecture & Security Before Tests

O Architecture / Security Agent executa ANTES do Test Agent. Ele não implementa. Ele produz `architecture-security-plan` com análise arquivo por arquivo e linha por linha (arquivo + linha + alteração + motivo). SOLID, design patterns e segurança (authn/authz, IDOR, RLS, secrets, XSS, injection, mass assignment, concorrência) são gates de planejamento, não afterthoughts.

### III. Test-First (NON-NEGOTIABLE)

O Test Agent cria testes ANTES da implementação, inclusive testes de segurança aplicáveis (authorization, IDOR, privilege escalation, tenant isolation, mass assignment, XSS, injection, secrets, concurrency). Depois de qualquer alteração do Code Agent, o Test Agent valida. `TESTS_FAILED` volta para o Code Agent. Nunca seguir para review com testes falhando.

### IV. State-Driven Workflow (No Skipped Gates)

O workflow é orientado por estado e não pode pular etapas:

1. Specification Analysis
2. Architecture & Security Analysis
3. Test Creation
4. Implementation
5. Test Validation
6. Code Review
7. Security Review
8. Git / Pull Request

`REVIEW_CHANGES_REQUESTED` nunca vai direto para PR. Sempre: Code Agent → Test Agent → `TESTS_PASSED` → Code Review.

### V. Server-Side Authorization Is the Only Security Boundary

Permissão de frontend não é security boundary. Autenticação não implica autorização sobre um ID. `Model::find($id)` sem ownership/policy é insuficiente. Browser flags (`isAdmin`, `canEdit`, `role`) só controlam UI. Secrets não entram no código-fonte nem em bundles client-side. Operação de banco exige autorização, isolamento e análise de concorrência.

## SOLID & Design Patterns

Toda implementação avalia S, O, L, I e D. Strategy somente quando houver variações reais de comportamento (não para inflar abstração). Patterns (Factory, Repository, Adapter, Observer, Command, State, Decorator, Builder, Specification, Chain of Responsibility) só quando resolverem um problema real.

Camadas preferidas quando houver múltiplas responsabilidades:

```text
Controller → Application Service → Domain Service → Repository / Gateway
```

Regras de negócio dependem de interfaces, não de clientes concretos.

## Security Requirements

Toda operação de banco analisa quem pode SELECT/INSERT/UPDATE/DELETE, tenant isolation, ownership, policies e authorization server-side. Toda rota com identificador é auditada contra IDOR. Inputs externos são não confiáveis. Queries dinâmicas usam binding/ORM/allowlists. Mass assignment usa allowlist; o cliente nunca define `user_id`, `tenant_id`, `role`, `is_admin`, `approved`, `status` ou `payment_status` quando esses campos são do backend. APIs documentam METHOD, ROUTE, AUTHENTICATION, AUTHORIZATION, INPUT, OUTPUT, RATE LIMIT, ERROR HANDLING, LOGGING. Erros de produção não expõem stack traces, SQL, credentials, paths ou secrets.

CRITICAL e HIGH bloqueiam o PR. Qualquer `FAIL` no `security_review` produz `REVIEW_CHANGES_REQUESTED`.

Fonte normativa completa: `.specify/memory/sdd-multi-agent.md`.

## Development Workflow

Agentes especializados (não um único passo de "escrever código"):

- Architecture / Security Agent → `architecture-security-plan`
- Test Agent → testes primeiro, depois validação
- Code Agent → implementação fiel ao plan; não remove testes, não enfraquece assertions, não desabilita segurança
- Code Review Agent → arquivo por arquivo, com severity
- Security Review Agent → checklist YAML obrigatório
- Git Agent → cria `development` a partir de `main`; `git diff` / `git status`; secrets check; commits e PRs assertivos **sempre com base `development`** (seção 30.1)

Definition of Done exige todos os itens `[PASS]` da seção 32 do SDD, inclusive **Commit e PR assertivos**. Estado final: `PR_CREATED` com PR apontando para `development`. Relatório final no formato da seção 33.

## Governance

Esta constituição prevalece sobre conveniência, prazo e "os testes passaram". Emendas exigem atualização deste arquivo, de `.specify/memory/sdd-multi-agent.md` e das skills em `.cursor/skills/sdd-*`. PRs e reviews verificam conformidade com os gates. Complexidade e design patterns exigem justificativa no architecture-security-plan.

**Version**: 1.0.2 | **Ratified**: 2026-08-31 | **Last Amended**: 2026-08-31
