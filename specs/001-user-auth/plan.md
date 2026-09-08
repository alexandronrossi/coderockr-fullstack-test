# Implementation Plan: Autenticação e papéis Admin / Owner

**Branch**: `feat/auth` (`001-user-auth`) | **Date**: 2026-09-08 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/001-user-auth/spec.md`

**Note**: This template is filled in by the `/speckit-plan` command; its definition describes the execution workflow.

## Summary

Pessoas entram com e-mail e senha, recebem um token Bearer, consultam só o próprio perfil e encerram a sessão. Dois papéis persistidos no servidor — Administrador e Owner — preparam a autorização das etapas de investimento. Não há cadastro público, SPA nem CRUD de investimento neste PR.

Abordagem: Laravel Sanctum (token pessoal), enum `UserRole`, coluna `role` fora de mass assignment, Form Request só com e-mail/senha, application service de login, Resource sem senha, seed Admin+Owner via env local, throttle no login, Scribe regenerado.

## Technical Context

**Language/Version**: PHP 8.3, Laravel 13

**Primary Dependencies**: `laravel/framework` ^13.17, **`laravel/sanctum`** (novo), `knuckleswtf/scribe` ^5.11 (docs)

**Storage**: SQLite local (`DB_CONNECTION=sqlite`); tabela `users` + `personal_access_tokens` (Sanctum)

**Testing**: PHPUnit 12 (`php artisan test`), `RefreshDatabase`

**Target Platform**: API JSON (SPA React fica em PR posterior; CORS já permite `http://localhost:5173`)

**Project Type**: Web service (JSON API no root Laravel; sem pasta `frontend/` nesta feature)

**Performance Goals**: Login e perfil em tempo de request local típico; SC-001 (< 1 min ponta a ponta)

**Constraints**: Sem secrets no código; senha hashed; papel não vem do cliente; health público; login rate-limited; erros de API sem stack em produção (`APP_DEBUG`)

**Scale/Scope**: Desafio fullstack (poucos usuários); 3 endpoints novos; 2 contas seed

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Gate | Status | Notes |
|------|--------|--------|
| I. Specification-first | PASS | `specs/001-user-auth/spec.md` existe |
| II. Architecture before tests | PASS | Este plan + `architecture-security-plan.md` antes do Test Agent |
| III. Test-first | PASS | Testes listados no architecture-security-plan; Code Agent não começa agora |
| IV. No skipped gates | PASS | Estado após este comando: `ARCHITECTURE_SECURITY_PLAN_READY` |
| V. Server-side authorization | PASS | Papel só no servidor; `GET /api/user` usa o autenticado, nunca `{id}` da URL; cliente não define `role` |
| SOLID | PASS | Controllers finos; login em application service; sem Strategy (dois papéis fixos, não variantes de algoritmo) |
| Patterns só se resolvem problema | PASS | Sem Repository extra (um model); Sanctum como mecanismo de sessão, não como “pattern” de domínio |
| Scribe | PASS | Novos endpoints `api/*` → `php artisan scribe:generate` e commit de `public/docs` |
| Graphify 30.2 | PASS | Rebuild no Git/PR; consulta feita na elaboração deste plan |
| Secrets | PASS | Senhas seed via `.env` / `.env.example` local; `APP_KEY` existente; token Sanctum não commitado |

**Post-design re-check**: Sem violação. Sem Strategy/Repository artificiais. Complexity Tracking vazio.

## Project Structure

### Documentation (this feature)

```text
specs/001-user-auth/
├── plan.md
├── architecture-security-plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
└── tasks.md                      # /speckit-tasks — ainda não
```

### Source Code (repository root)

```text
app/
├── Enums/UserRole.php                    # novo
├── Http/
│   ├── Controllers/Api/
│   │   ├── HealthController.php          # existente; permanece público
│   │   ├── LoginController.php           # novo
│   │   ├── LogoutController.php          # novo
│   │   └── CurrentUserController.php     # novo
│   ├── Requests/Api/LoginRequest.php     # novo
│   └── Resources/UserResource.php        # novo
├── Models/User.php                       # + HasApiTokens, role, isAdmin/isOwner
└── Services/Auth/LoginUser.php           # novo — autentica e emite token

database/
├── migrations/xxxx_add_role_to_users_table.php
├── factories/UserFactory.php             # states admin/owner
└── seeders/DatabaseSeeder.php            # Admin + Owner

routes/api.php                            # login, logout, user
bootstrap/app.php                         # alias de middleware se necessário
app/Providers/AppServiceProvider.php      # RateLimiter::for('login')
tests/Feature/Api/Auth/                  # novos
tests/Unit/UserRoleTest.php               # novo
```

**Structure Decision**: Continuar o Laravel no root (já usado por health + Scribe). Sem `backend/` nem SPA neste PR. Testes em `tests/Feature/Api` alinhados a `HealthTest`.

## Complexity Tracking

> Sem violações a justificar.
