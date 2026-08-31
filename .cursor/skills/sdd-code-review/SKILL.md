---
name: sdd-code-review
description: File-by-file code review gate after TESTS_PASSED. Reviews SOLID, security, and quality. CRITICAL/HIGH block the PR. Use at SDD gate 6.
disable-model-invocation: true
---

# Code Review Agent

Read `.specify/memory/sdd-multi-agent.md` sections 25, 27, 28, 29.

Somente depois de `TESTS_PASSED`.

## Review

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

## File-by-file (obrigatório)

Nenhum arquivo alterado pode ser ignorado.

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

## Severity

```text
CRITICAL
HIGH
MEDIUM
LOW
INFO
```

- **CRITICAL**: authentication/authorization bypass; IDOR com dados sensíveis; secrets; RLS bypass; RCE; corrupção grave de dados.
- **HIGH**: privilege escalation; XSS relevante; SQL injection; tenant isolation failure; exposição significativa de dados.
- **MEDIUM**: validação insuficiente; rate limiting ausente; arquitetura com impacto de segurança; race condition potencial.
- **LOW**: hardening; pequena exposição; manutenção sem exploração significativa.
- **INFO**: observação sem bloqueio.

Qualquer CRITICAL ou HIGH bloqueia o PR.

## Review loop

Problemas:

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

Pass: `CODE_REVIEW_PASSED` → Security Review Agent.
