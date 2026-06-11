# tasks/007-datatable-component.md

## Objective

Créer un composant Symfony UX `DataTable` générique permettant d’afficher une liste de données avec
:

- colonnes configurables ;
- lignes dynamiques ;
- boutons d’action par ligne ;
- tri par colonne ;
- filtre de recherche ;
- état vide ;
- pagination simple ;
- rendu responsive ;
- style Preline UI.

Le composant doit être démontré sur la page `/home` avec des données fictives.

La démonstration doit utiliser un flux complet :

Controller → Twig → Symfony UX Component → Preline UI → HTML

## Allowed Files

- src/Twig/Components/DataTable.php
- templates/components/DataTable.html.twig
- src/Controller/HomeController.php
- templates/home/index.html.twig
- tests/Functional/HomeControllerTest.php

## Optional Files

Uniquement si nécessaire :

- src/Twig/Components/TableAction.php
- templates/components/TableAction.html.twig
- src/Twig/Components/TableFilter.php
- templates/components/TableFilter.html.twig

## Constraints

- Respecter AGENTS.md.
- Respecter ai-rules/frontend.md.
- Respecter ai-rules/coding-rules.md.
- Respecter ai-rules/testing.md.
- Utiliser une structure compatible Preline Table.
- Diff minimal.
- Ne pas ajouter de dépendance.
- Ne pas créer de logique métier.
- Ne pas hardcoder les données dans Twig.
- Ne pas créer un composant métier spécialisé.

## Expected PHP Configuration

Le composant doit accepter une configuration générique.

Exemple d’utilisation attendue :

```twig
<twig:DataTable
    title="Utilisateurs de démonstration"
    :columns="userTable.columns"
    :rows="userTable.rows"
    :actions="userTable.actions"
    :filters="userTable.filters"
/>
```

## Expected Controller Data

Les données doivent venir du contrôleur.

Exemple :

```php
$userTable = [
    'columns' => [
        [
            'key' => 'name',
            'label' => 'Nom',
            'sortable' => true,
        ],
        [
            'key' => 'email',
            'label' => 'Email',
            'sortable' => true,
        ],
        [
            'key' => 'status',
            'label' => 'Statut',
            'sortable' => true,
        ],
        [
            'key' => 'role',
            'label' => 'Rôle',
            'sortable' => false,
        ],
    ],
    'rows' => [
        [
            'id' => 1,
            'name' => 'Admin Demo',
            'email' => 'admin@example.test',
            'status' => 'Actif',
            'role' => 'Administrateur',
        ],
        [
            'id' => 2,
            'name' => 'User Demo',
            'email' => 'user@example.test',
            'status' => 'En attente',
            'role' => 'Utilisateur',
        ],
    ],
    'actions' => [
        [
            'label' => 'Voir',
            'route' => 'app_account',
            'icon' => 'bi:eye',
            'variant' => 'secondary',
        ],
        [
            'label' => 'Modifier',
            'route' => 'app_account',
            'icon' => 'bi:pencil',
            'variant' => 'primary',
        ],
    ],
    'filters' => [
        [
            'name' => 'status',
            'label' => 'Statut',
            'type' => 'autocomplete',
            'placeholder' => 'Filtrer par statut',
            'choices' => [
                'Actif',
                'En attente',
                'Inactif',
            ],
        ],
    ],
];

return $this->render('home/index.html.twig', [
    'userTable' => $userTable,
]);
```

## Component Responsibilities

Le composant `DataTable` doit gérer :

- le rendu du titre ;
- le rendu des filtres ;
- le rendu des colonnes ;
- le rendu des lignes ;
- le rendu des boutons d’action ;
- le rendu de l’état vide ;
- le rendu de la pagination ;
- les classes CSS des variantes ;
- les classes CSS des états ;
- les icônes ;
- les attributs accessibles.

## Sorting Requirement

Les colonnes configurées avec :

```php
'sortable' => true
```

doivent afficher un indicateur visuel de tri.

Le composant doit prévoir les paramètres :

```text
sort
direction
```

via query string.

Exemple attendu :

```text
/home?sort=name&direction=asc
```

Le tri peut rester démonstratif sur les données fournies par le contrôleur.

Aucune persistance n’est attendue.

## Filter Requirement

Le composant doit afficher :

- un filtre basé sur les choix fournis.

Les filtres peuvent rester démonstratifs.

Aucune requête AJAX n’est obligatoire pour cette task.

Symfony UX Autocomplete est déjà installé dans le starter.

Si Symfony UX Autocomplete nécessite une intégration plus poussée, prévoir une structure HTML propre
et documenter la limite.

## Row Actions Requirement

Chaque ligne doit afficher les actions configurées.

Les actions doivent être générées depuis la configuration PHP.

Le Twig ne doit pas hardcoder :

- Voir ;
- Modifier ;
- Supprimer.

Exemple attendu :

```twig
{% for action in actions %}
    ...
{% endfor %}
```

## Styling Requirement

Le rendu doit respecter :

- Preline UI ;
- dark mode ;
- palette du starter ;
- responsive design ;
- design SaaS / admin.

## Empty State Requirement

Si `rows` est vide, afficher un état vide clair :

```text
Aucune donnée disponible.
```

avec une icône et un style cohérent.

## Pagination Requirement

Afficher une pagination simple en bas du tableau.

Pour cette task, la pagination peut être visuelle/démonstrative.

Aucune logique serveur complète n’est requise.

## Implementation Rules

Toute logique réutilisable doit être dans `DataTable.php`.

Le Twig doit rester principalement déclaratif.

Préférer des méthodes comme :

```php
getColumns()
getRows()
getActions()
getFilters()
getSortUrl()
getDirectionForColumn()
getActionClasses()
getFilterClasses()
getEmptyState()
```

Éviter les longues conditions Twig.

## Functional Validation

Mettre à jour le test fonctionnel de `/home`.

Tester :

- la page `/home` répond avec succès ;
- le tableau est présent ;
- le titre du tableau est visible ;
- les colonnes configurées sont visibles ;
- les lignes de démonstration sont visibles ;
- les boutons d’action sont visibles ;
- le filtre autocomplete est présent ;
- les liens de tri sont présents ;
- l’état vide est testable si possible.

## Validation

Exécuter :

```bash
make npm-build
make phpstan
make lint-twig
make test
```

## Final Report

À la fin, fournir :

- fichiers modifiés ;
- choix techniques ;
- validations exécutées ;
- limites éventuelles ;
- améliorations futures possibles.
