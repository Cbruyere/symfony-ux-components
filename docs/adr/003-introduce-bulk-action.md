# ADR-003 - Introduce Bulk Actions

## Status

Proposed

## Date

2026-06-10

## Decision

Introduce bulk actions support in DataTable.

## Context

Business applications frequently require operations on multiple rows:

- generate invoices
- delete records
- update statuses
- export selections

Current DataTable actions only support single-row operations.

## Alternatives Considered

### Single Actions Only

#### Pros

- simpler implementation

#### Cons

- poor UX
- repetitive actions

### Bulk Actions

#### Pros

- improved productivity
- better UX
- common business requirement

#### Cons

- selection state management
- additional complexity

## Decision Outcome

Implement:

- row selection
- select all
- bulk actions
- confirmation mechanism

## Consequences

### Positive

- faster workflows
- business-oriented UX

### Negative

- increased component complexity

## Follow-up Decisions

- bulk export
- bulk delete
- bulk update
