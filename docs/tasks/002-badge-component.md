# tasks/002-badge-component.md

## Objective

Créer un composant Symfony UX `Badge` générique.

## Allowed Files

- src/Twig/Components/Badge.php
- templates/components/Badge.html.twig

## Constraints

- Respecter AGENTS.md.
- Respecter ai-rules/frontend.md.
- Utiliser Tailwind / Preline.
- Diff minimal.
- Ne pas ajouter de dépendance.

## Expected Variants

- neutral
- info
- success
- warning
- danger

## Expected Result

```twig
<twig:Badge variant="success">
    Actif
</twig:Badge>
```

## Validation

- `make phpstan`
- `make lint-twig`
