# ADR-001 - Introduce DataTable LiveComponent

## Status

Accepted

## Date

2026-06-10

## Decision

Introduce a reusable DataTable based on Symfony UX LiveComponent.

## Context

Several project screens required tabular data display.

Standard HTML tables led to:

- duplicated implementations
- inconsistent behaviors
- repetitive frontend code
- difficult maintenance
- no standardized filtering or sorting mechanism

A reusable solution was required.

## Alternatives Considered

### Standard Twig Tables

Pros:

- simple
- no additional abstraction

Cons:

- duplication
- difficult maintenance
- inconsistent UX

### JavaScript DataTable Library

Pros:

- feature rich

Cons:

- additional dependency
- business logic split between PHP and JavaScript
- reduced Symfony UX integration

### Symfony UX LiveComponent

Pros:

- backend-driven
- reactive
- reusable
- testable
- Symfony native

Cons:

- additional complexity
- learning curve

## Decision Outcome

Implement a reusable DataTable component using Symfony UX LiveComponent.

The component must support:

- dynamic columns
- dynamic rows
- dynamic actions
- dynamic filters
- sorting
- responsive design
- dark mode

Configuration must remain PHP-first.

## Consequences

### Positive

- reusable component
- consistent UX
- simplified maintenance
- reduced duplication
- easier future extensions
- backend-centric configuration

### Negative

- slightly increased architecture complexity
- additional component maintenance

## Follow-up Decisions

Future ADRs may address:

- DataSource abstraction
- Bulk actions
- Renderers
- Export system
