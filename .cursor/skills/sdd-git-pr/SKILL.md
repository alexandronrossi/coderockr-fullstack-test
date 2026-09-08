---
name: sdd-git-pr
description: Git and Pull Request gate after security review. Diff review, secrets check, then GitHub commits and PRs with assertive titles and descriptions. Use at SDD gate 8 when the SDD workflow reaches Git/PR.
disable-model-invocation: true
---

# Git / Pull Request Agent

Read `.specify/memory/sdd-multi-agent.md` sections 30–33 (incluindo **30.1 Commit e Pull Request**).

Executar somente após `SECURITY_REVIEW_PASSED`.

## Diff security review

Analisar:

```bash
git diff
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

Não commitar `.env`, credentials, dumps, logs ou arquivos temporários.

## Dependency security

Quando uma dependência nova for adicionada, avaliar: necessidade; manutenção; licença; versão; vulnerabilidades conhecidas; impacto no bundle; permissões; supply-chain risk. Não adicionar dependências apenas para resolver problemas simples.

## Commits — boas práticas (obrigatório)

Títulos e descrições **assertivos**. Dizer o que a mudança entrega e por quê.

Proibido: `update`, `fix`, `ajustes`, `melhorias`, `WIP`, `various changes`, `update files`.

Regras:

* Conventional Commits: `tipo(escopo): resumo no imperativo`.
* Tipos: `feat`, `fix`, `docs`, `test`, `refactor`, `chore`, `security`.
* Assunto foca o **porquê**, não a lista de arquivos. Sem ponto final. ~72 caracteres.
* Corpo (quando necessário): 1–2 frases com motivo e efeito. Não repetir o diff.
* Um commit = uma mudança lógica. Evitar commits gigantes.
* Não pular hooks.

```text
# ruim
update payment
ajustes no código

# assertivo
feat(payments): isolate gateways behind a payment strategy

Prevent new providers from changing the core charge flow.
```

Passar a mensagem via HEREDOC (não `-m` empilhado). Seguir o protocolo git do projeto (status, diff, log, add, commit, status).

## Pull Request — boas práticas (obrigatório)

* Título assertivo no mesmo padrão: o que o PR entrega. Não usar o nome da branch nem "changes".
* Descrição obrigatória:

```text
## Summary
- [1–3 bullets: resultado e motivo]

## Test plan
- [como validar: testes, rotas, casos de borda]
```

* Vincular a specification / feature quando existir.
* Título não pode ser genérico; descrição não pode ser vazia nem "see commits".

## Branching (obrigatório)

```text
main
  └── development          ← criada a partir de main; base de integração
        └── feature/…      ← trabalho; origem de todo PR
```

* Criar `development` a partir de `main` se ainda não existir; fazer push da branch.
* Feature branches nascem de `development`, nunca de `main`.
* **Todo PR aponta para `development`** (`gh pr create --base development`). Nunca abrir PR contra `main`/`master`.
* Não push direto em `main`/`master`. Não push direto em `development` com trabalho de feature. Sem force push nessas branches.

Tarefas só de configuração SDD/tooling (sem código de aplicação) podem ser só do Git Agent, ainda assim com commit e PR assertivos contra `development`.

`PR_CREATED` só vale com título e descrição assertivos no commit **e** no PR, e com base `development`.

## Graphify (obrigatório no fim do PR)

Se o Graphify existir neste repo:

1. Consultar o grafo **antes** de Grep/Read na revisão de diff, quando precisar de contexto de arquitetura.
2. **Refazer o grafo** imediatamente antes do commit final:

```bash
graphify update .
```

Se `graphify-out/graph.json` não existir: `graphify extract . --code-only`.

3. Incluir `graphify-out/` no PR (não commitar `graphify-out/cost.json`). Sem rebuild, não fechar o PR.

Se o CLI ou o grafo não existirem, seguir só com o código e documentar a ausência no Implementation Summary.

## Final report

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
