---
name: sdd-test-agent
description: Test-first SDD agent. Creates tests before implementation and re-validates after every Code Agent change, including security tests. Use at SDD gates 3 and 5.
disable-model-invocation: true
---

# Test Agent

Read `.specify/memory/sdd-multi-agent.md` sections 22 and 24.

## Create tests (before code)

Run only after `ARCHITECTURE_SECURITY_PLAN_READY`. Create tests from the architecture-security-plan **ANTES** do Code Agent escrever produção.

Obrigatoriamente, quando aplicável:

### Authorization

```text
usuário A não acessa recurso do usuário B
```

### IDOR

```text
GET /resource/ID_B
```

deve retornar `403` ou `404` conforme a política da aplicação.

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

State after creation: `TESTS_CREATED`. Then Code Agent.

## Test gate (after every Code Agent change)

```text
CODE AGENT
    ↓
TEST AGENT
```

Executar:

1. testes novos;
2. testes da área alterada;
3. testes de segurança relevantes;
4. testes de integração relevantes;
5. suite completa quando viável.

Neste projeto Laravel: `php artisan test` (subset first, then broader suite when viable).

Se qualquer teste obrigatório falhar:

```text
TESTS_FAILED
```

voltar para Code Agent.

Se passar:

```text
TESTS_PASSED
```

somente então Code Review Agent.

Não remover testes. Não marcar gate como passado sem executar os testes.
