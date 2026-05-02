
# AI System Bootstrap Prompt v2

Paste this entire prompt into any AI tool (Cursor, Claude Code, GitHub Copilot, Windsurf, etc.) at the root of a new or existing project to generate a complete, project-specific AI instruction system with native sub-agent support.

---

## Prompt (copy everything below this line)

---

You are going to build a structured AI instruction system for this project.

The system tells future AI agents how to work here: what roles exist, how to classify tasks, what conventions to follow, how to orchestrate sub-agents, and what playbooks to run.

Work through five phases in order. Do not skip phases. Do not generate instruction files until Phase 3.

---

## Phase 0 — Detect AI tooling

Before exploring the project, scan for existing AI tool configuration files. Record which tools are active — this determines which tool-specific files AND which native sub-agent locations to target in Phase 3.

Check for these files at the project root and common locations:

| File | AI tool |
|---|---|
| `.cursorrules` | Cursor |
| `CLAUDE.md` | Claude Code |
| `.github/copilot-instructions.md` | GitHub Copilot |
| `.windsurfrules` | Windsurf |
| `codex.md` | OpenAI Codex |
| `.vscode/settings.json` | Check for `github.copilot` or `cursor` keys |
| `.aider.conf.yml` | Aider |

Also scan for existing sub-agent directories:

| Directory | AI tool |
|---|---|
| `.claude/agents/` | Claude Code native sub-agents |
| `.cursor/agents/` | Cursor agents (if present) |

Record the result as an internal note:

```
DETECTED AI TOOLS: [list tools found, or "none detected"]
DETECTED AGENT DIRS: [list directories found, or "none detected"]
```

Do not generate any files yet.

---

## Phase 1 — Explore the project

Read the project without making changes. Gather:

1. **File tree** — top-level folders, key subdirectories
2. **Stack** — framework, language, styling system, routing, state management, testing tools
   - Check: `package.json`, `requirements.txt`, `go.mod`, `Cargo.toml`, `pyproject.toml`, or equivalent
3. **Architecture** — how the codebase is organised (e.g. `src/components`, `app/`, `lib/`, `internal/`)
4. **Existing canonical docs** — search for TRD, ERD, PRD, and design system files:
   - Search by filename: any file whose name contains `trd`, `erd`, `prd`, `design-system`, `architecture`, `technical-requirements`, `product-requirements`, `data-model`, `schema`
   - Search by content keywords: "Technical Requirements", "Entity Relationship", "Product Requirements", "Design System", "System Architecture"
   - Check: `docs/`, `wiki/`, `README.md`, any `.md` files at root
   - Note: which canonical doc types exist, and where they are currently located
5. **Config files** — `tsconfig`, `eslint`, `prettier`, `.env.example`, CI configs (`.github/workflows`, `Dockerfile`, `docker-compose.yml`)
6. **Implied roles** — what kinds of engineers the codebase suggests
   (e.g. a design system folder implies a designer role; a `tests/` folder implies QA; a `terraform/` or `k8s/` folder implies DevOps)

Summarise findings in a short internal note before moving to Phase 1.5. Do not generate files yet.

---

## Phase 1.5 — Pre-flight docs scaffold

This phase ensures canonical project docs exist before generating instruction files. Every instruction file will reference these docs — they must exist first.

### Step 1: Build a canonical docs map

Based on Phase 1 findings, map each doc type to its current location:

```
CANONICAL DOCS MAP:
- PRD:           [path or "not found"]
- TRD:           [path or "not found"]
- ERD:           [path or "not found"]
- Design System: [path or "not found"]
```

### Step 2: Consolidate into docs/

- If any canonical doc exists **outside** `docs/`, note its current path and reference it from `docs/` by adding a pointer at the top of the file or creating `docs/<type>.md` that begins with `> Source: <original path>`. Do not duplicate content.
- If a canonical doc does **not exist**, create it immediately as a skeleton stub in `docs/` using the template below.
- If `docs/` does not exist, create it.

### Skeleton stub templates

**`docs/prd.md`** — Product Requirements Document
```markdown
# Product Requirements Document

> Status: Draft — fill in each section before starting feature work.

## Problem Statement
<!-- What problem does this product solve, and for whom? -->

## Goals
<!-- What outcomes define success? List 3–5 measurable goals. -->

## Non-Goals
<!-- What is explicitly out of scope? -->

## User Personas
<!-- Who are the primary users? Describe each briefly. -->

## Features & Requirements
<!-- List features. For each: description, priority (P0/P1/P2), acceptance criteria. -->

## Open Questions
<!-- Unresolved decisions that block progress. -->
```

**`docs/trd.md`** — Technical Requirements Document
```markdown
# Technical Requirements Document

> Status: Draft — fill in each section before starting implementation.

## System Overview
<!-- High-level description of the system and its components. -->

## Stack
<!-- Language, framework, runtime, database, hosting. Match what was found in package.json / config files. -->

## Architecture
<!-- Describe folder structure, layers (e.g. presentation / business logic / data), and key patterns. -->

## Key Constraints
<!-- Performance targets, security requirements, compliance, third-party limits. -->

## External Integrations
<!-- APIs, services, SDKs this system depends on. -->

## Deployment
<!-- Environments (dev, staging, prod), CI/CD pipeline, infrastructure. -->

## Open Technical Decisions
<!-- ADR-style: list decisions pending or made with rationale. -->
```

**`docs/erd.md`** — Entity Relationship Document
```markdown
# Entity Relationship Document

> Status: Draft — fill in entities before touching data models.

## Entities

### [EntityName]
| Field | Type | Required | Description |
|---|---|---|---|
| id | uuid | yes | Primary key |

## Relationships
<!-- Describe how entities relate: one-to-many, many-to-many, etc. -->

## Notes
<!-- Indexing decisions, soft delete strategy, multi-tenancy notes. -->
```

**`docs/design-system.md`** — Design System
```markdown
# Design System

> Status: Draft — fill in before implementing any UI.

## Colours
<!-- Primary, secondary, semantic (success, warning, error, info) tokens. -->

## Typography
<!-- Font family, scale (h1–h6, body, caption), line height, weight. -->

## Spacing & Layout
<!-- Base unit, grid system, max widths, breakpoints. -->

## Components
<!-- List reusable components with usage rules. -->

## Icons
<!-- Icon library in use and naming convention. -->

## Motion & Animation
<!-- Duration scale, easing curves, when to animate vs not. -->

## Accessibility
<!-- Minimum contrast ratios, touch target size, focus ring style. -->
```

### Step 3: Print the final docs map

After completing Steps 1–2, print:

```
FINAL CANONICAL DOCS MAP:
- PRD:           docs/prd.md
- TRD:           docs/trd.md
- ERD:           docs/erd.md
- Design System: docs/design-system.md
```

These paths are the authoritative references for all instruction files generated in Phase 3.

Do not generate instruction files yet.

---

## Phase 2 — Interview the user

Ask the following questions as a numbered list. Wait for all answers before proceeding to Phase 3.

1. What roles work on this project? (e.g. frontend developer, backend developer, designer, product manager, DevOps, QA)
2. What task types are most common? (e.g. feature work, bug fixes, refactors, infra changes, design reviews)
3. Are there hard constraints the AI must always respect? (e.g. no new dependencies without approval, no API calls in this phase, must pass CI before committing)
4. Review the canonical docs map printed at the end of Phase 1.5. Are any of those stubs already documented elsewhere? If so, provide the paths so existing content can be referenced.
5. What does "done" look like for a typical feature? (e.g. tests passing, PR approved, deployed to staging)
6. Are there naming conventions, folder rules, or code style rules not captured in linters?
7. Anything the AI should never do in this codebase?
8. Which AI tools does your team use day-to-day? (Select all that apply: Cursor, Claude Code, GitHub Copilot, Windsurf, OpenAI Codex, Aider, other) — note: tool files already detected in Phase 0 will be generated automatically; this question catches any additional tools not yet configured.

Wait for the user to answer all questions before generating any files.

---

## Phase 3 — Generate the instruction system

Using findings from all previous phases, generate the following files. Every detail must be adapted to this project — no placeholders, no generic examples.

---

### Token reduction rule (apply when generating all files below)

Every instruction file must open with this directive:

> **Read order:** Read `ai/agent.md` → select your role → read `ai/agents/<role>.md` → read only the sections of `docs/` relevant to your task → then and only then explore project files for anything still unclear. Do not re-read files you have already read in this session.

---

### File: `docs/prd.md`, `docs/trd.md`, `docs/erd.md`, `docs/design-system.md`

Already created in Phase 1.5. Do not regenerate. If the user provided updated paths in Q4, add a `> See also: <path>` note at the top of the relevant stub.

---

### File: `ai/agent.md`

This is the master entry point for all AI agents. Every tool and every sub-agent must read this file first.

Structure:

**Header:** One sentence describing what this file is and why it must be read first.

**How to operate (numbered steps)**
1. Read this file completely before taking any action.
2. Identify your task type using the Task Classification table below.
3. Select your role using the Agent table below.
4. Read the referenced `docs/` files listed in your agent file — do not skip this step.
5. Execute. Explore project files only for things not covered by the instruction system.
6. Follow the relevant playbook from `ai/playbooks/`.

**Agent table**

| Agent file | Role | When to use |
|---|---|---|
| `ai/agents/developer.md` | Developer | Feature implementation, bug fixes, refactors |
| `ai/agents/designer.md` | Designer / UI-UX | UI changes, component design, design system updates |
| `ai/agents/pm.md` | Product Manager | Scoping, requirements, prioritisation |
| `ai/agents/qa.md` | Test Engineer | Writing tests, reviewing test coverage, QA sign-off |
| `ai/agents/devops.md` | DevOps / Platform | CI/CD, infrastructure, deployment, environment config |

(Include only the roles identified in Phase 1 + Q1. Remove rows for roles not in this project.)

**Task Classification table**

| Task type | Signals | Primary agent | Supporting agents |
|---|---|---|---|
| New feature | "add", "build", "implement" | Developer | PM (scope), Designer (UI), QA (tests) |
| Bug fix | "broken", "error", "wrong", "not working" | Developer | QA |
| UI / design change | "style", "layout", "component", "design" | Designer | Developer |
| Refactor | "clean up", "restructure", "extract" | Developer | QA |
| Infrastructure | "deploy", "CI", "env", "Docker", "pipeline" | DevOps | Developer |
| Requirements | "what should", "scope", "prioritise" | PM | — |
| Testing | "test", "coverage", "regression" | QA | Developer |

**Sub-agent orchestration**

Use sub-agents when a task has independent parallel concerns or requires sequential handoffs across roles.

*When to use a single agent:*
- Task is unambiguous and maps to one role
- Scope is small (< 3 files likely affected)
- No cross-role dependencies

*When to use multiple agents:*
- Task spans multiple roles (e.g. new feature needs design + implementation + tests)
- Parallel subtasks exist that do not depend on each other
- A phase must complete before the next begins (strict handoff order)

*Parallel execution (run at the same time):*
Run agents in parallel when their outputs do not depend on each other. Example:
- Agent A: Review `docs/prd.md` and write acceptance criteria
- Agent B: Review `docs/erd.md` and identify schema changes needed
- Agent C: Check existing test coverage for the affected area

Start all three simultaneously. Merge outputs before implementation begins.

*Sequential execution (run in order):*
Run agents sequentially when later steps depend on earlier outputs. Standard feature order:
1. PM agent — confirms scope against `docs/prd.md`, outputs: acceptance criteria
2. Designer agent — designs UI/UX against `docs/design-system.md`, outputs: component spec
3. Developer agent — implements using outputs from steps 1–2 and `docs/trd.md`
4. QA agent — writes and runs tests, outputs: pass/fail report
5. DevOps agent — deploys if all tests pass

*Handoff protocol:*
Each agent, when finished, must output:
- What was done (summary)
- What the next agent needs to know (inputs for the next step)
- Any open questions or blockers

*Conflict resolution:*

| Dimension | Owner | Overrides |
|---|---|---|
| Product scope | PM | All others |
| Visual design | Designer | Developer |
| Technical feasibility | Developer | PM, Designer |
| Test coverage | QA | Developer |
| Deployment/infra | DevOps | All others |

**Reference docs**

| Task type | Read first |
|---|---|
| Feature / bug | `docs/prd.md`, `docs/trd.md` |
| Data / schema | `docs/erd.md` |
| UI / styling | `docs/design-system.md` |
| Infrastructure | `docs/trd.md` (Deployment section) |
| All tasks | Start with the most relevant doc. Do not read docs unrelated to your task. |

**Playbooks**

| Task | Playbook |
|---|---|
| New feature | `ai/playbooks/feature-flow.md` |
| Bug fix | `ai/playbooks/bugfix-flow.md` |
| Refactor | `ai/playbooks/refactor-flow.md` |

**Core principle**

Read the instruction system first. Explore the project second. Generate code last. Every decision must be traceable to `docs/prd.md`, `docs/trd.md`, `docs/erd.md`, or `docs/design-system.md`.

---

### Files: `ai/agents/<role>.md` — one per role (canonical, tool-agnostic)

These are the single source of truth for each role's behaviour, responsibilities, and rules. All tool-specific sub-agent files (generated below) reference these files — they never duplicate their content.

Each agent file must follow this structure exactly:

```
# [Role] Agent

> Read order: Read ai/agent.md first, then this file, then the docs listed below.

## Role
[One sentence: what this agent owns and is responsible for.]

## Reference docs
Read these before starting any task. Reference sections relevant to your task only.

| Doc | Sections relevant to this role |
|---|---|
| docs/prd.md | [specific sections] |
| docs/trd.md | [specific sections] |
| docs/erd.md | [specific sections] |
| docs/design-system.md | [specific sections] |

Do not extract or copy content from these docs into this file. Reference only.

## Responsibilities
- [bullet list of what this agent does]

## Rules
- Must: [list of things this agent must always do]
- Must not: [list of things this agent must never do]

## Checklist
Before marking a task complete, verify:
- [ ] [specific checkpoint 1]
- [ ] [specific checkpoint 2]
- [ ] [specific checkpoint 3]
```

Generate one file per role identified in Phase 1 + Q1. Minimum: Developer. Add others only if supported by project evidence and interview answers.

Fill every section with project-specific content. No placeholders.

---

### Tool-native sub-agent files — generated per tool detected

This is the key enhancement: each AI tool gets its own natively-formatted sub-agent files, placed where that tool expects to find them. These files are thin wrappers — they point to `ai/agents/<role>.md` for all behavioural rules and never duplicate that content.

---

#### Claude Code — `.claude/agents/<role>.md`

Claude Code discovers sub-agents placed in `.claude/agents/`. Each file requires a YAML frontmatter block followed by a short system prompt.

Generate one file per role. Structure:

```markdown
---
name: [role]
description: >
  [When Claude should automatically delegate to this agent. Be specific about
  trigger phrases and task types so Claude picks the right agent automatically.
  Example: "Use this agent for feature implementation, bug fixes, and refactors.
  Triggered by: 'implement', 'build', 'fix', 'add feature', 'broken'."]
tools: [comma-separated list of tools this agent may use — scope narrowly]
model: claude-sonnet-4-20250514
---

You are the [Role] agent for this project.

Your full behavioural rules, responsibilities, and checklist are defined in `ai/agents/[role].md`.
Read that file completely before taking any action.

Then read `ai/agent.md` for orchestration rules, task classification, and playbook selection.

Your canonical docs are in `docs/`. Read only the sections listed in `ai/agents/[role].md`
for your role — do not read docs unrelated to your current task.

Do not re-read files you have already read in this session.
```

Tool access scoping per role (apply strictly):

| Role | Allowed tools |
|---|---|
| Developer | Read, Write, Edit, Bash, Glob, Grep |
| Designer | Read, Write, Edit, Glob |
| PM | Read, Glob |
| QA | Read, Write, Edit, Bash, Glob, Grep |
| DevOps | Read, Write, Edit, Bash, Glob, Grep |

> **Important:** Sub-agents in Claude Code cannot spawn other sub-agents. Parallel execution is orchestrated by the parent Claude session, which delegates to multiple sub-agents and merges their outputs. The handoff protocol in `ai/agent.md` governs this.

---

#### Cursor — `.cursor/rules/<role>.mdc`

Cursor uses `.mdc` files in `.cursor/rules/` for agent-like rule scoping. These are not true sub-agents but act as role-scoped context injections.

Generate one `.mdc` file per role. Structure:

```
---
description: [One sentence on when this rule activates for this role]
globs: [file patterns this role typically works on, e.g. src/components/**, *.test.ts]
alwaysApply: false
---

# [Role] Agent Context

Your full behavioural rules are in `ai/agents/[role].md`. Read that file first.
Read `ai/agent.md` for orchestration and task classification.
Read only the `docs/` sections listed for your role in `ai/agents/[role].md`.

[Hard constraints from Q3 and Q7, scoped to this role.]
```

Also generate/update `.cursorrules` at the project root:

```
Always read ai/agent.md before starting any task.
Select the appropriate agent from ai/agents/ based on your task type.
Treat docs/prd.md, docs/trd.md, docs/erd.md, and docs/design-system.md as the source of truth.
[Hard constraints from Q3 and Q7.]
Read instruction files before exploring project files. Do not re-read files already read.
```

---

#### GitHub Copilot — `.github/copilot-instructions.md`

Copilot uses a single instruction file. Generate or append:

```markdown
# Copilot Instructions

This project uses a structured AI instruction system.

Always read `ai/agent.md` before starting any task.
Select the appropriate agent role from `ai/agents/` based on task type.
Treat `docs/prd.md`, `docs/trd.md`, `docs/erd.md`, and `docs/design-system.md` as source of truth.

## Role reference
[List each role and its trigger signals, adapted from the Task Classification table in ai/agent.md.]

## Hard constraints
[Hard constraints from Q3 and Q7.]

## Token efficiency
Read instruction files before exploring project files. Do not re-read files already read in this session.
```

---

#### Windsurf — `.windsurfrules`

Generate or append:

```
Always read ai/agent.md before starting any task.
Select the appropriate agent from ai/agents/ based on your task type.
Treat docs/prd.md, docs/trd.md, docs/erd.md, and docs/design-system.md as source of truth. Do not contradict them.
[Hard constraints from Q3 and Q7.]
Read instruction files before exploring project files. Do not re-read files already read in this session.
```

---

#### OpenAI Codex — `codex.md`

Generate or append:

```markdown
# Codex Instructions

Always read `ai/agent.md` before starting any task.
Select the appropriate agent from `ai/agents/` based on your task type.
Treat `docs/prd.md`, `docs/trd.md`, `docs/erd.md`, and `docs/design-system.md` as source of truth.
[Hard constraints from Q3 and Q7.]
Read instruction files before exploring project files. Do not re-read files already read in this session.
```

---

#### Aider — `.aider.conf.yml`

Generate or append a `system_prompt` key:

```yaml
system_prompt: |
  Always read ai/agent.md before starting any task.
  Select the appropriate agent from ai/agents/ based on your task type.
  Treat docs/prd.md, docs/trd.md, docs/erd.md, and docs/design-system.md as source of truth.
  [Hard constraints from Q3 and Q7.]
  Read instruction files before exploring project files. Do not re-read files already read.
```

---

### Files: `ai/playbooks/feature-flow.md`, `bugfix-flow.md`, `refactor-flow.md`

#### `ai/playbooks/feature-flow.md`

Step-by-step guide for shipping a new feature end-to-end:

1. Scope the feature against `docs/prd.md` — confirm it is listed and prioritised
2. Check `docs/erd.md` for any schema changes required
3. Check `docs/design-system.md` for existing components to reuse
4. Check `docs/trd.md` for architectural constraints
5. PM agent: write acceptance criteria (if PM role exists)
6. Designer agent: produce component spec (if Designer role exists and UI is involved)
7. Developer agent: implement using TRD architecture rules and ERD schema
8. QA agent: write tests covering acceptance criteria
9. Review criteria: what must pass before the feature is considered complete (use Q5 answer)
10. Definition of done: final checklist using Q5 answer

#### `ai/playbooks/bugfix-flow.md`

1. Reproduce: steps to confirm the bug is real and repeatable
2. Read `docs/trd.md` to understand expected behaviour
3. Read `docs/erd.md` if the bug may be data-related
4. Isolate: identify the smallest change that causes the bug
5. Fix: implement the minimal change; do not refactor adjacent code
6. Verify: run relevant tests; confirm the bug no longer reproduces
7. Regression check: confirm no other tests broke
8. Document: if the bug revealed a gap in `docs/trd.md` or `docs/erd.md`, update the relevant doc

#### `ai/playbooks/refactor-flow.md`

1. Scope check: read `docs/trd.md` to confirm the refactor aligns with architectural direction
2. Read existing code thoroughly before writing anything
3. Identify what changes and what must not change (public interfaces, API contracts)
4. Make changes in the smallest increments possible
5. Run tests after each increment
6. If types, interfaces, or exports change, update all consumers
7. If the refactor changes the architecture, update `docs/trd.md`
8. Verify no regressions: all tests pass, no type errors, no lint errors

---

### Final file map summary

After generating all files, print this summary:

```
FILES GENERATED:

Canonical docs (stubs — fill these in):
  docs/prd.md
  docs/trd.md
  docs/erd.md
  docs/design-system.md

Master orchestration:
  ai/agent.md

Role definitions (tool-agnostic, source of truth):
  ai/agents/developer.md
  ai/agents/[other roles].md

Playbooks:
  ai/playbooks/feature-flow.md
  ai/playbooks/bugfix-flow.md
  ai/playbooks/refactor-flow.md

Tool-native files (auto-generated per detected tool):
  Claude Code:       .claude/agents/<role>.md   [one per role]
  Cursor:            .cursor/rules/<role>.mdc   [one per role] + .cursorrules
  GitHub Copilot:    .github/copilot-instructions.md
  Windsurf:          .windsurfrules
  OpenAI Codex:      codex.md
  Aider:             .aider.conf.yml

ARCHITECTURE:
  ai/agents/       — canonical role definitions (edit here to change behaviour)
  .claude/agents/  — Claude Code sub-agent wrappers (point to ai/agents/)
  .cursor/rules/   — Cursor role-scoped context (point to ai/agents/)
  All other tools  — single config files (point to ai/agent.md + ai/agents/)
```

---

## Key architecture principle

```
ai/agents/<role>.md          ← single source of truth for role behaviour
        ↑
        │ referenced by
        ├── .claude/agents/<role>.md   (Claude Code native sub-agents)
        ├── .cursor/rules/<role>.mdc   (Cursor role-scoped rules)
        └── all other tool configs     (via ai/agent.md routing)
```

**Never duplicate behavioural rules into tool-specific files.** Tool files are wrappers only. All role logic lives in `ai/agents/`. This means you update behaviour in one place and all tools pick it up automatically.

---

## Output rules

- Every file must be self-contained and immediately usable — no TODOs, no placeholders
- Use the actual stack, folder names, file paths, and tool names discovered in Phase 1
- Use the actual constraints and conventions from Phase 2 answers
- Do not invent roles, tools, or patterns not present in the project or interview
- Do not include anything from any other project — this system is specific to this codebase
- Every agent file must reference `docs/` directly — never copy content out of docs into instruction files
- Claude Code sub-agents: always include valid YAML frontmatter; always scope tools narrowly
- If a tool-specific file already existed (detected in Phase 0), append rather than overwrite
- The canonical docs (`docs/`) are the single source of truth for project knowledge
- `ai/agents/` is the single source of truth for role behaviour
- Tool-native files are routing and permission wrappers only

Begin with Phase 0 now.
