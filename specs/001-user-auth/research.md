# Research: Autenticação Admin / Owner

## 1. Mecanismo de sessão da API

**Decision**: Laravel Sanctum personal access tokens (header `Authorization: Bearer`).

**Rationale**: Decisão de produto já registrada (Bearer, SPA futura com `VITE_API_URL`). Scribe já declara Bearer. Tokens são revogáveis no logout (`currentAccessToken()->delete()`). Não exige cookie/CSRF nesta etapa (SPA ainda não existe).

**Alternatives considered**:
- Session cookie + `EnsureFrontendRequestsAreStateful`: útil depois se a SPA for same-site; agora aumentaria CSRF sem UI.
- Passport / JWT de terceiro: peso e superfície maiores que o desafio pede.
- Fortify: voltado a views/reset; fora do escopo (FR-013).

## 2. Onde vive o papel

**Decision**: Coluna `users.role` (`string`, default `owner`) + backed enum `App\Enums\UserRole` (`admin`, `owner`). Fora de `Fillable`. Estados de factory `admin()` / `owner()`. Seed usa factory states, nunca o body HTTP.

**Rationale**: Dois valores fixos; enum impede typos e permite fail-closed (`isAdmin()` só se `UserRole::Admin`). Mass assignment do skeleton já é allowlist (`name`, `email`, `password`) — **não** adicionar `role`.

**Alternatives considered**:
- Spatie Permission: dependência extra para dois papéis.
- Token abilities Sanctum como “admin”: o cliente poderia mal-interpretar abilities; o papel é da pessoa, não do token.
- Boolean `is_admin`: pior para fail-closed e extensão futura.

## 3. Camadas (Controller vs Service)

**Decision**: Três controllers invocáveis + `App\Services\Auth\LoginUser` para verificar credenciais e emitir token. Logout e “me” ficam nos controllers (uma linha de Sanctum / Resource). Sem Repository.

**Rationale**: Login tem regra (mensagem genérica, não vazar existência de e-mail, ignorar `role` no input). Logout/me não têm regra de domínio. Repository não resolve problema real (um Eloquent model).

**Alternatives considered**: AuthController único com três métodos (aceitável, mas pior SRP). Action classes por endpoint (equivalente aos invocáveis).

## 4. Endpoints e IDOR

**Decision**: Não expor `GET /api/users/{id}`. Perfil é `GET /api/user` (usuário da sessão). Login `POST /api/login`. Logout `POST /api/logout`.

**Rationale**: Spec exige que Owner veja só a própria conta. Sem `{id}` na rota, o vetor IDOR clássico desta etapa some. Testes ainda enviam `role` / `user_id` no body/query e afirmam que o perfil não muda.

**Alternatives considered**: `GET /api/users/{id}` com policy — adia complexidade para um recurso que a spec não pede agora.

## 5. Rate limit (FR-011)

**Decision**: Named limiter `login`: 5 tentativas / minuto por combinação IP + e-mail normalizado. Middleware `throttle:login` só em `POST /api/login`. Resposta 429.

**Rationale**: Constitution e spec pedem proteção a força bruta. Limiter por e-mail evita pulverizar tentativas no mesmo mailbox; IP cobre e-mails aleatórios.

**Alternatives considered**: `throttle:5,1` genérico (não amarra e-mail). Laravel Fortify lockout (fora de escopo). CAPTCHA (desnecessário no desafio).

## 6. Mensagem de falha (FR-009 / SC-006)

**Decision**: Sempre HTTP 401 JSON `{ "message": "Invalid credentials." }` para e-mail inexistente **e** senha errada. Validação vazia/malformada: 422 (input inválido, não “usuário não existe”).

**Rationale**: 422 em formato é UX de formulário, não enumeração de contas. 401 único para credenciais evita timing óbvio de “user vs password” no corpo da resposta. Não diferenciar mensagens.

**Alternatives considered**: 422 em ambos (comum no Laravel `ValidationException`) — pior para clientes que tratam 422 como campo. 404 para e-mail — vaza existência.

## 7. Seed e secrets

**Decision**: `DatabaseSeeder` cria Admin e Owner. E-mail/senha de `.env` (`SEED_ADMIN_EMAIL`, `SEED_ADMIN_PASSWORD`, `SEED_OWNER_EMAIL`, `SEED_OWNER_PASSWORD`) com defaults **apenas** em `.env.example` para local (`admin@example.com` / `password`, `owner@example.com` / `password`). README da feature (quickstart) documenta. Não commitar `.env`.

**Rationale**: Spec pede contas iniciais sem cadastro público e admite credenciais de desenvolvimento.

**Alternatives considered**: Senhas hardcoded no seeder (viola spirit do gate de secrets). Sem Owner seed (quebra testes de isolamento nas etapas seguintes).

## 8. Scribe

**Decision**: Anotar controllers (`@group Authentication`, `@unauthenticated` no login, `@authenticated` em me/logout). Regenerar `public/docs` neste PR. `config/scribe.php` já tem Bearer; `auth.default` permanece `false` (health segue público).

**Rationale**: Constituição: todo PR com endpoint novo regenera docs.

## 9. Strategy / papéis

**Decision**: **Não** usar Strategy para Admin vs Owner neste PR.

**Rationale**: Não há algoritmos variantes (ganho/IR são a próxima feature). Papel é dado para policies futuras, não uma família de estratégias de login.

## Resoluções NEEDS CLARIFICATION

Nenhum item do Technical Context ficou como NEEDS CLARIFICATION. Decisões de produto (Sanctum Bearer, Admin/Owner, seed, sem mass-assign de role) já estavam fechadas na spec e no plano de entrega.
