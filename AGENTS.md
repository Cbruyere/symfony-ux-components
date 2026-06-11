# Symfony UX Components

## Purpose

This repository is a reusable Symfony UX components composer package

It provides a complete technical foundation for building modern Symfony applications.

This repository must remain generic and reusable.

Only generic demonstration features are allowed.

---

## Technical Stack

The following stack is mandatory.

### Backend

- PHP 8.4
- Symfony 8
- Symfony Translation

### Frontend

- Symfony UX
- Symfony UX Twig Components
- Symfony UX Live Components
- Symfony UX Autocomplete
- Symfony UX Dropzone
- Symfony UX ChartJS
- Symfony UX Icons
- Symfony UX Map
- Symfony UX Toolkit
- Tailwind CSS
- Preline UI
- Stimulus

### Quality

- PHPStan
- Twig lint
- PHP lint

### Infrastructure

- Docker
- Makefile

---

## Docker First

All project commands must be executed through Docker.

Never ask the user to execute:

- php
- composer
- symfony
- npm

directly on the host machine.

Always prefer Makefile commands.

Examples:

```bash
make phpstan
make lint
```

---

## Architecture

The ux components must remain technical only.

The repository provides:

- reusable UI components
- demonstration pages

The repository must not implement business workflows.

---

## Frontend Rules

Use:

- Tailwind CSS
- Preline UI
- Symfony UX
- Stimulus

Do not use:

- Bootstrap CSS
- Flowbite
- DaisyUI
- React
- Vue
- Angular
- jQuery

Bootstrap Icons are allowed through Symfony UX Icons.

Bootstrap CSS is forbidden.

---

## Symfony UX Components

Reusable UI elements must be implemented as Symfony UX Twig Components.

Pages must not directly depend on Preline markup.

Architecture:

Page
→ Symfony UX Twig Component
→ Preline UI
→ Tailwind CSS

All reusable components should be encapsulated.

Examples:

- Card
- Modal
- Badge
- Table
- Dropdown
- Sidebar
- Navbar
- Alert
- Statistic Widget

---

## Component First Rule

Before creating a new component:

1. Search for an existing component.
2. Reuse it if possible.
3. Extend it if necessary.
4. Create a new component only as a last resort.

One component must have one responsibility.

Avoid:

- UserCard
- ProfileCard
- CustomerCard
- AccountCard

Prefer:

- Card

with configurable properties.

---

## Design System

See:

docs/ai-rules/frontend.md

---

## Docker Rules

See:

docs/ai-rules/docker.md

---

## Pages

The ux components contains:

### Home

Route:

```text
/home
```

Purpose:

Demonstrate available Symfony UX components.

## Completion Checklist

Before considering a task complete:

- PHPStan passes
- Twig lint passes
- PHP lint passes
- No duplicated component created
- Existing design system respected
- Existing layout respected
- Dark mode respected

---

## References

Read before implementing features:

- docs/architecture.md
- docs/technical/docker.md
- docs/ai-rules/frontend.md
- docs/ai-rules/coding.md
- docs/ai-rules/review.md
- docs/ai-rules/workflow.md

## Task Completion

When a task is completed:

1. Verify all acceptance criteria.
2. Execute all project quality gates.
3. Update required documentation if needed.
4. Generate a retrospective report.
5. Generate or update follow-up tasks if identified during implementation.

Retrospective reports must:

- be created in `docs/retrospectives/`
- follow `docs/templates/task-retrospective-template.md`
- follow the rules defined in `docs/ai-rules/generate-task-retrospective.md`

A task is considered completed only when:

```bash
make phpstan
make lint
```

are all successful.
