# Task: Auto-populate DataTable filter choices

Context

The DataTable LiveComponent is now stable and fully functional.

Current filter configuration requires manually providing choices:

[ 'name' => 'status', 'label' => 'Statut', 'field' => 'status', 'type' => 'select', 'choices' => [
'Actif', 'En attente', ], ]

This is repetitive and not scalable.

Objective

Allow a select filter to automatically generate its choices from the available rows.

Example:

[ 'name' => 'email', 'label' => 'Email', 'field' => 'email', 'type' => 'select', 'autoChoices' =>
true, ]

Expected behavior

The DataTable should:

- inspect all available rows
- read values from the configured field
- generate a distinct list
- remove duplicates
- sort values alphabetically
- expose generated values as filter choices

Example

Rows:

[ ['email' => 'admin@example.test'], ['email' => 'user@example.test'], ['email' =>
'user@example.test'], ]

Generated choices:

[ 'admin@example.test', 'user@example.test', ]

Requirements

- keep DataTable generic
- keep existing manual choices support
- autoChoices is optional
- if choices are provided manually, keep current behavior
- if autoChoices=true, generate choices automatically
- filtering logic must remain unchanged
- sorting logic must remain unchanged
- LiveComponent behavior must remain unchanged
- no page reload
- no custom JavaScript
- no new dependency

Implementation

Preferred behavior:

if autoChoices=true: generate choices from rows

else: use provided choices

Keep logic in DataTable.php.

Twig should remain declarative.

Tests

Add tests for:

- distinct values generation
- duplicate removal
- alphabetical sorting
- autoChoices=true
- manual choices still working
- filtering still works with generated choices

Validation

Run:

- make npm-build
- make phpstan
- make lint-twig
- make test

Final report

Provide:

- modified files
- implementation details
- generated choices behavior
- test results
- remaining limitations
