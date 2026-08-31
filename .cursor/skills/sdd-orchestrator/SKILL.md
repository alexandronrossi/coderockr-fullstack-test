---
name: sdd-orchestrator
description: Orchestrates the SDD multi-agent workflow with mandatory gates from specification to PR. Use when implementing a specification, feature, or Spec Kit plan; when the user mentions SDD, gates, architecture-security-plan, or PR_CREATED; and before writing application code from a spec.
---

# SDD Orchestrator

Source of truth: `.specify/memory/sdd-multi-agent.md`
Constitution: `.specify/memory/constitution.md`

Nenhuma implementação pode ser considerada concluída sem passar pelos gates. O workflow é orientado por estado e **não pode pular etapas**.

## Gates

1. Specification Analysis
2. Architecture & Security Analysis
3. Test Creation
4. Implementation
5. Test Validation
6. Code Review
7. Security Review
8. Git / Pull Request

## Workflow

```text
SPECIFICATION
      │
      ▼
ARCHITECTURE / SECURITY AGENT
      │
      ▼
TEST AGENT (cria testes ANTES do código)
      │
      ▼
CODE AGENT
      │
      ▼
TEST AGENT (validação)
      │
 ┌────┴────┐
FAILED   PASSED
 │         │
 CODE     CODE REVIEW
 AGENT      │
 │       ┌──┴──┐
 │     FAIL  PASS
 │      │     │
 │     CODE  SECURITY REVIEW
 │     AGENT   │
 │      │   FAIL / PASS
 │      │     │
 └──────┴─────┴── GIT / PR
```

## State machine

Track and emit the current state after every gate:

| State | Next |
|---|---|
| `SPECIFICATION` | Architecture / Security Agent |
| `ARCHITECTURE_SECURITY_PLAN_READY` | Test Agent (create) |
| `TESTS_CREATED` | Code Agent |
| `IMPLEMENTATION_DONE` | Test Agent (validate) |
| `TESTS_FAILED` | Code Agent |
| `TESTS_PASSED` | Code Review Agent |
| `REVIEW_CHANGES_REQUESTED` | Code Agent → Test Agent → `TESTS_PASSED` → Code Review |
| `CODE_REVIEW_PASSED` | Security Review Agent |
| `SECURITY_REVIEW_PASSED` | Git Agent |
| `PR_CREATED` | Done |

Forbidden: `REVIEW_CHANGES_REQUESTED` → Code Agent → PR.

## Per-gate skills

Before each gate, **read** the skill and follow it:

1. Architecture / Security: `.cursor/skills/sdd-architecture-security/SKILL.md`
2. Test create / validate: `.cursor/skills/sdd-test-agent/SKILL.md`
3. Implementation: `.cursor/skills/sdd-code-agent/SKILL.md`
4. Code review: `.cursor/skills/sdd-code-review/SKILL.md`
5. Security review: `.cursor/skills/sdd-security-review/SKILL.md`
6. Git / PR: `.cursor/skills/sdd-git-pr/SKILL.md`

Write the architecture-security-plan to `specs/<feature>/architecture-security-plan.md` using `.specify/templates/architecture-security-plan.md`.

## Definition of Done

Implementation is complete only when:

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

Final state: `PR_CREATED` only when commit and PR titles/descriptions are assertive (SDD 30.1) and the PR targets `development` (created from `main`). Then emit the Implementation Summary from section 33 of the SDD.

## Absolute rule

O objetivo não é apenas fazer os testes passarem. Produzir código:

```text
CORRETO + TESTADO + SEGURO + MANUTENÍVEL + ARQUITETURALMENTE CONSISTENTE
```
