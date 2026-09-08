# Quickstart: Interface web de investimentos

Validação ponta a ponta **depois** de testes e código. API deve estar migrada/seedada.

Contrato UI: [contracts/ui.md](./contracts/ui.md). API: [../003-investment-api/quickstart.md](../003-investment-api/quickstart.md).

## Prerequisites

- PHP 8.3, Composer, SQLite
- Node 20+, npm
- Branch `feat/investment-ui` com `frontend/` criado

## Setup API

```bash
composer install
php artisan migrate --force
php artisan db:seed
php artisan serve
```

Contas seed (defaults):

| Papel | E-mail | Senha |
|-------|--------|--------|
| Admin | admin@example.com | password |
| Owner | owner@example.com | password |

## Setup UI

```bash
cd frontend
cp .env.example .env
# VITE_API_URL=http://localhost:8000/api
npm install
npm run dev
```

Abrir `http://localhost:5173`.

## Manual checks

1. Login Owner → lista só próprios.
2. Login Admin → lista com mais de um dono (se houver dados).
3. Criar 1000.00 + data passada → aparece na lista/detalhe; status active.
4. Detalhe mostra `expected_balance` do servidor (sem digitar saldo).
5. Resgatar com data válida → tax/net na tela; status withdrawn; segundo resgate falha.
6. Owner: URL `/investments/{idDoOutro}` → não mostra valores alheios.
7. Logout → lista inacessível; voltar no browser não restaura sessão.
8. Login inválido → mensagem genérica.

## Automated

```bash
cd frontend && npm test
# na raiz
php artisan test
```

## Screenshots

Após UI estável, capturar ≥2 imagens em `screenshots/` (lista + detalhe ou resgate).

## Docs API

`http://localhost:8000/docs` (Scribe; não regenerar neste PR).
