# Quickstart: API de investimentos

Validação ponta a ponta **depois** de testes e código. Não substitui PHPUnit.

Contrato: [contracts/investments.yaml](./contracts/investments.yaml). Modelo: [data-model.md](./data-model.md). Cálculo: domínio `002-gains-tax`.

## Prerequisites

- PHP 8.3, Composer, SQLite
- Branch `feat/investment-api` com migrations aplicadas
- Contas seed Admin / Owner (ver `specs/001-user-auth/quickstart.md`)

## Setup

```bash
composer install
php artisan migrate --force
php artisan db:seed
php artisan serve
```

## Contract checks

Obter token Owner:

```bash
curl -s -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d "{\"email\":\"owner@example.com\",\"password\":\"password\"}"
```

### 1. Sem token → 401

```bash
curl -s -o NUL -w "%{http_code}" -X POST http://localhost:8000/api/investments \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d "{\"amount\":\"1000.00\",\"created_on\":\"2025-01-15\"}"
```

Esperado: `401`.

### 2. Criar (ignorar user_id / expected_balance)

```bash
curl -s -X POST http://localhost:8000/api/investments \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d "{\"amount\":\"1000.00\",\"created_on\":\"2025-01-15\",\"user_id\":999,\"expected_balance\":\"9999.00\"}"
```

Esperado: `201`, `amount` `1000.00`, `owner.email` do Owner, `status` `active`. Não usar o `user_id` enviado.

### 3. Listar paginado

```bash
curl -s "http://localhost:8000/api/investments?per_page=15&page=1&user_id=1" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

Esperado: só investimentos do Owner; `user_id` na query não amplia o conjunto; `meta.total` coerente.

### 4. Detalhe e IDOR

- `GET /api/investments/{id}` do próprio → `200` com `expected_balance` calculado.
- Mesmo `{id}` com token de **outro** Owner → `404` (sem body com amount alheio).
- Token **Admin** no `{id}` do Owner → `200`.

### 5. Resgate

```bash
curl -s -X POST http://localhost:8000/api/investments/{id}/withdraw \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d "{\"withdrawn_on\":\"2025-02-15\",\"tax\":\"0.00\"}"
```

Esperado: `200`, `status` `withdrawn`, imposto calculado pelo servidor (campo `tax` do body ignorado). Segundo POST no mesmo id → `422`.

### 6. Docs

```bash
composer docs
```

`public/docs` lista o grupo Investments (Bearer).

## Automated suite

```bash
php artisan test --filter=Investment
php artisan test
```

Feature: create/list/show/withdraw, 401, 404 IDOR, mass assignment, paginação, freeze. Unitários de domínio continuam verdes.
