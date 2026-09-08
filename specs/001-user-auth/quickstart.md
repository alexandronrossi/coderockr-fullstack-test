# Quickstart: Autenticação Admin / Owner

Validação ponta a ponta **depois** que testes e código existirem. Não substitui PHPUnit.

## Prerequisites

- PHP 8.3, Composer, SQLite
- Branch `feat/auth` com Sanctum instalado e migrations aplicadas
- `.env` copiado de `.env.example` (inclui `SEED_*` e `CORS_ALLOWED_ORIGINS`)

## Setup

```bash
composer install
php artisan migrate --force
php artisan db:seed
php artisan serve
```

Contas locais (defaults do `.env.example`):

| Papel | E-mail | Senha |
|-------|--------|--------|
| Admin | admin@example.com | password |
| Owner | owner@example.com | password |

## Contract checks

Contrato: [contracts/auth.yaml](./contracts/auth.yaml). Modelo: [data-model.md](./data-model.md).

### 1. Health continua público

```bash
curl -s http://localhost:8000/api/health
```

Esperado: `{"status":"ok"}`.

### 2. Login Owner

```bash
curl -s -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d "{\"email\":\"owner@example.com\",\"password\":\"password\",\"role\":\"admin\"}"
```

Esperado: `200` com `token` e `user.role` = `owner` (campo `role` no body **ignorado**). Sem `password` no JSON.

### 3. Perfil (Bearer)

```bash
curl -s http://localhost:8000/api/user \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

Esperado: `200`, dados do Owner. Query `?user_id=1` (se enviada) não troca o perfil.

### 4. Sem token

```bash
curl -s http://localhost:8000/api/user -H "Accept: application/json"
```

Esperado: `401`.

### 5. Credenciais inválidas

```bash
curl -s -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d "{\"email\":\"nobody@example.com\",\"password\":\"wrong\"}"
```

Esperado: `401` `{"message":"Invalid credentials."}` — mesma mensagem que senha errada de e-mail existente.

### 6. Logout

```bash
curl -s -o NUL -w "%{http_code}" -X POST http://localhost:8000/api/logout \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

Esperado: `204`. Repetir `GET /api/user` com o mesmo token → `401`.

### 7. Docs

```bash
composer docs
```

`public/docs` deve listar login (unauthenticated), user e logout (Bearer).

## Automated suite

```bash
php artisan test --testsuite=Feature --filter=Auth
php artisan test --testsuite=Unit --filter=UserRole
```

Todos verdes antes de review.
