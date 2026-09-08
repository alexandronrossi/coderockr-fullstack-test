# Architecture-Security Plan: Interface web de investimentos

**Branch**: `feat/investment-ui` (`004-investment-ui`) | **Date**: 2026-09-08 | **Spec**: [spec.md](./spec.md)

**Agent**: Architecture / Security Agent
**State**: ARCHITECTURE_SECURITY_PLAN_READY
**Implementation**: this agent MUST NOT implement the solution

**Input**: Feature specification from `/specs/004-investment-ui/spec.md`

## Architecture

```text
Browser (React SPA :5173)
        ↓  HTTPS/HTTP JSON + Bearer
Laravel API (:8000)  — INALTERADA nesta feature
        ↓
Auth + Investment services + Domain valuation (já entregues)
```

**Boundaries**:
- **UI**: apresentação, navegação, validação de formato, sessão local (token).
- **API**: única fonte de verdade para números, papéis e autorização (IDOR 404).
- **Proibido no cliente**: composto 0,52%, faixas de IR, filtrar lista por `owner.id` como “segurança”, confiar em `role` para liberar dados.

**Não fazer**: novos endpoints, Scribe, mudar Policy/domínio, Inertia, cookie Sanctum stateful, cadastro público.

## SOLID

| Princípio | Aplicação |
|-----------|-----------|
| S | `client.ts` HTTP; `session.ts` storage; páginas = uma rota; `WithdrawForm` só resgate |
| O | Nova tela = nova page; API client estende por módulo (`auth.ts`, `investments.ts`) |
| L | Tipos `Investment` / `User` espelham API sem “super-user” no cliente |
| I | Funções de API por recurso, sem god-client |
| D | Pages dependem de módulos api/auth, não de `fetch` espalhado |

Sem Strategy Admin/Owner no frontend.

## Design Patterns

- **Protected route** (`RequireAuth`): gate de UX, não de segurança.
- **API module / thin client**: Adapter sobre `fetch`.
- **Não** Repository, **não** Strategy de papéis, **não** state manager global obrigatório (React state + sessionStorage bastam).

## Security Architecture

**Authentication**: Bearer Sanctum via token em `sessionStorage`. Login público. 401 → clear + `/login`.

**Authorization**: **Só no servidor**. UI pode esconder “Resgatar” se `status === 'withdrawn'`, mas Owner com URL alheia ainda chama GET e recebe 404. `role` no header é cosmético.

**Validação**: UX no formulário; servidor valida de novo. Nunca mass-assign de campos de controle na UI (campos inexistentes).

**Banco / RLS / injection / concurrency**: N/A no frontend. API já cobre lock de resgate.

**Secrets**: `VITE_API_URL` é público. Sem senhas seed no bundle. Token não em logs de console em produção; não commitar `.env` do frontend com dados reais além do example.

**XSS**: React escapa texto por padrão; não usar `dangerouslySetInnerHTML` com dados da API. Não refletir HTML de mensagens de erro.

**CSRF**: Bearer (não cookie session) → CSRF clássico N/A. `supports_credentials` CORS permanece `false`.

**IDOR**: Teste: Owner navega para `/investments/{idBob}` → UI mostra not-found; network sem body com amount. Confiança na API 404.

**Privilege escalation**: UI sem campo role; não enviar `role` no login.

**Mass assignment (cliente)**: Create/withdraw payloads allowlist explícita no código do client.

**Data exposure**: Não mostrar token na UI; não persistir password.

**Frontend permission**: Documentar explicitamente que esconder botão ≠ authz.

**Observabilidade**: Erros de rede amigáveis; sem stack traces.

## API Inventory

Nenhum endpoint **novo**. Consumo (já existentes):

```text
METHOD: POST
ROUTE: /api/login
AUTHENTICATION: none
AUTHORIZATION: none
INPUT: { email, password } — UI NÃO envia role/user_id
OUTPUT: { token, user }
RATE LIMIT: throttle:login (servidor)
ERROR HANDLING: 401 genérico; 422 validação
LOGGING: não logar password/token no browser
```

```text
METHOD: POST
ROUTE: /api/logout
AUTHENTICATION: Bearer
…
```

```text
METHOD: GET
ROUTE: /api/user
AUTHENTICATION: Bearer
…
```

```text
METHOD: GET | POST
ROUTE: /api/investments , /api/investments/{id} , /api/investments/{id}/withdraw
AUTHENTICATION: Bearer
AUTHORIZATION: Policy + visibleTo (servidor)
INPUT: create { amount, created_on }; withdraw { withdrawn_on }; list page/per_page
OUTPUT: Investment Resource / page
RATE LIMIT: throttle:investments
ERROR HANDLING: 401 / 404 IDOR / 422
LOGGING: não logar Bearer
```

## File-by-file Analysis

```text
FILE: frontend/package.json

RESPONSIBILITY:
Deps e scripts da SPA (dev, build, test).

CURRENT_PROBLEM:
Pasta frontend inexistente.

PROPOSED_CHANGE:
Criar com react, react-dom, react-router-dom, vite, typescript, vitest, @testing-library/react.

ARCHITECTURE:
Raiz do app UI.

SOLID:
N/A.

DESIGN_PATTERN:
Nenhum.

SECURITY:
Sem deps que embutam secrets; pin ranges razoáveis.

DATA_ACCESS:
Nenhum.

AUTHORIZATION:
N/A.

INPUT:
N/A.

OUTPUT:
N/A.

DEPENDENCIES:
npm registry.

LINES:
Novo.

TESTS:
npm test script.

RISKS:
Duplicar Vite do Laravel root — manter separado.
```

```text
FILE: frontend/vite.config.ts

RESPONSIBILITY:
Build/dev server 5173.

CURRENT_PROBLEM:
Novo.

PROPOSED_CHANGE:
Plugin React; alias `@`; test config Vitest jsdom.

ARCHITECTURE:
Tooling.

SOLID:
N/A.

DESIGN_PATTERN:
Nenhum.

SECURITY:
Não proxy com credenciais perigosas; API via CORS explícito.

DATA_ACCESS:
N/A.

AUTHORIZATION:
N/A.

INPUT:
N/A.

OUTPUT:
Bundle.

DEPENDENCIES:
vite, @vitejs/plugin-react.

LINES:
Novo.

TESTS:
N/A.

RISKS:
Proxy mal configurado escondendo CORS — preferir CORS Laravel já ok.
```

```text
FILE: frontend/.env.example

RESPONSIBILITY:
VITE_API_URL público.

CURRENT_PROBLEM:
Novo.

PROPOSED_CHANGE:
VITE_API_URL=http://localhost:8000/api

ARCHITECTURE:
Config client.

SOLID:
N/A.

DESIGN_PATTERN:
Nenhum.

SECURITY:
Só URL pública; nunca SEED_* passwords aqui.

DATA_ACCESS:
N/A.

AUTHORIZATION:
N/A.

INPUT:
N/A.

OUTPUT:
N/A.

DEPENDENCIES:
Vite env.

LINES:
Novo.

TESTS:
Client lê import.meta.env.

RISKS:
Commitar .env real — gitignore frontend/.env.
```

```text
FILE: frontend/src/api/client.ts

RESPONSIBILITY:
fetch + Bearer + erros tipados.

CURRENT_PROBLEM:
Novo.

PROPOSED_CHANGE:
apiRequest(method, path, body?); anexa token; 401 dispara clearSession.

ARCHITECTURE:
Adapter HTTP.

SOLID:
S — só transporte.

DESIGN_PATTERN:
Thin client / Adapter.

SECURITY:
Não colocar token em query string; não logar Authorization.

DATA_ACCESS:
HTTP only.

AUTHORIZATION:
Delega ao servidor.

INPUT:
path, body allowlist pelos callers.

OUTPUT:
JSON tipado ou ApiError.

DEPENDENCIES:
session.getToken.

LINES:
Novo (~80).

TESTS:
Mock fetch: 401 limpa sessão; header Bearer presente.

RISKS:
Swallow de erros 404 com dados fake.
```

```text
FILE: frontend/src/api/auth.ts

RESPONSIBILITY:
login, logout, me.

CURRENT_PROBLEM:
Novo.

PROPOSED_CHANGE:
login({email,password}) only those keys; logout(); opcional me().

ARCHITECTURE:
API module.

SOLID:
S.

DESIGN_PATTERN:
Nenhum.

SECURITY:
Payload sem role; password só no body HTTPS local.

DATA_ACCESS:
POST/GET auth routes.

AUTHORIZATION:
N/A.

INPUT:
email, password.

OUTPUT:
token + user.

DEPENDENCIES:
client.ts.

LINES:
Novo.

TESTS:
login não inclui role no body (assert JSON.stringify).

RISKS:
Enviar form inteiro com campos extras.
```

```text
FILE: frontend/src/api/investments.ts

RESPONSIBILITY:
list, show, create, withdraw.

CURRENT_PROBLEM:
Novo.

PROPOSED_CHANGE:
Funções com payloads allowlist; list(page, perPage) sem user_id.

ARCHITECTURE:
API module.

SOLID:
S.

DESIGN_PATTERN:
Nenhum.

SECURITY:
Create/withdraw sem tax/user_id/status.

DATA_ACCESS:
Investment endpoints.

AUTHORIZATION:
Servidor.

INPUT:
amount, created_on / withdrawn_on / page.

OUTPUT:
Investment / page.

DEPENDENCIES:
client.ts, types.

LINES:
Novo.

TESTS:
create payload keys only amount+created_on; withdraw only withdrawn_on.

RISKS:
Recalcular balance no mapper.
```

```text
FILE: frontend/src/auth/session.ts

RESPONSIBILITY:
get/set/clear token+user em sessionStorage.

CURRENT_PROBLEM:
Novo.

PROPOSED_CHANGE:
API pura; keys namespaced (ex. coderockr.token).

ARCHITECTURE:
Session boundary.

SOLID:
S.

DESIGN_PATTERN:
Nenhum.

SECURITY:
Clear on logout/401; não espelhar token no DOM.

DATA_ACCESS:
sessionStorage.

AUTHORIZATION:
N/A.

INPUT:
token, user.

OUTPUT:
estado sessão.

DEPENDENCIES:
Nenhum.

LINES:
Novo.

TESTS:
clear remove keys; get sem key → null.

RISKS:
localStorage por engano.
```

```text
FILE: frontend/src/auth/RequireAuth.tsx

RESPONSIBILITY:
Redirect se sem token.

CURRENT_PROBLEM:
Novo.

PROPOSED_CHANGE:
Outlet se autenticado; Navigate to /login senão.

ARCHITECTURE:
UX gate.

SOLID:
S.

DESIGN_PATTERN:
Protected route.

SECURITY:
Não é security boundary — API ainda exige Bearer.

DATA_ACCESS:
session.

AUTHORIZATION:
Só presença de token.

INPUT:
children/outlet.

OUTPUT:
route.

DEPENDENCIES:
react-router, session.

LINES:
Novo.

TESTS:
sem token → login; com token → children.

RISKS:
Tratar role no RequireAuth como authz de recurso.
```

```text
FILE: frontend/src/types/api.ts

RESPONSIBILITY:
Tipos User, Investment, InvestmentPage, ApiError.

CURRENT_PROBLEM:
Novo.

PROPOSED_CHANGE:
Espelhar Resource JSON; strings para money.

ARCHITECTURE:
Contrato TS.

SOLID:
S.

DESIGN_PATTERN:
Nenhum.

SECURITY:
Sem password no tipo User.

DATA_ACCESS:
N/A.

AUTHORIZATION:
N/A.

INPUT:
N/A.

OUTPUT:
types.

DEPENDENCIES:
Nenhum.

LINES:
Novo.

TESTS:
N/A (compile-time).

RISKS:
number para money (float) — evitar.
```

```text
FILE: frontend/src/pages/LoginPage.tsx

RESPONSIBILITY:
Login UX.

CURRENT_PROBLEM:
Novo.

PROPOSED_CHANGE:
Form email/password; chama auth.login; brand visível.

ARCHITECTURE:
Page.

SOLID:
S — sem regra de investimento.

DESIGN_PATTERN:
Nenhum.

SECURITY:
Erro 401 genérico em PT; sem role field.

DATA_ACCESS:
via auth.ts.

AUTHORIZATION:
N/A.

INPUT:
credentials.

OUTPUT:
redirect.

DEPENDENCIES:
auth, session, router.

LINES:
Novo.

TESTS:
submit chama login sem role; 401 mostra mensagem genérica.

RISKS:
Mostrar “usuário não encontrado”.
```

```text
FILE: frontend/src/pages/InvestmentsListPage.tsx

RESPONSIBILITY:
Lista paginada.

CURRENT_PROBLEM:
Novo.

PROPOSED_CHANGE:
Fetch list; render rows; pagination; empty state; link new.

ARCHITECTURE:
Page.

SOLID:
S.

DESIGN_PATTERN:
Nenhum.

SECURITY:
Não filtrar por role no client; não passar user_id na query.

DATA_ACCESS:
investments.list.

AUTHORIZATION:
Servidor.

INPUT:
page.

OUTPUT:
UI.

DEPENDENCIES:
InvestmentRow, Pagination, api.

LINES:
Novo.

TESTS:
renderiza amount/status do mock; não chama API com user_id.

RISKS:
Filtrar owner.id === me quando admin.
```

```text
FILE: frontend/src/pages/CreateInvestmentPage.tsx

RESPONSIBILITY:
Form criar.

CURRENT_PROBLEM:
Novo.

PROPOSED_CHANGE:
Só amount + created_on; validate UX; POST allowlist.

ARCHITECTURE:
Page.

SOLID:
S.

DESIGN_PATTERN:
Nenhum.

SECURITY:
Sem campos ocultos de user_id/expected_balance.

DATA_ACCESS:
investments.create.

AUTHORIZATION:
Servidor (create = self).

INPUT:
form.

OUTPUT:
redirect detalhe.

DEPENDENCIES:
api.

LINES:
Novo.

TESTS:
payload keys; rejeita 0.00 na UX.

RISKS:
Enviar formData completo.
```

```text
FILE: frontend/src/pages/InvestmentDetailPage.tsx

RESPONSIBILITY:
Detalhe + withdraw se active.

CURRENT_PROBLEM:
Novo.

PROPOSED_CHANGE:
GET by id; 404 UI; mostrar tax/net do server; WithdrawForm.

ARCHITECTURE:
Page.

SOLID:
S — display; withdraw no componente filho.

DESIGN_PATTERN:
Nenhum.

SECURITY:
404 sem inventar valores; sem calc local de IR.

DATA_ACCESS:
show/withdraw.

AUTHORIZATION:
Servidor (IDOR).

INPUT:
:id.

OUTPUT:
UI.

DEPENDENCIES:
WithdrawForm, api.

LINES:
Novo.

TESTS:
404 state; tax equals mock (não computed); withdraw payload only date.

RISKS:
Preview de imposto client-side.
```

```text
FILE: frontend/src/components/AppShell.tsx

RESPONSIBILITY:
Layout autenticado: brand, user, logout.

CURRENT_PROBLEM:
Novo.

PROPOSED_CHANGE:
Header com logo Coderockr; botão sair.

ARCHITECTURE:
Chrome UI.

SOLID:
S.

DESIGN_PATTERN:
Nenhum.

SECURITY:
Logout limpa sessão; role só texto.

DATA_ACCESS:
logout API.

AUTHORIZATION:
N/A.

INPUT:
children.

OUTPUT:
layout.

DEPENDENCIES:
session, auth.

LINES:
Novo.

TESTS:
logout limpa token.

RISKS:
Nenhum crítico.
```

```text
FILE: frontend/src/components/WithdrawForm.tsx

RESPONSIBILITY:
Data de resgate + submit.

CURRENT_PROBLEM:
Novo.

PROPOSED_CHANGE:
Campo withdrawn_on; onSuccess(investment).

ARCHITECTURE:
Component.

SOLID:
S.

DESIGN_PATTERN:
Nenhum.

SECURITY:
Só envia withdrawn_on.

DATA_ACCESS:
via callback/api.

AUTHORIZATION:
Servidor.

INPUT:
investment id, created_on (para hint UX).

OUTPUT:
events.

DEPENDENCIES:
Nenhum calc.

LINES:
Novo.

TESTS:
não inclui tax no body.

RISKS:
Campo hidden tax.
```

```text
FILE: frontend/src/components/InvestmentRow.tsx
FILE: frontend/src/components/Pagination.tsx

RESPONSIBILITY:
Linha da lista; controles de página.

CURRENT_PROBLEM:
Novo.

PROPOSED_CHANGE:
Apresentação pura / callbacks onPageChange.

ARCHITECTURE:
Presentational.

SOLID:
S.

DESIGN_PATTERN:
Nenhum.

SECURITY:
Sem lógica de authz.

DATA_ACCESS:
Nenhum.

AUTHORIZATION:
N/A.

INPUT:
props.

OUTPUT:
UI.

DEPENDENCIES:
router Link.

LINES:
Novos.

TESTS:
render props.

RISKS:
Nenhum.
```

```text
FILE: frontend/src/styles/tokens.css
FILE: frontend/src/App.tsx
FILE: frontend/src/main.tsx
FILE: frontend/index.html

RESPONSIBILITY:
Tokens visuais, rotas, bootstrap, HTML shell.

CURRENT_PROBLEM:
Novos.

PROPOSED_CHANGE:
Router routes; CSS variables (evitar temas AI genéricos); montar React.

ARCHITECTURE:
App shell.

SOLID:
S.

DESIGN_PATTERN:
Nenhum.

SECURITY:
CSP default do browser; sem inline scripts com token.

DATA_ACCESS:
N/A.

AUTHORIZATION:
RequireAuth nas rotas protegidas.

INPUT:
N/A.

OUTPUT:
SPA.

DEPENDENCIES:
react-router.

LINES:
Novos.

TESTS:
smoke render App.

RISKS:
Rota /investments/:id sem RequireAuth.
```

```text
FILE: frontend/src/**/*.test.tsx

RESPONSIBILITY:
Testes Vitest antes/durante TDD.

CURRENT_PROBLEM:
Novos.

PROPOSED_CHANGE:
Ver Required Tests.

ARCHITECTURE:
Testes.

SOLID:
N/A.

DESIGN_PATTERN:
N/A.

SECURITY:
Cobrir IDOR UX 404, mass-assign payload, no client tax math.

DATA_ACCESS:
mocks.

AUTHORIZATION:
mocks 404.

INPUT:
N/A.

OUTPUT:
pass/fail.

DEPENDENCIES:
vitest, testing-library.

LINES:
Novos.

TESTS:
estes.

RISKS:
Mocks que “passam” calculando tax no expect errado.
```

```text
FILE: screenshots/*.png

RESPONSIBILITY:
Evidência visual (FR-013).

CURRENT_PROBLEM:
Pasta inexistente.

PROPOSED_CHANGE:
≥2 PNGs: lista; detalhe ou pós-resgate.

ARCHITECTURE:
Entrega.

SOLID:
N/A.

DESIGN_PATTERN:
N/A.

SECURITY:
Sem tokens/senhas visíveis nas capturas (usar blur se necessário).

DATA_ACCESS:
N/A.

AUTHORIZATION:
N/A.

INPUT:
N/A.

OUTPUT:
imagens.

DEPENDENCIES:
N/A.

LINES:
Novos binários.

TESTS:
Existência verificada no review.

RISKS:
Screenshots com password no form.
```

```text
FILE: README.md

RESPONSIBILITY:
Build instructions, libs, link docs.

CURRENT_PROBLEM:
Falta SPA e screenshots.

PROPOSED_CHANGE:
Seções frontend install/dev, libs (React/Vite/RR), link /docs, pasta screenshots.

ARCHITECTURE:
Docs entrega.

SOLID:
N/A.

DESIGN_PATTERN:
N/A.

SECURITY:
Não colar senhas de produção; seed só como local demo.

DATA_ACCESS:
N/A.

AUTHORIZATION:
N/A.

INPUT:
N/A.

OUTPUT:
markdown.

DEPENDENCIES:
N/A.

LINES:
Atualizar seções Deliverables / stack.

TESTS:
N/A.

RISKS:
Instruções que apontam main em vez de development.
```

```text
FILE: .gitignore

RESPONSIBILITY:
Ignorar artefatos.

CURRENT_PROBLEM:
Pode faltar frontend/node_modules e frontend/.env.

PROPOSED_CHANGE:
Garantir `frontend/node_modules/`, `frontend/dist/`, `frontend/.env` (manter `.env.example`).

ARCHITECTURE:
Repo hygiene.

SOLID:
N/A.

DESIGN_PATTERN:
N/A.

SECURITY:
Não commitir .env com secrets.

DATA_ACCESS:
N/A.

AUTHORIZATION:
N/A.

INPUT:
N/A.

OUTPUT:
N/A.

DEPENDENCIES:
N/A.

LINES:
Append se ausente.

TESTS:
N/A.

RISKS:
Commit acidental de dist.
```

```text
FILE: routes/api.php / app/Domain/** / app/Policies/**

RESPONSIBILITY:
API e domínio existentes.

CURRENT_PROBLEM:
Nenhum para esta feature.

PROPOSED_CHANGE:
**Não alterar.**

ARCHITECTURE:
Backend intacto.

SOLID:
Mantido.

DESIGN_PATTERN:
N/A.

SECURITY:
Authz permanece no servidor.

DATA_ACCESS:
N/A.

AUTHORIZATION:
N/A.

INPUT:
N/A.

OUTPUT:
N/A.

DEPENDENCIES:
N/A.

LINES:
Nenhuma.

TESTS:
php artisan test regressão.

RISKS:
“Ajuste rápido” de API neste PR — proibido sem Scribe/spec.
```

## Line-by-line Analysis

```text
FILE: config/cors.php

LINE 24:
Problema:
Nenhum — default já inclui localhost:5173 via env.
Alteração:
Nenhuma obrigatória; confirmar .env.example CORS_ALLOWED_ORIGINS.
Motivo:
SPA na 5173.
SECURITY:
supports_credentials false (L35) — ok com Bearer.
TEST:
Browser login cross-origin manual / quickstart.
```

```text
FILE: .env.example (Laravel root)

LINE 8:
Problema:
Nenhum se CORS já listado.
Alteração:
Opcional comentário apontando frontend/.env.example VITE_API_URL.
Motivo:
Avaliador sobe os dois processos.
SECURITY:
Sem secrets novos.
TEST:
N/A.
```

```text
FILE: .gitignore

LINE (vendor/node_modules já):
Problema:
frontend/node_modules pode não estar coberto se só /node_modules na raiz — em geral `/node_modules` cobre só raiz.
Alteração:
Adicionar `frontend/node_modules/`, `frontend/dist/`, `frontend/.env`.
Motivo:
SPA nova.
SECURITY:
.env local.
TEST:
git status limpo após npm install.
```

```text
FILE: README.md

LINES (Deliverables / Requirements):
Problema:
Sem instruções da SPA.
Alteração:
Documentar dual process (artisan serve + npm run dev), libs, /docs, screenshots/.
Motivo:
Entregável do desafio.
SECURITY:
Credenciais seed só como demo local.
TEST:
Seguir quickstart.
```

```text
FILE: frontend/src/api/investments.ts

LINES 1-XX:
Responsabilidade:
Chamadas investment allowlist.
Dependencies:
client.ts.
Security:
Nunca tax/user_id no body; nunca user_id na query de list.
Tests:
payload snapshot tests.
```

```text
FILE: frontend/src/pages/InvestmentDetailPage.tsx

LINES 1-XX:
Responsabilidade:
Show + withdraw UX.
Dependencies:
investments API, WithdrawForm.
Security:
404 handling; display-only money fields.
Tests:
mock tax asserted equal to API string; no Math on amount.
```

```text
FILE: frontend/src/auth/RequireAuth.tsx

LINES 1-XX:
Responsabilidade:
UX redirect.
Dependencies:
session, router.
Security:
Not a substitute for API authz.
Tests:
unauth redirect.
```

## Required Tests

**Antes do Code Agent (FAIL / red onde aplicável):**

- `session.test.ts` — set/get/clear token
- `client.test.ts` — Bearer header; 401 clears session
- `auth.api.test.ts` — login body sem `role`/`user_id`
- `investments.api.test.ts` — create/withdraw allowlist; list sem `user_id`
- `LoginPage.test.tsx` — 401 mensagem genérica
- `RequireAuth.test.tsx` — redirect
- `InvestmentsListPage.test.tsx` — renderiza campos do mock; Admin mock com dois owners sem filtro client
- `CreateInvestmentPage.test.tsx` — não envia expected_balance
- `InvestmentDetailPage.test.tsx` — 404; mostra tax do mock; withdraw body só date
- `AppShell` logout limpa sessão

**Regressão:** `php artisan test` (API intacta).

**Manual / DoD:** quickstart + ≥2 screenshots.

**Não:** reimplementar WithdrawalTaxCalculatorTest no frontend.

## Remaining Risks

- XSS se alguém usar HTML raw em mensagens — mitigar com texto React.
- Token em sessionStorage ainda é roubável via XSS grave — CSP futura; sem `dangerouslySetInnerHTML`.
- Avaliador pode esperar pixel-perfect Figma — spec permite estrutura livre.
- Sem E2E automatizado: gap coberto por quickstart + screenshots.
- Dois processos (API+UI) — README deve ser claro.
