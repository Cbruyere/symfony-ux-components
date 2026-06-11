# ADR-006 - Introduce Faker Demo Datasets

## Status

Proposed

## Date

2026-06-10

## Decision

Use Faker to generate realistic datasets for demonstrations and validation.

## Context

Current demo datasets contain only a few rows.

This does not allow realistic validation of:

- pagination
- filtering
- sorting
- performance
- UX behavior

## Alternatives Considered

### Static Demo Data

#### Pros

- simple

#### Cons

- unrealistic
- limited testing value

### Faker Generated Data

#### Pros

- realistic
- scalable
- reusable

#### Cons

- additional dependency

## Decision Outcome

Introduce:

- fakerphp/faker
- demo factories
- realistic datasets

## Consequences

### Positive

- better validation
- realistic demonstrations
- reusable data generation

### Negative

- additional dependency

## Follow-up Decisions

- chart datasets
- KPI datasets
- dashboard datasets
