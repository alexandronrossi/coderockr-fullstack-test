# UI contract: Investment SPA

Contrato da interface com o usuário e com a API existente. Endpoints HTTP: ver `specs/003-investment-api/contracts/investments.yaml` e `specs/001-user-auth/contracts/auth.yaml`.

## Screens

### Login (`/login`)

| Elemento | Comportamento |
|----------|----------------|
| Brand / produto | Visível (hero-level na composição de login) |
| Email, senha | Required |
| Submit | `POST /api/login` → guardar token+user → `/investments` |
| Erro 401 | Mensagem genérica (não “email não existe”) |
| Autenticado visitando `/login` | Redirecionar para `/investments` |

### Lista (`/investments`)

| Elemento | Comportamento |
|----------|----------------|
| Auth | Sem token → `/login` |
| Tabela/lista | Colunas: dono, data, valor, saldo esperado, status |
| Paginação | `page`, `per_page` (default 15); próxima página chama API |
| Vazio | CTA para criar |
| Ações | Abrir detalhe; link criar |
| Query `user_id` | **Não** enviar para ampliar escopo |

### Criar (`/investments/new`)

| Elemento | Comportamento |
|----------|----------------|
| Campos | Só `amount`, `created_on` |
| Submit | `POST /api/investments` body allowlist |
| Sucesso 201 | Ir para detalhe do `id` criado ou lista |
| 422 | Mostrar erros de campo |

### Detalhe (`/investments/:id`)

| Elemento | Comportamento |
|----------|----------------|
| Load | `GET /api/investments/:id` |
| 404 | Mensagem “não encontrado” (Owner alheio incluso) — **sem** inventar dados |
| Campos | amount, expected_balance, gain, status; se withdrawn: withdrawn_on, tax, net |
| Resgate | Se `active`: form `withdrawn_on` → `POST .../withdraw` |
| Pós-resgate | Re-render com response; esconder form ou desabilitar |

### Shell autenticado

| Elemento | Comportamento |
|----------|----------------|
| Nome / email / role | Informativos |
| Sair | `POST /api/logout` + clear session → `/login` |

## Client → API rules

1. Todo request autenticado: `Authorization: Bearer {token}`.
2. Nunca enviar `role`, `user_id`, `expected_balance`, `tax`, `net`, `status` em create/withdraw.
3. Nunca calcular gain/tax no cliente.
4. Em 401: limpar sessão e ir para login.

## Accessibility / responsive

- Formulários com labels.
- Lista usável em viewport estreito (stack ou scroll horizontal mínimo).
- Foco e erros associados aos campos.

## Non-goals

- Cadastro, reset de senha, dark mode obrigatório, PWA offline.
