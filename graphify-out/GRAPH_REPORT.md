# Graph Report - coderockr-fullstack-test  (2026-09-08)

## Corpus Check
- 279 files · ~280,837 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 2005 nodes · 2484 edges · 183 communities (151 shown, 16 thin omitted)
- Extraction: 99% EXTRACTED · 1% INFERRED · 0% AMBIGUOUS · INFERRED: 27 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `5ea50dd5`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- composer.json
- sdd-multi-agent.md
- scripts
- Tasks: [FEATURE NAME]
- TestCase
- speckit-analyze/SKILL.md
- package.json
- Fullstack Test Project <img src="https://raw.githubusercontent.com/Coderockr/fullstack-test/refs/heads/main/coderockr.banner.svg" align="right" height="50px" />
- Execution Steps
- common.ps1
- Create tests (before code)
- Feature Specification: [FEATURE NAME]
- speckit-plan/SKILL.md
- speckit-tasks/SKILL.md
- Illuminate\Database\Migrations\Migration
- Coderockr Fullstack Constitution
- speckit-specify/SKILL.md
- Architecture-Security Plan: [FEATURE]
- Core Principles
- 22. Test Agent — Security Tests
- Illuminate\Http\Request
- Code Review Agent
- Git / Pull Request Agent
- Architecture-Security Plan: Autenticação e papéis Admin / Owner
- Implementation Plan: [FEATURE]
- Architecture / Security Agent
- SDD Orchestrator
- speckit-checklist/SKILL.md
- speckit-implement/SKILL.md
- speckit-clarify/SKILL.md
- Architecture-Security Plan: Ganho composto no dia civil e imposto no resgate
- Tasks: Autenticação e papéis Admin / Owner
- speckit-constitution/SKILL.md
- 28. Severity
- 5. SOLID obrigatório
- create-new-feature.ps1
- logging.php
- speckit-taskstoissues/SKILL.md
- [CHECKLIST TYPE] Checklist: [FEATURE NAME]
- Laravel Application
- Laravel Application
- scribe.php
- Code Agent
- Money
- 25. Code Review Gate
- 30.1 Commit e Pull Request — Boas práticas (obrigatório)
- console.php
- sdd-security-review/SKILL.md
- SDD Multi-Agent — Architecture, SOLID, Design Patterns & Security
- 12. Hardcoded Secrets
- 30.2 Graphify — consulta prioritária e rebuild no PR (obrigatório)
- 8. DATABASE SECURITY — RLS / Authorization
- Data Model: Autenticação Admin / Owner
- InvestmentFactory
- Research: Autenticação Admin / Owner
- sanctum.php
- Tasks: Ganho composto no dia civil e imposto no resgate
- Data Model: Ganho composto e imposto
- Research: Ganho composto e imposto
- Architecture-Security Plan: Criar, listar, detalhar e resgatar investimentos
- Tasks: Criar, listar, detalhar e resgatar investimentos
- User
- Quickstart: Interface web de investimentos
- Investment
- session.ts
- UserRole
- Architecture-Security Plan: Interface web de investimentos
- Research: API de investimentos
- devDependencies
- Tasks: Interface web de investimentos
- AppServiceProvider.php
- compilerOptions
- LoginTest
- Illuminate\Foundation\Http\FormRequest
- Research: Interface web de investimentos
- DatabaseSeeder
- User.php
- InvestmentValuation
- Detection Checklist
- Process
- Security Best Practices
- Detection Checklist
- Process
- Architecture Best Practices
- Data Model: Interface (visão cliente)
- Architecture Best Practices
- Tailwind CSS Development
- Security Best Practices
- Tailwind CSS Development
- Advanced Query Best Practices
- Events and Notifications Best Practices
- Migration Best Practices
- Queue and Job Best Practices
- Advanced Query Best Practices
- Events and Notifications Best Practices
- Migration Best Practices
- Queue and Job Best Practices
- Controller
- IndexInvestmentRequest
- LoginController.php
- Caching Best Practices
- Database Performance Best Practices
- Eloquent Best Practices
- Caching Best Practices
- Database Performance Best Practices
- Eloquent Best Practices
- Blade and View Best Practices
- Error Handling Best Practices
- Task Scheduling Best Practices
- Endpoint Tests
- Blade and View Best Practices
- Error Handling Best Practices
- Task Scheduling Best Practices
- Endpoint Tests
- LogoutController.php
- .claude/skills/laravel-best-practices/SKILL.md
- Collection Best Practices
- HTTP Client Best Practices
- Mail Best Practices
- Routing and Controller Best Practices
- Convention and Style Best Practices
- Validation and Forms Best Practices
- Assertions
- .claude/skills/testing-best-practices/SKILL.md
- Fakes, Mocks, and Determinism
- Test Suite Performance
- Reviewing Tests
- Collection Best Practices
- HTTP Client Best Practices
- Mail Best Practices
- Routing and Controller Best Practices
- .cursor/skills/laravel-best-practices/SKILL.md
- Convention and Style Best Practices
- Validation and Forms Best Practices
- Assertions
- .cursor/skills/testing-best-practices/SKILL.md
- Fakes, Mocks, and Determinism
- Test Suite Performance
- Reviewing Tests
- Configuration Best Practices
- Naming and Structure
- require-dev
- Naming and Structure
- Factories and Test Data
- Testing Best Practices
- Factories and Test Data
- Testing Best Practices
- vite-env.d.ts
- laravel-boost
- Carbon\CarbonImmutable
- InvalidInvestmentDate
- PHPUnit\Framework\TestCase
- CarbonImmutable
- setup
- Verdict: CODE_REVIEW_PASSED
- config
- ListInvestmentsTest
- e2e-smoke.mjs
- Security review — 004-investment-ui
- StoreInvestmentTest
- RegisterTest
- bootstrap/app.php
- psr-4
- require
- LoginRateLimitTest.php
- post-create-project-cmd
- extra

## God Nodes (most connected - your core abstractions)
1. `User` - 86 edges
2. `TestCase` - 39 edges
3. `Money` - 38 edges
4. `Investment` - 29 edges
5. `InvestmentValuation` - 23 edges
6. `Controller` - 20 edges
7. `compilerOptions` - 20 edges
8. `Tasks: Interface web de investimentos` - 15 edges
9. `Tasks: Autenticação e papéis Admin / Owner` - 14 edges
10. `Tasks: Ganho composto no dia civil e imposto no resgate` - 14 edges

## Surprising Connections (you probably didn't know these)
- `InvestmentValuation` --references--> `CompoundGainCalculator`  [EXTRACTED]
  app/Domain/Investment/InvestmentValuation.php → app/Domain/Investment/CompoundGainCalculator.php
- `InvestmentValuation` --references--> `WithdrawalTaxCalculator`  [EXTRACTED]
  app/Domain/Investment/InvestmentValuation.php → app/Domain/Investment/WithdrawalTaxCalculator.php
- `ListInvestments` --references--> `InvestmentValuation`  [EXTRACTED]
  app/Services/Investment/ListInvestments.php → app/Domain/Investment/InvestmentValuation.php
- `WithdrawInvestment` --references--> `InvestmentValuation`  [EXTRACTED]
  app/Services/Investment/WithdrawInvestment.php → app/Domain/Investment/InvestmentValuation.php
- `CurrentUserController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/Api/CurrentUserController.php → app/Http/Controllers/Controller.php

## Import Cycles
- None detected.

## Communities (183 total, 16 thin omitted)

### Community 0 - "composer.json"
Cohesion: 0.14
Nodes (13): autoload-dev, psr-4, description, keywords, license, minimum-stability, name, prefer-stable (+5 more)

### Community 1 - "sdd-multi-agent.md"
Cohesion: 0.07
Nodes (28): 10. IDOR — Insecure Direct Object Reference, 11. Browser Permissions NÃO são Security Boundaries, 13. XSS / Input Handling, 14. SQL Injection, 15. Mass Assignment, 16. Authentication, 17. Authorization, 18. API Security (+20 more)

### Community 2 - "scripts"
Cohesion: 0.12
Nodes (16): scripts, dev, docs, post-autoload-dump, post-update-cmd, pre-package-uninstall, test, Composer\\Config::disableProcessTimeout (+8 more)

### Community 3 - "Tasks: [FEATURE NAME]"
Cohesion: 0.07
Nodes (26): Dependencies & Execution Order, Format: `[ID] [P?] [Story] Description`, Implementation for User Story 1, Implementation for User Story 2, Implementation for User Story 3, Implementation Strategy, Incremental Delivery, MVP First (User Story 1 Only) (+18 more)

### Community 4 - "TestCase"
Cohesion: 0.07
Nodes (13): Factory, Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Foundation\Testing\TestCase, CurrentUserTest, DatabaseSeederAuthTest, LoginFieldLimitsTest, LogoutTest, PrivilegeEscalationTest (+5 more)

### Community 5 - "speckit-analyze/SKILL.md"
Cohesion: 0.08
Nodes (25): 1. Initialize Analysis Context, 2. Load Artifacts (Progressive Disclosure), 3. Build Semantic Models, 4. Detection Passes (Token-Efficient Analysis), 5. Severity Assignment, 6. Produce Compact Analysis Report, 7. Provide Next Actions, 8. Offer Remediation (+17 more)

### Community 6 - "package.json"
Cohesion: 0.10
Nodes (20): concurrently, @laravel/multiplex, laravel-vite-plugin, devDependencies, concurrently, laravel-vite-plugin, tailwindcss, @tailwindcss/vite (+12 more)

### Community 7 - "Fullstack Test Project <img src="https://raw.githubusercontent.com/Coderockr/fullstack-test/refs/heads/main/coderockr.banner.svg" align="right" height="50px" />"
Cohesion: 0.09
Nodes (22): API (Laravel), Backend (API), Coding Standards, Credits, Deliverables, Design Reference, Frontend (UI), Fullstack Test Project <img src="https://raw.githubusercontent.com/Coderockr/fullstack-test/refs/heads/main/coderockr.banner.svg" align="right" height="50px" /> (+14 more)

### Community 8 - "Execution Steps"
Cohesion: 0.12
Nodes (15): 1. Initialize Convergence Context, 2. Load Artifacts (Progressive Disclosure), 3. Build the Intent Inventory, 4. Assess the Codebase and Classify Findings, 5. Assign Severity, 6. Present the In-Session Findings Summary, 7. Append Convergence Tasks (or report converged), 8. Provide Next Actions (Handoff) (+7 more)

### Community 9 - "common.ps1"
Cohesion: 0.23
Nodes (13): Find-SpecifyRoot(), Format-SpecKitCommand(), Get-CurrentBranch(), Get-FeaturePathsEnv(), Get-InvokeSeparator(), Get-NormalizedPriority(), Get-Python3Command(), Get-RepoRoot() (+5 more)

### Community 10 - "Create tests (before code)"
Cohesion: 0.15
Nodes (12): Authorization, Concurrency, Create tests (before code), IDOR, Injection, Mass assignment, Privilege escalation, Secrets (+4 more)

### Community 11 - "Feature Specification: [FEATURE NAME]"
Cohesion: 0.15
Nodes (12): Assumptions, Edge Cases, Feature Specification: [FEATURE NAME], Functional Requirements, Key Entities *(include if feature involves data)*, Measurable Outcomes, Requirements *(mandatory)*, Success Criteria *(mandatory)* (+4 more)

### Community 12 - "speckit-plan/SKILL.md"
Cohesion: 0.17
Nodes (11): Completion Report, Done When, Key rules, Mandatory Post-Execution Hooks, Outline, Phase 0: Outline & Research, Phase 1: Design & Contracts, Phases (+3 more)

### Community 13 - "speckit-tasks/SKILL.md"
Cohesion: 0.17
Nodes (11): Checklist Format (REQUIRED), Completion Report, Done When, Mandatory Post-Execution Hooks, Outline, Phase Structure, Pre-Execution Checks, SDD Task Ordering (mandatory) (+3 more)

### Community 14 - "Illuminate\Database\Migrations\Migration"
Cohesion: 0.14
Nodes (3): Illuminate\Database\Migrations\Migration, Illuminate\Database\Schema\Blueprint, Illuminate\Support\Facades\Schema

### Community 15 - "Coderockr Fullstack Constitution"
Cohesion: 0.17
Nodes (11): Coderockr Fullstack Constitution, Core Principles, Development Workflow, Governance, I. Specification-First, II. Architecture & Security Before Tests, III. Test-First (NON-NEGOTIABLE), IV. State-Driven Workflow (No Skipped Gates) (+3 more)

### Community 16 - "speckit-specify/SKILL.md"
Cohesion: 0.18
Nodes (10): Completion Report, Done When, For AI Generation, Mandatory Post-Execution Hooks, Outline, Pre-Execution Checks, Quick Guidelines, Section Requirements (+2 more)

### Community 17 - "Architecture-Security Plan: [FEATURE]"
Cohesion: 0.18
Nodes (10): API Inventory, Architecture, Architecture-Security Plan: [FEATURE], Design Patterns, File-by-file Analysis, Line-by-line Analysis, Remaining Risks, Required Tests (+2 more)

### Community 18 - "Core Principles"
Cohesion: 0.18
Nodes (10): Core Principles, Governance, [PRINCIPLE_1_NAME], [PRINCIPLE_2_NAME], [PRINCIPLE_3_NAME], [PRINCIPLE_4_NAME], [PRINCIPLE_5_NAME], [PROJECT_NAME] Constitution (+2 more)

### Community 19 - "22. Test Agent — Security Tests"
Cohesion: 0.20
Nodes (10): 22. Test Agent — Security Tests, Authorization, Concurrency, IDOR, Injection, Mass assignment, Privilege escalation, Secrets (+2 more)

### Community 20 - "Illuminate\Http\Request"
Cohesion: 0.26
Nodes (6): CurrentUserController, ShowInvestmentController, InvestmentResource, UserResource, Illuminate\Http\Request, Illuminate\Http\Resources\Json\JsonResource

### Community 21 - "Code Review Agent"
Cohesion: 0.22
Nodes (8): Architecture, Code Quality, Code Review Agent, File-by-file (obrigatório), Review, Review loop, Security, Severity

### Community 22 - "Git / Pull Request Agent"
Cohesion: 0.22
Nodes (8): Branching (obrigatório), Commits — boas práticas (obrigatório), Dependency security, Diff security review, Final report, Git / Pull Request Agent, Graphify (obrigatório no fim do PR), Pull Request — boas práticas (obrigatório)

### Community 23 - "Architecture-Security Plan: Autenticação e papéis Admin / Owner"
Cohesion: 0.05
Nodes (38): API Inventory, Architecture, Architecture-Security Plan: Autenticação e papéis Admin / Owner, Arquivos existentes, Arquivos novos, Design Patterns, File-by-file Analysis, Line-by-line Analysis (+30 more)

### Community 24 - "Implementation Plan: [FEATURE]"
Cohesion: 0.22
Nodes (8): Complexity Tracking, Constitution Check, Documentation (this feature), Implementation Plan: [FEATURE], Project Structure, Source Code (repository root), Summary, Technical Context

### Community 25 - "Architecture / Security Agent"
Cohesion: 0.25
Nodes (7): Architecture / Security Agent, Determine, File-by-file (obrigatório), Line-by-line (arquivo existente), Line-by-line (arquivo novo), Security non-negotiables, SOLID

### Community 26 - "SDD Orchestrator"
Cohesion: 0.25
Nodes (7): Absolute rule, Definition of Done, Gates, Per-gate skills, SDD Orchestrator, State machine, Workflow

### Community 27 - "speckit-checklist/SKILL.md"
Cohesion: 0.25
Nodes (7): Anti-Examples: What NOT To Do, Checklist Purpose: "Unit Tests for English", Example Checklist Types & Sample Items, Execution Steps, Post-Execution Checks, Pre-Execution Checks, User Input

### Community 28 - "speckit-implement/SKILL.md"
Cohesion: 0.25
Nodes (7): Completion Report, Done When, Mandatory Post-Execution Hooks, Outline, Pre-Execution Checks, SDD Multi-Agent Gate (mandatory), User Input

### Community 29 - "speckit-clarify/SKILL.md"
Cohesion: 0.29
Nodes (6): Completion Report, Done When, Mandatory Post-Execution Hooks, Outline, Pre-Execution Checks, User Input

### Community 30 - "Architecture-Security Plan: Ganho composto no dia civil e imposto no resgate"
Cohesion: 0.05
Nodes (36): API Inventory, Architecture, Architecture-Security Plan: Ganho composto no dia civil e imposto no resgate, Design Patterns, File-by-file Analysis, Line-by-line Analysis, Remaining Risks, Required Tests (+28 more)

### Community 31 - "Tasks: Autenticação e papéis Admin / Owner"
Cohesion: 0.06
Nodes (30): Dependencies & Execution Order, Format: `[ID] [P?] [Story] Description`, Implementation, Implementation for User Story 1, Implementation for User Story 2, Implementation for User Story 4, Implementation Strategy, Incremental Delivery (+22 more)

### Community 32 - "speckit-constitution/SKILL.md"
Cohesion: 0.33
Nodes (5): Outline, Post-Execution Checks, Pre-Execution Checks, Scope Guard, User Input

### Community 33 - "28. Severity"
Cohesion: 0.33
Nodes (6): 28. Severity, CRITICAL, HIGH, INFO, LOW, MEDIUM

### Community 34 - "5. SOLID obrigatório"
Cohesion: 0.33
Nodes (6): 5. SOLID obrigatório, D — Dependency Inversion Principle, I — Interface Segregation Principle, L — Liskov Substitution Principle, O — Open/Closed Principle, S — Single Responsibility Principle

### Community 36 - "logging.php"
Cohesion: 0.40
Nodes (4): Monolog\Handler\NullHandler, Monolog\Handler\StreamHandler, Monolog\Handler\SyslogUdpHandler, Monolog\Processor\PsrLogMessageProcessor

### Community 37 - "speckit-taskstoissues/SKILL.md"
Cohesion: 0.40
Nodes (4): Outline, Post-Execution Checks, Pre-Execution Checks, User Input

### Community 38 - "[CHECKLIST TYPE] Checklist: [FEATURE NAME]"
Cohesion: 0.40
Nodes (4): [Category 1], [Category 2], [CHECKLIST TYPE] Checklist: [FEATURE NAME], Notes

### Community 39 - "Laravel Application"
Cohesion: 0.50
Nodes (3): Agent Setup, Laravel Application, Prerequisites

### Community 40 - "Laravel Application"
Cohesion: 0.50
Nodes (3): Agent Setup, Laravel Application, Prerequisites

### Community 41 - "scribe.php"
Cohesion: 0.50
Nodes (3): Knuckles\Scribe\Config\AuthIn, Knuckles\Scribe\Config\Defaults, Knuckles\Scribe\Extracting\Strategies

### Community 42 - "Code Agent"
Cohesion: 0.50
Nodes (3): Code Agent, Proibido, Required

### Community 43 - "Money"
Cohesion: 0.16
Nodes (4): Money, self, InvalidArgumentException, MoneyTest

### Community 44 - "25. Code Review Gate"
Cohesion: 0.50
Nodes (4): 25. Code Review Gate, Architecture, Code Quality, Security

### Community 45 - "30.1 Commit e Pull Request — Boas práticas (obrigatório)"
Cohesion: 0.50
Nodes (4): 30.1 Commit e Pull Request — Boas práticas (obrigatório), Branching (obrigatório), Commit, Pull Request

### Community 66 - "Data Model: Autenticação Admin / Owner"
Cohesion: 0.09
Nodes (21): Data Model: Autenticação Admin / Owner, Factory states, Out of scope, Seed records, Session (token Sanctum), State transitions, User (Pessoa), UserRole (enum) (+13 more)

### Community 67 - "InvestmentFactory"
Cohesion: 0.14
Nodes (7): InvestmentFactory, static, Illuminate\Auth\AuthenticationException, Illuminate\Database\Eloquent\Factories\Factory, Illuminate\Support\Facades\Hash, Illuminate\Support\Str, Pdo\Mysql

### Community 68 - "Research: Autenticação Admin / Owner"
Cohesion: 0.17
Nodes (11): 1. Mecanismo de sessão da API, 2. Onde vive o papel, 3. Camadas (Controller vs Service), 4. Endpoints e IDOR, 5. Rate limit (FR-011), 6. Mensagem de falha (FR-009 / SC-006), 7. Seed e secrets, 8. Scribe (+3 more)

### Community 69 - "sanctum.php"
Cohesion: 0.40
Nodes (4): Illuminate\Cookie\Middleware\EncryptCookies, Illuminate\Foundation\Http\Middleware\ValidateCsrfToken, Laravel\Sanctum\Http\Middleware\AuthenticateSession, Laravel\Sanctum\Sanctum

### Community 70 - "Tasks: Ganho composto no dia civil e imposto no resgate"
Cohesion: 0.06
Nodes (31): Dependencies & Execution Order, Format: `[ID] [P?] [Story] Description`, Implementation, Implementation for User Story 1, Implementation for User Story 2, Implementation for User Story 3, Implementation for User Story 4, Implementation Strategy (+23 more)

### Community 71 - "Data Model: Ganho composto e imposto"
Cohesion: 0.10
Nodes (17): CivilMonthAnniversary, CompoundGainCalculator, Domain contract: compound gain and withdrawal tax, InvestmentValuation, WithdrawalTaxCalculator, Civil anniversary, Compound gain input, Data Model: Ganho composto e imposto (+9 more)

### Community 72 - "Research: Ganho composto e imposto"
Cohesion: 0.20
Nodes (9): 1. Onde vive o cálculo, 2. Representação de dinheiro, 3. Compostagem e arredondamento, 4. Aniversário civil (clamp), 5. Imposto (faixas), 6. Data inválida e freeze, 7. HTTP / Scribe / Auth, Research: Ganho composto e imposto (+1 more)

### Community 73 - "Architecture-Security Plan: Criar, listar, detalhar e resgatar investimentos"
Cohesion: 0.05
Nodes (36): API Inventory, Architecture, Architecture-Security Plan: Criar, listar, detalhar e resgatar investimentos, Design Patterns, File-by-file Analysis, Line-by-line Analysis, Remaining Risks, Required Tests (+28 more)

### Community 74 - "Tasks: Criar, listar, detalhar e resgatar investimentos"
Cohesion: 0.06
Nodes (31): Dependencies & Execution Order, Format: `[ID] [P?] [Story] Description`, Implementation, Implementation for User Story 1, Implementation for User Story 2, Implementation for User Story 3, Implementation for User Story 4, Implementation Strategy (+23 more)

### Community 75 - "User"
Cohesion: 0.11
Nodes (6): User, InvestmentPolicy, Illuminate\Foundation\Auth\User, InvestmentAuthorizationTest, ShowInvestmentTest, WithdrawInvestmentTest

### Community 76 - "Quickstart: Interface web de investimentos"
Cohesion: 0.05
Nodes (37): Data Model: Investimentos, Evaluation (não persistida), Factory, Investment, Out of scope, State transitions, User (existente), Validation rules (+29 more)

### Community 77 - "Investment"
Cohesion: 0.13
Nodes (9): Investment, ListInvestments, self, ValuedInvestment, Illuminate\Contracts\Pagination\LengthAwarePaginator, Illuminate\Database\Eloquent\Builder, Illuminate\Database\Eloquent\Factories\HasFactory, Illuminate\Database\Eloquent\Model (+1 more)

### Community 78 - "session.ts"
Cohesion: 0.06
Nodes (57): login(), logout(), register(), apiBaseUrl(), ApiError, apiRequest(), RequestOptions, user (+49 more)

### Community 79 - "UserRole"
Cohesion: 0.15
Nodes (4): UserRole, RegisterUser, UserRoleTest, UserTest

### Community 80 - "Architecture-Security Plan: Interface web de investimentos"
Cohesion: 0.05
Nodes (37): API Inventory, Architecture, Architecture-Security Plan: Interface web de investimentos, Design Patterns, File-by-file Analysis, Line-by-line Analysis, Remaining Risks, Required Tests (+29 more)

### Community 81 - "Research: API de investimentos"
Cohesion: 0.15
Nodes (12): 10. Scribe, 1. Camadas, 2. Autorização e IDOR (FR-004, FR-005, SC-003), 3. Mass assignment e dono (FR-003), 4. Dinheiro e datas, 5. Exemplo 1000 / 1200 / 45 no HTTP, 6. Paginação (FR-009), 7. Concorrência de resgate (FR-013) (+4 more)

### Community 82 - "devDependencies"
Cohesion: 0.05
Nodes (38): dependencies, react, react-dom, react-router-dom, devDependencies, jsdom, @testing-library/jest-dom, @testing-library/react (+30 more)

### Community 83 - "Tasks: Interface web de investimentos"
Cohesion: 0.06
Nodes (34): Dependencies & Execution Order, Format: `[ID] [P?] [Story] Description`, Implementation, Implementation for User Story 1, Implementation for User Story 2, Implementation for User Story 3, Implementation for User Story 4, Implementation for User Story 5 (+26 more)

### Community 84 - "AppServiceProvider.php"
Cohesion: 0.33
Nodes (3): AppServiceProvider, Illuminate\Cache\RateLimiting\Limit, Illuminate\Support\ServiceProvider

### Community 85 - "compilerOptions"
Cohesion: 0.07
Nodes (26): compilerOptions, allowImportingTsExtensions, baseUrl, isolatedModules, jsx, lib, module, moduleDetection (+18 more)

### Community 87 - "Illuminate\Foundation\Http\FormRequest"
Cohesion: 0.14
Nodes (6): RegisterRequest, StoreInvestmentRequest, WithdrawInvestmentRequest, AuthFieldLimits, Illuminate\Foundation\Http\FormRequest, Illuminate\Validation\Rules\Password

### Community 88 - "Research: Interface web de investimentos"
Cohesion: 0.15
Nodes (12): 10. Scribe / API, 1. Onde vive a SPA, 2. Sessão no browser, 3. Papel Admin / Owner na UI, 4. Cálculos monetários, 5. Roteamento e telas, 6. Cliente HTTP, 7. Visual / Figma (+4 more)

### Community 89 - "DatabaseSeeder"
Cohesion: 0.60
Nodes (3): DatabaseSeeder, Illuminate\Database\Console\Seeds\WithoutModelEvents, Illuminate\Database\Seeder

### Community 90 - "User.php"
Cohesion: 0.19
Nodes (7): static, UserFactory, Illuminate\Database\Eloquent\Attributes\Fillable, Illuminate\Database\Eloquent\Attributes\Hidden, Illuminate\Database\Eloquent\Relations\HasMany, Illuminate\Notifications\Notifiable, Laravel\Sanctum\HasApiTokens

### Community 91 - "InvestmentValuation"
Cohesion: 0.18
Nodes (6): InvestmentValuation, CreateInvestment, ShowInvestment, Illuminate\Database\Eloquent\ModelNotFoundException, Illuminate\Validation\ValidationException, InvestmentValuationTest

### Community 92 - "Detection Checklist"
Cohesion: 0.17
Nodes (11): A. Validation & HTTP input, B. Controllers & routing, C. Authorization, D. Eloquent & models, Detection Checklist, E. Architecture & organization, F. Frontend & views, G. Database & migrations (+3 more)

### Community 93 - "Process"
Cohesion: 0.17
Nodes (11): Edge cases, Glob mapping, Ground Rules (read before you start), Infer Conventions, Process, Step 0: Orient, Step 1: Predefined sweep, Step 2: Open-ended pass (+3 more)

### Community 94 - "Security Best Practices"
Cohesion: 0.17
Nodes (11): Apply Cross-Site Request Forgery Protection, Audit Dependencies, Authorize Protected Actions, Bind Query Parameters, Control Mass Assignment, Encrypt Sensitive Attributes When Appropriate, Escape Output in Its Context, Keep Secrets Out of Application Code (+3 more)

### Community 95 - "Detection Checklist"
Cohesion: 0.17
Nodes (11): A. Validation & HTTP input, B. Controllers & routing, C. Authorization, D. Eloquent & models, Detection Checklist, E. Architecture & organization, F. Frontend & views, G. Database & migrations (+3 more)

### Community 96 - "Process"
Cohesion: 0.17
Nodes (11): Edge cases, Glob mapping, Ground Rules (read before you start), Infer Conventions, Process, Step 0: Orient, Step 1: Predefined sweep, Step 2: Open-ended pass (+3 more)

### Community 97 - "Architecture Best Practices"
Cohesion: 0.17
Nodes (11): Architecture Best Practices, Depend on Contracts at Boundaries, Extract Focused Business Operations, Follow Framework Conventions, Inject Required Dependencies, Specify a Deterministic Sort Order, Use Atomic Locks for Race Conditions, Use `Concurrency::run()` for Parallel Execution (+3 more)

### Community 98 - "Data Model: Interface (visão cliente)"
Cohesion: 0.17
Nodes (11): Create, Data Model: Interface (visão cliente), Formulários (só input), Investment (visão), InvestmentPage, Login, Out of scope, Session (+3 more)

### Community 99 - "Architecture Best Practices"
Cohesion: 0.18
Nodes (11): Architecture Best Practices, Depend on Contracts at Boundaries, Extract Focused Business Operations, Follow Framework Conventions, Inject Required Dependencies, Specify a Deterministic Sort Order, Use Atomic Locks for Race Conditions, Use `Concurrency::run()` for Parallel Execution (+3 more)

### Community 100 - "Tailwind CSS Development"
Cohesion: 0.18
Nodes (10): Basic Usage, Common Pitfalls, CSS-First Configuration, Dark Mode, Documentation, Import Syntax, Replaced Utilities, Spacing (+2 more)

### Community 101 - "Security Best Practices"
Cohesion: 0.17
Nodes (11): Apply Cross-Site Request Forgery Protection, Audit Dependencies, Authorize Protected Actions, Bind Query Parameters, Control Mass Assignment, Encrypt Sensitive Attributes When Appropriate, Escape Output in Its Context, Keep Secrets Out of Application Code (+3 more)

### Community 102 - "Tailwind CSS Development"
Cohesion: 0.18
Nodes (10): Basic Usage, Common Pitfalls, CSS-First Configuration, Dark Mode, Documentation, Import Syntax, Replaced Utilities, Spacing (+2 more)

### Community 103 - "Advanced Query Best Practices"
Cohesion: 0.20
Nodes (9): Advanced Query Best Practices, Combine Related Counts with Conditional Aggregates, Compare `whereHas()` with an `IN` Subquery, Consider a Correlated Subquery for Has-Many Ordering, Create Dynamic Relationships with a Subquery Foreign Key, Design Composite Indexes for the Query, Measure Two Simple Queries Against One Complex Query, Reuse Loaded Parent Models with `setRelation()` (+1 more)

### Community 104 - "Events and Notifications Best Practices"
Cohesion: 0.20
Nodes (9): Cache Event Discovery During Production Deployment, Dispatch Queued Notifications After Commit, Events and Notifications Best Practices, Implement `HasLocalePreference` on Notifiable Models, Queue Slow Notifications, Rely on Event Discovery, Route Notification Channels to Dedicated Queues, Use On-Demand Notifications for Non-User Recipients (+1 more)

### Community 105 - "Migration Best Practices"
Cohesion: 0.20
Nodes (9): Define Foreign-Key Constraints Deliberately, Design Indexes for Real Queries, Generate Migrations with Artisan, Keep Migrations Focused, Make Rollbacks Honest, Migration Best Practices, Mirror Defaults Only When Unsaved Models Need Them, Stage Changes That Affect Existing Rows (+1 more)

### Community 106 - "Queue and Job Best Practices"
Cohesion: 0.20
Nodes (9): Back Off Transient Failures, Batch Jobs for Group Coordination, Configure Time-Based Retry Limits Deliberately, Handle Terminal Failure When Needed, Keep Reservation Time Longer Than Execution Time, Queue and Job Best Practices, Rate Limit External Calls, Use Horizon for Redis Queue Operations (+1 more)

### Community 107 - "Advanced Query Best Practices"
Cohesion: 0.20
Nodes (9): Advanced Query Best Practices, Combine Related Counts with Conditional Aggregates, Compare `whereHas()` with an `IN` Subquery, Consider a Correlated Subquery for Has-Many Ordering, Create Dynamic Relationships with a Subquery Foreign Key, Design Composite Indexes for the Query, Measure Two Simple Queries Against One Complex Query, Reuse Loaded Parent Models with `setRelation()` (+1 more)

### Community 108 - "Events and Notifications Best Practices"
Cohesion: 0.20
Nodes (9): Cache Event Discovery During Production Deployment, Dispatch Queued Notifications After Commit, Events and Notifications Best Practices, Implement `HasLocalePreference` on Notifiable Models, Queue Slow Notifications, Rely on Event Discovery, Route Notification Channels to Dedicated Queues, Use On-Demand Notifications for Non-User Recipients (+1 more)

### Community 109 - "Migration Best Practices"
Cohesion: 0.20
Nodes (9): Define Foreign-Key Constraints Deliberately, Design Indexes for Real Queries, Generate Migrations with Artisan, Keep Migrations Focused, Make Rollbacks Honest, Migration Best Practices, Mirror Defaults Only When Unsaved Models Need Them, Stage Changes That Affect Existing Rows (+1 more)

### Community 110 - "Queue and Job Best Practices"
Cohesion: 0.20
Nodes (9): Back Off Transient Failures, Batch Jobs for Group Coordination, Configure Time-Based Retry Limits Deliberately, Handle Terminal Failure When Needed, Keep Reservation Time Longer Than Execution Time, Queue and Job Best Practices, Rate Limit External Calls, Use Horizon for Redis Queue Operations (+1 more)

### Community 111 - "Controller"
Cohesion: 0.21
Nodes (8): HealthController, RegisterController, StoreInvestmentController, WithdrawInvestmentController, Controller, Illuminate\Foundation\Auth\Access\AuthorizesRequests, Illuminate\Http\JsonResponse, Illuminate\Support\Facades\Route

### Community 112 - "IndexInvestmentRequest"
Cohesion: 0.24
Nodes (3): IndexInvestmentController, IndexInvestmentRequest, Illuminate\Http\Resources\Json\AnonymousResourceCollection

### Community 113 - "LoginController.php"
Cohesion: 0.32
Nodes (3): LoginController, LoginRequest, LoginUser

### Community 114 - "Caching Best Practices"
Cohesion: 0.22
Nodes (8): Caching Best Practices, Configure Failover Cache Stores in Production, Consider `Cache::flexible()` for Stale-While-Revalidate, Use `Cache::add()` for Atomic Conditional Writes, Use `Cache::memo()` to Avoid Redundant Hits Within an Execution, Use `Cache::remember()` for Cache-Aside Reads, Use Cache Tags to Invalidate Related Groups, Use `once()` for In-Process Memoization

### Community 115 - "Database Performance Best Practices"
Cohesion: 0.22
Nodes (8): Add Indexes for Measured Query Patterns, Count Relationships Without Loading Them, Database Performance Best Practices, Eager Load Relationships Before Iterating, Keep Queries Out of Blade Templates, Prevent Lazy Loading in Development, Process Large Data Sets Incrementally, Select Only Needed Columns

### Community 116 - "Eloquent Best Practices"
Cohesion: 0.22
Nodes (8): Apply Global Scopes Sparingly, Cast Date and Time Attributes, Define Attribute Casts, Define Precise Relationship Types, Eloquent Best Practices, Keep Application Queries Model-Aware, Use Local Scopes for Reusable Queries, Use `whereBelongsTo()` for Relationship Queries

### Community 117 - "Caching Best Practices"
Cohesion: 0.22
Nodes (8): Caching Best Practices, Configure Failover Cache Stores in Production, Consider `Cache::flexible()` for Stale-While-Revalidate, Use `Cache::add()` for Atomic Conditional Writes, Use `Cache::memo()` to Avoid Redundant Hits Within an Execution, Use `Cache::remember()` for Cache-Aside Reads, Use Cache Tags to Invalidate Related Groups, Use `once()` for In-Process Memoization

### Community 118 - "Database Performance Best Practices"
Cohesion: 0.22
Nodes (8): Add Indexes for Measured Query Patterns, Count Relationships Without Loading Them, Database Performance Best Practices, Eager Load Relationships Before Iterating, Keep Queries Out of Blade Templates, Prevent Lazy Loading in Development, Process Large Data Sets Incrementally, Select Only Needed Columns

### Community 119 - "Eloquent Best Practices"
Cohesion: 0.22
Nodes (8): Apply Global Scopes Sparingly, Cast Date and Time Attributes, Define Attribute Casts, Define Precise Relationship Types, Eloquent Best Practices, Keep Application Queries Model-Aware, Use Local Scopes for Reusable Queries, Use `whereBelongsTo()` for Relationship Queries

### Community 120 - "Blade and View Best Practices"
Cohesion: 0.25
Nodes (7): Blade and View Best Practices, Prefer Components for Explicit Interfaces, Return Blade Fragments for Partial Rendering, Share Compatible View Data with a View Composer, Share Parent Component Props with `@aware`, Use `$attributes->merge()` in Component Templates, Use `@pushOnce` for Per-Component Scripts

### Community 121 - "Error Handling Best Practices"
Cohesion: 0.25
Nodes (7): Add Context to Exception Classes, Choose Where to Report and Render Exceptions, Define JSON Rendering for API Routes, Error Handling Best Practices, Mark Exceptions the Handler Should Not Report, Prevent Duplicate Reports of One Exception Instance, Throttle High-Volume Exception Reports

### Community 122 - "Task Scheduling Best Practices"
Cohesion: 0.25
Nodes (7): Bound Work Inside the Task, Group Shared Configuration, Prevent Unwanted Overlap, Restrict Tasks by Environment, Run a Task on One Server, Run Eligible Commands in the Background, Task Scheduling Best Practices

### Community 123 - "Endpoint Tests"
Cohesion: 0.25
Nodes (7): Endpoint Coverage, Endpoint Tests, How to Write the Test, Tenant Isolation, Test Authorization at the Policy Level, Testing Validation, Which Layer Owns Which Case

### Community 124 - "Blade and View Best Practices"
Cohesion: 0.25
Nodes (7): Blade and View Best Practices, Prefer Components for Explicit Interfaces, Return Blade Fragments for Partial Rendering, Share Compatible View Data with a View Composer, Share Parent Component Props with `@aware`, Use `$attributes->merge()` in Component Templates, Use `@pushOnce` for Per-Component Scripts

### Community 125 - "Error Handling Best Practices"
Cohesion: 0.25
Nodes (7): Add Context to Exception Classes, Choose Where to Report and Render Exceptions, Define JSON Rendering for API Routes, Error Handling Best Practices, Mark Exceptions the Handler Should Not Report, Prevent Duplicate Reports of One Exception Instance, Throttle High-Volume Exception Reports

### Community 126 - "Task Scheduling Best Practices"
Cohesion: 0.25
Nodes (7): Bound Work Inside the Task, Group Shared Configuration, Prevent Unwanted Overlap, Restrict Tasks by Environment, Run a Task on One Server, Run Eligible Commands in the Background, Task Scheduling Best Practices

### Community 127 - "Endpoint Tests"
Cohesion: 0.25
Nodes (7): Endpoint Coverage, Endpoint Tests, How to Write the Test, Tenant Isolation, Test Authorization at the Policy Level, Testing Validation, Which Layer Owns Which Case

### Community 129 - ".claude/skills/laravel-best-practices/SKILL.md"
Cohesion: 0.29
Nodes (5): Consistency First, Decision Rules, How to Apply, Laravel Best Practices, Rule Index

### Community 130 - "Collection Best Practices"
Cohesion: 0.29
Nodes (6): Choose Between `cursor()` and `lazy()`, Collection Best Practices, Use `#[CollectedBy]` for Custom Collection Classes, Use Higher-Order Messages for Simple Operations, Use `lazyById()` When Updating Records While Iterating, Use `toQuery()` for Bulk Operations on Collections

### Community 131 - "HTTP Client Best Practices"
Cohesion: 0.29
Nodes (6): Fake HTTP Requests in Tests, Handle Errors Explicitly, HTTP Client Best Practices, Pool Independent Requests, Retry Only Safe Operations, Set Explicit Timeouts

### Community 132 - "Mail Best Practices"
Cohesion: 0.29
Nodes (6): Assert the Delivery Mode, Dispatch Queued Mail After Commit, Mail Best Practices, Queue Slow Mail Delivery, Separate Content and Delivery Tests, Use Markdown Mailables When They Fit

### Community 133 - "Routing and Controller Best Practices"
Cohesion: 0.29
Nodes (6): Keep Controllers Focused on HTTP Concerns, Organize Controllers Around Resources, Routing and Controller Best Practices, Scope Nested Bindings, Use Implicit Route Model Binding, Use Resource Routes for Resourceful Actions

### Community 134 - "Convention and Style Best Practices"
Cohesion: 0.29
Nodes (6): Convention and Style Best Practices, Follow Project Naming Conventions, Keep Presentation Code Maintainable, Prefer Clear, Idiomatic Syntax, Use Utilities When They Clarify Intent, Write Comments That Explain Why

### Community 135 - "Validation and Forms Best Practices"
Cohesion: 0.29
Nodes (6): Add Cross-Field Validation After Base Rules, Express Conditional Rules Clearly, Extract Validation When It Improves the Boundary, Prefer Readable Rule Syntax, Use Only Intended Validated Data, Validation and Forms Best Practices

### Community 136 - "Assertions"
Cohesion: 0.29
Nodes (6): Arrange, Act, Assert, Assert a Known Value, Assert the Complete Result, Assertions, How to Find the Correct Assertion, Named Response Assertions

### Community 137 - ".claude/skills/testing-best-practices/SKILL.md"
Cohesion: 0.29
Nodes (3): Built-in Laravel Assertion Methods, How to Find Test Framework Features, Security Tests

### Community 138 - "Fakes, Mocks, and Determinism"
Cohesion: 0.29
Nodes (7): Database, Fakes, Mocks, and Determinism, Framework Fakes, How to Isolate a Dependency, Mocking, Outbound HTTP Testing, Time and Randomness

### Community 139 - "Test Suite Performance"
Cohesion: 0.29
Nodes (6): Common Errors, Global Fakes, How to Find a Slow Test, How to Run the Suite in Parallel, Test Environment, Test Suite Performance

### Community 140 - "Reviewing Tests"
Cohesion: 0.29
Nodes (6): Assertions, Coverage, Data and Determinism, Names and Structure, Reviewing Tests, Test Value

### Community 141 - "Collection Best Practices"
Cohesion: 0.29
Nodes (6): Choose Between `cursor()` and `lazy()`, Collection Best Practices, Use `#[CollectedBy]` for Custom Collection Classes, Use Higher-Order Messages for Simple Operations, Use `lazyById()` When Updating Records While Iterating, Use `toQuery()` for Bulk Operations on Collections

### Community 142 - "HTTP Client Best Practices"
Cohesion: 0.29
Nodes (6): Fake HTTP Requests in Tests, Handle Errors Explicitly, HTTP Client Best Practices, Pool Independent Requests, Retry Only Safe Operations, Set Explicit Timeouts

### Community 143 - "Mail Best Practices"
Cohesion: 0.29
Nodes (6): Assert the Delivery Mode, Dispatch Queued Mail After Commit, Mail Best Practices, Queue Slow Mail Delivery, Separate Content and Delivery Tests, Use Markdown Mailables When They Fit

### Community 144 - "Routing and Controller Best Practices"
Cohesion: 0.29
Nodes (6): Keep Controllers Focused on HTTP Concerns, Organize Controllers Around Resources, Routing and Controller Best Practices, Scope Nested Bindings, Use Implicit Route Model Binding, Use Resource Routes for Resourceful Actions

### Community 145 - ".cursor/skills/laravel-best-practices/SKILL.md"
Cohesion: 0.17
Nodes (10): Configuration Best Practices, Name Repeated Domain Values, Protect Production Secrets, Read Environment Variables in Configuration Files, Use `App::environment()` for Environment Checks, Consistency First, Decision Rules, How to Apply (+2 more)

### Community 146 - "Convention and Style Best Practices"
Cohesion: 0.29
Nodes (6): Convention and Style Best Practices, Follow Project Naming Conventions, Keep Presentation Code Maintainable, Prefer Clear, Idiomatic Syntax, Use Utilities When They Clarify Intent, Write Comments That Explain Why

### Community 147 - "Validation and Forms Best Practices"
Cohesion: 0.29
Nodes (6): Add Cross-Field Validation After Base Rules, Express Conditional Rules Clearly, Extract Validation When It Improves the Boundary, Prefer Readable Rule Syntax, Use Only Intended Validated Data, Validation and Forms Best Practices

### Community 148 - "Assertions"
Cohesion: 0.29
Nodes (6): Arrange, Act, Assert, Assert a Known Value, Assert the Complete Result, Assertions, How to Find the Correct Assertion, Named Response Assertions

### Community 149 - ".cursor/skills/testing-best-practices/SKILL.md"
Cohesion: 0.29
Nodes (3): Built-in Laravel Assertion Methods, How to Find Test Framework Features, Security Tests

### Community 150 - "Fakes, Mocks, and Determinism"
Cohesion: 0.29
Nodes (7): Database, Fakes, Mocks, and Determinism, Framework Fakes, How to Isolate a Dependency, Mocking, Outbound HTTP Testing, Time and Randomness

### Community 151 - "Test Suite Performance"
Cohesion: 0.29
Nodes (6): Common Errors, Global Fakes, How to Find a Slow Test, How to Run the Suite in Parallel, Test Environment, Test Suite Performance

### Community 152 - "Reviewing Tests"
Cohesion: 0.29
Nodes (6): Assertions, Coverage, Data and Determinism, Names and Structure, Reviewing Tests, Test Value

### Community 153 - "Configuration Best Practices"
Cohesion: 0.33
Nodes (5): Configuration Best Practices, Name Repeated Domain Values, Protect Production Secrets, Read Environment Variables in Configuration Files, Use `App::environment()` for Environment Checks

### Community 154 - "Naming and Structure"
Cohesion: 0.33
Nodes (5): File Layout, Grouping, Naming and Structure, Naming Tests, Test Class and Methods

### Community 155 - "require-dev"
Cohesion: 0.22
Nodes (9): require-dev, fakerphp/faker, knuckleswtf/scribe, laravel/pail, laravel/pao, laravel/pint, mockery/mockery, nunomaduro/collision (+1 more)

### Community 156 - "Naming and Structure"
Cohesion: 0.33
Nodes (5): File Layout, Grouping, Naming and Structure, Naming Tests, Test Class and Methods

### Community 157 - "Factories and Test Data"
Cohesion: 0.40
Nodes (4): Data Providers, Each Test Makes Its Own Data, Factories and Test Data, Record Construction

### Community 158 - "Testing Best Practices"
Cohesion: 0.40
Nodes (5): Consistency First, How to Apply, Rule Index, Testing Best Practices, What to Test

### Community 159 - "Factories and Test Data"
Cohesion: 0.40
Nodes (4): Data Providers, Each Test Makes Its Own Data, Factories and Test Data, Record Construction

### Community 160 - "Testing Best Practices"
Cohesion: 0.40
Nodes (5): Consistency First, How to Apply, Rule Index, Testing Best Practices, What to Test

### Community 165 - "Carbon\CarbonImmutable"
Cohesion: 0.23
Nodes (3): WithdrawalTaxCalculator, Carbon\CarbonImmutable, WithdrawalTaxCalculatorTest

### Community 166 - "InvalidInvestmentDate"
Cohesion: 0.18
Nodes (5): InvalidInvestmentDate, InvestmentAlreadyWithdrawn, WithdrawInvestment, DomainException, Illuminate\Support\Facades\DB

### Community 167 - "PHPUnit\Framework\TestCase"
Cohesion: 0.24
Nodes (4): CompoundGainCalculator, PHPUnit\Framework\TestCase, CompoundGainCalculatorTest, ExampleTest

### Community 168 - "CarbonImmutable"
Cohesion: 0.44
Nodes (3): CivilMonthAnniversary, CarbonImmutable, CivilMonthAnniversaryTest

### Community 169 - "setup"
Cohesion: 0.25
Nodes (8): post-root-package-install, setup, composer install, npm install --ignore-scripts, npm run build, @php artisan key:generate, @php artisan migrate --force, @php -r \"file_exists('.env') || copy('.env.example', '.env');\

### Community 170 - "Verdict: CODE_REVIEW_PASSED"
Cohesion: 0.29
Nodes (6): Architecture, Code review — 004-investment-ui, Notes (non-blocking), Quality, Security, Verdict: CODE_REVIEW_PASSED

### Community 171 - "config"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 173 - "e2e-smoke.mjs"
Cohesion: 1.00
Nodes (3): apiLogin(), assert(), main()

### Community 177 - "bootstrap/app.php"
Cohesion: 0.40
Nodes (3): Illuminate\Foundation\Application, Illuminate\Foundation\Configuration\Exceptions, Illuminate\Foundation\Configuration\Middleware

### Community 178 - "psr-4"
Cohesion: 0.40
Nodes (5): autoload, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\

### Community 179 - "require"
Cohesion: 0.40
Nodes (5): require, laravel/framework, laravel/sanctum, laravel/tinker, php

### Community 181 - "post-create-project-cmd"
Cohesion: 0.50
Nodes (4): post-create-project-cmd, @php artisan key:generate --ansi, @php artisan migrate --graceful --ansi, @php -r \"file_exists('database/database.sqlite') || touch('database/database.sqlite');\

### Community 182 - "extra"
Cohesion: 0.67
Nodes (3): extra, laravel, dont-discover

## Knowledge Gaps
- **1104 isolated node(s):** `php`, `$schema`, `name`, `type`, `description` (+1099 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 1250 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **16 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User` to `InvestmentFactory`, `TestCase`, `InvalidInvestmentDate`, `ListInvestmentsTest`, `Investment`, `UserRole`, `RegisterTest`, `LoginController.php`, `StoreInvestmentTest`, `LoginRateLimitTest.php`, `LoginTest`, `DatabaseSeeder`, `User.php`, `InvestmentValuation`?**
  _High betweenness centrality (0.016) - this node is a cross-community bridge._
- **Why does `Money` connect `Money` to `Carbon\CarbonImmutable`, `InvalidInvestmentDate`, `PHPUnit\Framework\TestCase`, `Investment`, `InvestmentValuation`?**
  _High betweenness centrality (0.006) - this node is a cross-community bridge._
- **Why does `Investment` connect `Investment` to `InvestmentFactory`, `InvalidInvestmentDate`, `User`, `Controller`, `IndexInvestmentRequest`, `InvestmentValuation`?**
  _High betweenness centrality (0.004) - this node is a cross-community bridge._
- **What connects `php`, `$schema`, `name` to the rest of the system?**
  _1104 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `composer.json` be split into smaller, more focused modules?**
  _Cohesion score 0.14285714285714285 - nodes in this community are weakly interconnected._
- **Should `sdd-multi-agent.md` be split into smaller, more focused modules?**
  _Cohesion score 0.06896551724137931 - nodes in this community are weakly interconnected._
- **Should `scripts` be split into smaller, more focused modules?**
  _Cohesion score 0.125 - nodes in this community are weakly interconnected._