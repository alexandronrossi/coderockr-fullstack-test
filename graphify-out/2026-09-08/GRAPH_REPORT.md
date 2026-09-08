# Graph Report - coderockr-fullstack-test  (2026-09-08)

## Corpus Check
- 77 files · ~48,862 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 555 nodes · 532 edges · 66 communities (43 shown, 9 thin omitted)
- Extraction: 100% EXTRACTED · 0% INFERRED · 0% AMBIGUOUS
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `bc5be56d`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- composer.json
- sdd-multi-agent.md
- scripts
- Tasks: [FEATURE NAME]
- User
- speckit-analyze/SKILL.md
- package.json
- Fullstack Test Project <img src="https://raw.githubusercontent.com/Coderockr/fullstack-test/refs/heads/main/coderockr.banner.svg" align="right" height="50px" />
- Execution Steps
- common.ps1
- Create tests (before code)
- Feature Specification: [FEATURE NAME]
- speckit-plan/SKILL.md
- speckit-tasks/SKILL.md
- 0001_01_01_000000_create_users_table.php
- Coderockr Fullstack Constitution
- speckit-specify/SKILL.md
- Architecture-Security Plan: [FEATURE]
- Core Principles
- 22. Test Agent — Security Tests
- HealthController
- Code Review Agent
- Git / Pull Request Agent
- TestCase
- Implementation Plan: [FEATURE]
- Architecture / Security Agent
- SDD Orchestrator
- speckit-checklist/SKILL.md
- speckit-implement/SKILL.md
- speckit-clarify/SKILL.md
- AppServiceProvider
- bootstrap/app.php
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
- ExampleTest
- 25. Code Review Gate
- 30.1 Commit e Pull Request — Boas práticas (obrigatório)
- console.php
- sdd-security-review/SKILL.md
- SDD Multi-Agent — Architecture, SOLID, Design Patterns & Security
- 12. Hardcoded Secrets
- 30.2 Graphify — consulta prioritária e rebuild no PR (obrigatório)
- 8. DATABASE SECURITY — RLS / Authorization

## God Nodes (most connected - your core abstractions)
1. `Tasks: [FEATURE NAME]` - 13 edges
2. `Fullstack Test Project <img src="https://raw.githubusercontent.com/Coderockr/fullstack-test/refs/heads/main/coderockr.banner.svg" align="right" height="50px" />` - 11 edges
3. `scripts` - 10 edges
4. `Create tests (before code)` - 10 edges
5. `22. Test Agent — Security Tests` - 10 edges
6. `Architecture-Security Plan: [FEATURE]` - 10 edges
7. `User` - 9 edges
8. `require-dev` - 9 edges
9. `Git / Pull Request Agent` - 8 edges
10. `setup` - 7 edges

## Surprising Connections (you probably didn't know these)
- `HealthController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/Api/HealthController.php → app/Http/Controllers/Controller.php
- `HealthTest` --inherits--> `TestCase`  [EXTRACTED]
  tests/Feature/Api/HealthTest.php → tests/TestCase.php
- `ExampleTest` --inherits--> `TestCase`  [EXTRACTED]
  tests/Feature/ExampleTest.php → tests/TestCase.php

## Import Cycles
- None detected.

## Communities (66 total, 9 thin omitted)

### Community 0 - "composer.json"
Cohesion: 0.05
Nodes (41): pestphp/pest-plugin, php-http/discovery, autoload, autoload-dev, psr-4, psr-4, config, allow-plugins (+33 more)

### Community 1 - "sdd-multi-agent.md"
Cohesion: 0.07
Nodes (28): 10. IDOR — Insecure Direct Object Reference, 11. Browser Permissions NÃO são Security Boundaries, 13. XSS / Input Handling, 14. SQL Injection, 15. Mass Assignment, 16. Authentication, 17. Authorization, 18. API Security (+20 more)

### Community 2 - "scripts"
Cohesion: 0.07
Nodes (28): scripts, dev, docs, post-autoload-dump, post-create-project-cmd, post-root-package-install, post-update-cmd, pre-package-uninstall (+20 more)

### Community 3 - "Tasks: [FEATURE NAME]"
Cohesion: 0.07
Nodes (26): Dependencies & Execution Order, Format: `[ID] [P?] [Story] Description`, Implementation for User Story 1, Implementation for User Story 2, Implementation for User Story 3, Implementation Strategy, Incremental Delivery, MVP First (User Story 1 Only) (+18 more)

### Community 4 - "User"
Cohesion: 0.10
Nodes (15): User, UserFactory, DatabaseSeeder, Illuminate\Database\Console\Seeds\WithoutModelEvents, Illuminate\Database\Eloquent\Attributes\Fillable, Illuminate\Database\Eloquent\Attributes\Hidden, Illuminate\Database\Eloquent\Factories\Factory, Illuminate\Database\Eloquent\Factories\HasFactory (+7 more)

### Community 5 - "speckit-analyze/SKILL.md"
Cohesion: 0.08
Nodes (25): 1. Initialize Analysis Context, 2. Load Artifacts (Progressive Disclosure), 3. Build Semantic Models, 4. Detection Passes (Token-Efficient Analysis), 5. Severity Assignment, 6. Produce Compact Analysis Report, 7. Provide Next Actions, 8. Offer Remediation (+17 more)

### Community 6 - "package.json"
Cohesion: 0.10
Nodes (20): concurrently, @laravel/multiplex, laravel-vite-plugin, devDependencies, concurrently, laravel-vite-plugin, tailwindcss, @tailwindcss/vite (+12 more)

### Community 7 - "Fullstack Test Project <img src="https://raw.githubusercontent.com/Coderockr/fullstack-test/refs/heads/main/coderockr.banner.svg" align="right" height="50px" />"
Cohesion: 0.12
Nodes (16): Backend (API), Coding Standards, Credits, Deliverables, Design Reference, Frontend (UI), Fullstack Test Project <img src="https://raw.githubusercontent.com/Coderockr/fullstack-test/refs/heads/main/coderockr.banner.svg" align="right" height="50px" />, Gain Calculation (+8 more)

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

### Community 14 - "0001_01_01_000000_create_users_table.php"
Cohesion: 0.23
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

### Community 20 - "HealthController"
Cohesion: 0.28
Nodes (4): HealthController, Controller, Illuminate\Http\JsonResponse, Illuminate\Support\Facades\Route

### Community 21 - "Code Review Agent"
Cohesion: 0.22
Nodes (8): Architecture, Code Quality, Code Review Agent, File-by-file (obrigatório), Review, Review loop, Security, Severity

### Community 22 - "Git / Pull Request Agent"
Cohesion: 0.22
Nodes (8): Branching (obrigatório), Commits — boas práticas (obrigatório), Dependency security, Diff security review, Final report, Git / Pull Request Agent, Graphify (obrigatório no fim do PR), Pull Request — boas práticas (obrigatório)

### Community 23 - "TestCase"
Cohesion: 0.28
Nodes (4): Illuminate\Foundation\Testing\TestCase, HealthTest, ExampleTest, TestCase

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

### Community 31 - "bootstrap/app.php"
Cohesion: 0.40
Nodes (4): Illuminate\Foundation\Application, Illuminate\Foundation\Configuration\Exceptions, Illuminate\Foundation\Configuration\Middleware, Illuminate\Http\Request

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

### Community 44 - "25. Code Review Gate"
Cohesion: 0.50
Nodes (4): 25. Code Review Gate, Architecture, Code Quality, Security

### Community 45 - "30.1 Commit e Pull Request — Boas práticas (obrigatório)"
Cohesion: 0.50
Nodes (4): 30.1 Commit e Pull Request — Boas práticas (obrigatório), Branching (obrigatório), Commit, Pull Request

## Knowledge Gaps
- **325 isolated node(s):** `$schema`, `name`, `type`, `description`, `laravel` (+320 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 397 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **9 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `scripts` connect `scripts` to `composer.json`?**
  _High betweenness centrality (0.009) - this node is a cross-community bridge._
- **Why does `22. Test Agent — Security Tests` connect `22. Test Agent — Security Tests` to `sdd-multi-agent.md`?**
  _High betweenness centrality (0.004) - this node is a cross-community bridge._
- **What connects `$schema`, `name`, `type` to the rest of the system?**
  _325 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `composer.json` be split into smaller, more focused modules?**
  _Cohesion score 0.047619047619047616 - nodes in this community are weakly interconnected._
- **Should `sdd-multi-agent.md` be split into smaller, more focused modules?**
  _Cohesion score 0.06896551724137931 - nodes in this community are weakly interconnected._
- **Should `scripts` be split into smaller, more focused modules?**
  _Cohesion score 0.07407407407407407 - nodes in this community are weakly interconnected._
- **Should `Tasks: [FEATURE NAME]` be split into smaller, more focused modules?**
  _Cohesion score 0.07407407407407407 - nodes in this community are weakly interconnected._