# Rule - Generate Task Retrospective

## Purpose

At the end of every completed task, generate a retrospective report.

The retrospective is a mandatory project artifact used to:

- measure task quality
- measure AI collaboration quality
- identify misunderstandings
- identify friction points
- improve future task writing
- improve project documentation
- build long-term project metrics

---

## Trigger

Execute this rule when:

- the task implementation is completed
- all acceptance criteria are satisfied
- quality gates are green

Required quality gates:

```bash
make test
make phpstan
make lint-twig
make npm-build
```

---

## Retrospective Location

Generate a new file in:

```text
docs/retrospectives/
```

Naming convention:

```text
<task-id>-retrospective.md
```

Examples:

```text
011-datatable-component-retrospective.md
012-autofill-filters-retrospective.md
013-faker-data-retrospective.md
```

---

## Template

The retrospective MUST use:

```text
docs/templates/task-retrospective-template.md
```

The template must be completed entirely.

Empty sections are not allowed.

---

## Required Analysis

### Task Understanding

Analyze:

- initial task clarity
- ambiguity level
- missing information
- overall understanding quality

Provide:

- strengths
- weaknesses
- improvement suggestions

---

### Planning Quality

Analyze:

- quality of the generated plan
- plan completeness
- required plan revisions

Determine:

```text
Plan accepted first try:
Yes / No
```

Provide explanation.

---

### Collaboration Analysis

Analyze:

- clarification requests
- implementation iterations
- feedback cycles

Determine:

```text
Number of clarification cycles
```

Provide details.

---

### Implementation Analysis

Analyze:

- technical complexity
- architectural complexity
- implementation quality
- maintainability

Determine:

```text
One-shot implementation:
Yes / No
```

Provide explanation.

---

### Quality Analysis

Verify:

```bash
make test
make phpstan
make lint-twig
make npm-build
```

Record results.

---

## Required Metrics

The retrospective MUST contain:

### Complexity

```text
1 → 5
```

Scale:

| Score | Meaning      |
| ----- | ------------ |
| 1     | Very Simple  |
| 2     | Simple       |
| 3     | Moderate     |
| 4     | Complex      |
| 5     | Very Complex |

---

### Collaboration Fluidity

```text
1 → 10
```

Scale:

| Score | Meaning        |
| ----- | -------------- |
| 1     | Very Difficult |
| 5     | Acceptable     |
| 10    | Excellent      |

---

### Overall Success

```text
1 → 10
```

Scale:

| Score | Meaning    |
| ----- | ---------- |
| 1     | Poor       |
| 5     | Acceptable |
| 10    | Excellent  |

---

## Lessons Learned

Identify:

### What worked well

Examples:

- task quality
- architecture quality
- documentation quality
- examples provided

### What could be improved

Examples:

- missing examples
- missing acceptance criteria
- ambiguous requirements

### What should be reused

Examples:

- architectural pattern
- testing strategy
- implementation approach

---

## Documentation Impact

Determine whether updates are required for:

```text
docs/context.md
docs/roadmap.md
docs/technical/
CHANGELOG.md
```

Provide recommendations.

---

## Technical Debt

Identify:

- introduced technical debt
- postponed improvements
- refactoring opportunities

If none:

```text
No technical debt identified.
```

---

## Follow-up Work

Determine whether new tasks should be created.

Examples:

```text
Create follow-up task
Create refactoring task
Create documentation task
No follow-up required
```

---

## Final Recommendation

Choose one or more:

- Continue roadmap
- Create follow-up task
- Refactor before continuing
- Improve documentation
- Add tests

Provide justification.

---

## Important

Retrospectives are mandatory project artifacts.

A task is not considered fully completed until:

- implementation is finished
- quality gates are green
- retrospective is generated

The retrospective must be objective, factual and actionable.

Avoid generic statements.

Provide concrete observations and measurable feedback whenever possible.
