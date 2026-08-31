# Architecture-Security Plan: [FEATURE]

**Branch**: `[###-feature-name]` | **Date**: [DATE] | **Spec**: [link]

**Agent**: Architecture / Security Agent
**State**: ARCHITECTURE_SECURITY_ANALYSIS
**Implementation**: this agent MUST NOT implement the solution

**Input**: Feature specification from `/specs/[###-feature-name]/spec.md`

## Architecture

[Arquitetura necessária, responsabilidades, boundaries]

## SOLID

[Avaliação dos cinco princípios]

## Design Patterns

[Patterns reais somente. Se Strategy: interface, implementações, resolver, testes]

## Security Architecture

[authn, authz, validação, banco, secrets, APIs, IDOR, XSS, CSRF, mass assignment, injection, privilege escalation, race conditions, locking, transações, cache, filas, observabilidade, erros]

## API Inventory

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

## File-by-file Analysis

Para cada arquivo criado ou modificado, usar o formato obrigatório:

```text
FILE: caminho/do/arquivo

RESPONSIBILITY:
CURRENT_PROBLEM:
PROPOSED_CHANGE:
ARCHITECTURE:
SOLID:
DESIGN_PATTERN:
SECURITY:
DATA_ACCESS:
AUTHORIZATION:
INPUT:
OUTPUT:
DEPENDENCIES:
LINES:
TESTS:
RISKS:
```

## Line-by-line Analysis

Arquivos existentes: `FILE` + `LINE N` + Problema + Alteração + Motivo + Pattern + SOLID + SECURITY + TEST.

Arquivos novos: `FILE` + `LINES 1-XX` + Responsabilidade + Dependencies + Security + Tests.

A análise deve indicar *arquivo + linha + alteração + motivo*. Não usar descrições genéricas.

## Required Tests

[Lista de testes, incluindo testes de segurança aplicáveis]

## Remaining Risks
