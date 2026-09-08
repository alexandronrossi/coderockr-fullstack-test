# Data Model: Autenticação Admin / Owner

## User (Pessoa)

Tabela existente `users`, acréscimo de `role`.

| Campo | Tipo | Regras | Origem |
|-------|------|--------|--------|
| id | bigint PK | auto | servidor |
| name | string | required | seed / factory; não via login |
| email | string unique | required, email | login identifica; seed define |
| email_verified_at | timestamp null | não usado nesta feature | factory default now() |
| password | string hashed | required; never in JSON | login compara; cast `hashed` |
| role | string | `admin` \| `owner`; default `owner`; **não fillable** | seed / factory states |
| remember_token | string null | legado session; não usado na API token | factory |
| timestamps | | | servidor |

**Casts**: `password` => `hashed`; `role` => `App\Enums\UserRole`.

**Fillable (manter)**: `name`, `email`, `password`. **Nunca** `role`, `id`.

**Hidden**: `password`, `remember_token`.

**Métodos**: `isAdmin(): bool` (true só se `UserRole::Admin`); `isOwner(): bool` (true só se `UserRole::Owner`). Valor de enum inválido no banco → ambos false (fail-closed; não promove a Admin).

**Relação Sanctum**: `HasApiTokens` → `tokens()` / `createToken('api')`.

### Factory states

- default: `role = owner`
- `admin()`: `role = admin`
- `owner()`: `role = owner` (explícito)

## UserRole (enum)

```text
admin  → Administrador (nas etapas futuras: vê todos os investimentos)
owner  → Owner (nas etapas futuras: só os próprios)
```

Sem outros cases. Código de autorização **não** trata string solta como admin.

## Session (token Sanctum)

Tabela `personal_access_tokens` (migration do pacote).

| Conceito | Comportamento |
|----------|----------------|
| Emissão | Só após senha válida em `LoginUser` |
| Uso | Header `Authorization: Bearer {token}` |
| Fim | `POST /api/logout` apaga o token atual |
| Expiração | Default Sanctum (sem refresh nesta feature) |

Não é entidade de domínio própria; é infraestrutura de sessão da spec.

## Seed records

| Papel | E-mail default (local) | Senha default (local) |
|-------|------------------------|------------------------|
| Admin | `SEED_ADMIN_EMAIL` → `admin@example.com` | `SEED_ADMIN_PASSWORD` → `password` |
| Owner | `SEED_OWNER_EMAIL` → `owner@example.com` | `SEED_OWNER_PASSWORD` → `password` |

Idempotência: `updateOrCreate` por e-mail para re-seed não duplicar.

## Validation rules

**Login**: `email` required|email; `password` required|string. Qualquer outro campo ignorado.

**Perfil / logout**: sem body. Identidade = `auth()->user()`.

## State transitions

```text
sem sessão --(credenciais válidas)--> sessão ativa (token)
sessão ativa --(logout ou token inválido)--> sem sessão
role: nunca transiciona via API nesta feature
```

## Out of scope

Investment, Withdrawal, pagination, SPA session storage.
