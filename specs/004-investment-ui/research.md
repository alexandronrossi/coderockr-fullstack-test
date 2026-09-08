# Research: Interface web de investimentos

## 1. Onde vive a SPA

**Decision**: App React + Vite + TypeScript em `frontend/`, porta 5173, `VITE_API_URL` apontando para `http://localhost:8000/api`.

**Rationale**: Decisão de produto já registrada nas etapas anteriores. CORS Laravel já permite `http://localhost:5173`. Isolar a SPA evita misturar o Vite do Laravel (`resources/js`) com o app de produto.

**Alternatives considered**: Inertia/Blade (acoplaria UI ao Laravel e atrasaria stack React pedida). Monólito no `resources/` (conflito com assets do skeleton).

## 2. Sessão no browser

**Decision**: Sanctum **Bearer** (igual à API). Persistir `token` + perfil `{ id, name, email, role }` em `sessionStorage`. Logout: `POST /logout` + limpar storage + redirecionar para login. Rotas protegidas via `RequireAuth` (sem token → `/login`).

**Rationale**: API já é Bearer-only (`config/sanctum.php` `guard => []`). Cookie/CSRF Sanctum stateful não está habilitado. `sessionStorage` some ao fechar a aba (ajuda FR-010 / back-after-logout).

**Alternatives considered**: Cookie session (exigiria `EnsureFrontendRequestsAreStateful` + CSRF — fora do escopo). `localStorage` (persiste após fechar aba; pior para shared machine no desafio).

## 3. Papel Admin / Owner na UI

**Decision**: `user.role` só controla **rótulos** e eventual copy (“vendo todos”). **Nunca** filtrar lista no cliente; **nunca** bloquear chamada de API só com `if (role === 'owner')`. Detalhe/resgate de ID alheio: chamar API; tratar 404.

**Rationale**: Constituição V — frontend permission ≠ security. API já faz `visibleTo` + Policy.

**Alternatives considered**: Esconder rotas Admin-only (não há rotas Admin-only). Duplicar filtro por `owner.id === me` na lista (quebraria SC-004 do Admin e falsaria segurança).

## 4. Cálculos monetários

**Decision**: Zero lógica de 0,52% / faixas de IR no frontend. Exibir strings `amount`, `expected_balance`, `gain`, `tax`, `net` do JSON. Validação de formulário: formato `^\d+\.\d{2}$`, amount &gt; 0, datas ≤ hoje — só UX; 422 do servidor manda.

**Rationale**: FR-007; evita divergência do domínio.

**Alternatives considered**: Recalcular “preview” de IR no cliente (proibido). Lib `decimal.js` (desnecessária se só exibimos).

## 5. Roteamento e telas

**Decision**: React Router:

| Path | Página |
|------|--------|
| `/login` | Login |
| `/investments` | Lista paginada |
| `/investments/new` | Criar |
| `/investments/:id` | Detalhe + formulário de resgate se `active` |

Após login → `/investments`. Criar sucesso → detalhe ou lista. Resgate sucesso → atualizar detalhe.

**Rationale**: Espelha FR lista/detalhe/criar/resgatar; resgate no detalhe reduz telas sem perder o fluxo Figma.

**Alternatives considered**: Modal de resgate separado (ok, mas detalhe + form é mais simples de testar).

## 6. Cliente HTTP

**Decision**: Wrapper `fetch` em `frontend/src/api/client.ts`: base URL de `import.meta.env.VITE_API_URL`, header `Authorization: Bearer`, `Accept: application/json`, parse de erros 401/404/422. Sem Axios (menos dependência).

**Rationale**: Superfície pequena; Constitution pede gerir deps com cuidado.

**Alternatives considered**: Axios (ok, peso extra). TanStack Query (útil, mas YAGNI no desafio — fetch por página basta).

## 7. Visual / Figma

**Decision**: Estrutura das telas alinhada ao Figma (lista, detalhe, forms). CSS variables próprias + tipografia distinta (não Inter/Roboto default). Logo de `public/images` (ou URL da API/`/images`). Responsivo. Sem pixel-perfect.

**Rationale**: Spec FR-012 + nota do README; regras de design do projeto evitam temas AI genéricos.

**Alternatives considered**: Material/MUI (peso e look genérico). Copiar Figma 1:1 (fora do pedido).

## 8. Testes

**Decision**: Vitest + Testing Library: login redirect, lista renderiza campos do mock API, create não envia `user_id`/`expected_balance`, detail mostra tax do mock (não calcula), 404 no detail alheio, logout limpa sessão. Sem E2E Playwright obrigatório neste PR (quickstart manual cobre ponta a ponta).

**Rationale**: SDD III no frontend; API Feature tests já cobrem IDOR real.

**Alternatives considered**: Só testes manuais (falha gate). Playwright full (bom, mas fora do MVP se Vitest + quickstart bastam).

## 9. Screenshots e README

**Decision**: Pasta `screenshots/` na raiz com ≥2 PNGs (lista; detalhe ou pós-resgate). Atualizar README root: como subir API + `frontend`, libs da SPA, link `/docs`.

**Rationale**: FR-013 e entregáveis do desafio.

## 10. Scribe / API

**Decision**: **Não** alterar endpoints; **não** rodar `scribe:generate` neste PR.

**Rationale**: Constituição — Scribe só quando API muda.

## Resoluções NEEDS CLARIFICATION

Nenhum item do Technical Context ficou aberto. Stack React/Vite/TS + Bearer + `frontend/` já era decisão de produto.
