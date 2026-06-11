# tasks/003-alert-component.md

## Objective

Créer un composant Symfony UX `Alert` générique encapsulant un composant Preline.

Le composant doit être démontré sur la page `/home`.

La démonstration doit utiliser un flux complet :

Controller → Twig → Symfony UX Component → HTML

## Allowed Files

- src/Twig/Components/Alert.php
- templates/components/Alert.html.twig
- src/Controller/HomeController.php
- templates/home/index.html.twig

## Constraints

- Respecter AGENTS.md.
- Respecter ai-rules/frontend.md.
- Respecter ai-rules/coding-rules.md.
- Respecter les composants Preline officiels.
- Diff minimal.
- Ne pas ajouter de dépendance.

## Expected Variants

- info
- success
- warning
- danger

## Expected API

```twig
<twig:Alert
    variant="success"
    title="Succès"
>
    L'opération a été réalisée avec succès.
</twig:Alert>
```

## Demonstration Rules

Le contrôleur doit fournir les données.

Exemple :

```php
$alerts = [
    [
        'variant' => 'success',
        'title' => 'Succès',
        'message' => 'Utilisateur créé avec succès.',
    ],
    [
        'variant' => 'warning',
        'title' => 'Attention',
        'message' => 'Certaines informations sont incomplètes.',
    ],
];
```

Puis :

```twig
{% for alert in alerts %}
    <twig:Alert
        variant="{{ alert.variant }}"
        title="{{ alert.title }}"
    >
        {{ alert.message }}
    </twig:Alert>
{% endfor %}
```

## Implementation Rules

Les classes CSS associées aux variantes doivent être définies dans le composant PHP.

Éviter les expressions conditionnelles complexes directement dans Twig.

Préférer :

```php
getVariantClasses()
```

ou équivalent.

## Functional Validation

Le flux complet doit être démontré :

Controller → Twig → Component → Render

## Bonus

Si un test fonctionnel existe déjà pour `/home`, l'adapter afin de vérifier la présence des alertes.

## Validation

- make npm-build
- make phpstan
- make lint-twig
- make test
