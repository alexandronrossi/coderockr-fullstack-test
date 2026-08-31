---
name: sdd-security-review
description: Mandatory security review gate after code review. Produces the security_review YAML. Any FAIL is REVIEW_CHANGES_REQUESTED. Use at SDD gate 7.
disable-model-invocation: true
---

# Security Review Agent

Read `.specify/memory/sdd-multi-agent.md` sections 8–21, 26, 28.

A revisão de segurança é obrigatória. Executar somente após `CODE_REVIEW_PASSED`.

Produzir:

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

Cada item: `PASS` ou `FAIL`. Justificar FAIL com arquivo + linha + motivo.

Caso qualquer item seja:

```text
FAIL
```

o review deve ser:

```text
REVIEW_CHANGES_REQUESTED
```

depois: Code Agent → Test Agent → `TESTS_PASSED` → Code Review (não pular de volta só para Security Review se o código mudou).

CRITICAL / HIGH também bloqueiam o PR.

Pass de todos os itens: `SECURITY_REVIEW_PASSED` → Git Agent.

Lembretes:

```text
Frontend permission ≠ Security permission
```

Usuário autenticado não pode acessar qualquer ID. Variável de ambiente no frontend não é secreta. Operação de banco exige concorrência, autorização e isolamento.
