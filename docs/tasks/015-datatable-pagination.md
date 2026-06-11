# Task 015 - DataTable Pagination Support

## Contexte

La tâche 014 a introduit une abstraction de datasource permettant à la DataTable de fonctionner avec :

```php
[
    'rows' => [...],
]
```

et :

```php
[
    'source' => User::class,
]
```

Les fonctionnalités suivantes sont déjà opérationnelles :

* DataSourceInterface
* DataSourceResolver
* ArrayDataSource
* DoctrineDataSource
* tri
* filtres
* autoChoices
* compatibilité ascendante avec `rows`

La récupération des données est désormais abstraite.

La pagination reste cependant incomplète.

Actuellement, les résultats sont récupérés dans leur intégralité.

## Objectif

Introduire une pagination réelle pour toutes les datasources tout en conservant les fonctionnalités existantes.

La pagination doit fonctionner de manière identique quelle que soit la source utilisée.

## Principes d'architecture

### Important

La DataTable ne doit pas contenir de logique spécifique à Doctrine.

La pagination doit être gérée au niveau des datasources.

Architecture attendue :

```text
DataTable
    │
    ▼
DataSourceResolver
    │
    ▼
DataSourceInterface
    ├── ArrayDataSource
    └── DoctrineDataSource
```

Chaque datasource reste responsable de :

* filtrage
* tri
* pagination

## DataTableState

Étendre ou utiliser `DataTableState` afin de transporter les informations nécessaires à la pagination :

Exemples :

```php
$page;
$perPage;
```

Le composant ne doit pas manipuler directement les détails d'implémentation des datasources.

## DataTableResult

Étendre ou utiliser `DataTableResult` afin de transporter les informations nécessaires à l'affichage de la pagination.

Exemples :

```php
$rows;
$totalRows;
$currentPage;
$perPage;
$totalPages;
```

L'implémentation exacte reste libre tant que le composant dispose des informations nécessaires.

## ArrayDataSource

La pagination doit être appliquée après :

* filtrage
* tri

Responsabilités :

* calcul du nombre total de résultats
* calcul des pages
* découpage des données
* retour d'un DataTableResult cohérent

## DoctrineDataSource

La pagination doit être effectuée côté base de données.

Responsabilités :

* conservation des filtres
* conservation du tri
* calcul du nombre total de résultats
* récupération uniquement des résultats nécessaires à la page courante

La datasource ne doit jamais charger l'intégralité des résultats pour ensuite paginer en mémoire.

L'approche attendue est équivalente à :

```sql
COUNT(...)
```

puis :

```sql
LIMIT ...
OFFSET ...
```

## Compatibilité ascendante

Les comportements existants doivent rester inchangés :

```php
[
    'rows' => [...],
]
```

et :

```php
[
    'source' => [...],
]
```

doivent continuer à fonctionner.

Aucune régression ne doit être introduite sur :

* tri
* filtres
* autoChoices
* actions
* responsive
* dark mode

## Démonstration

Mettre à jour la page :

```text
demo/templates/demo/index.html.twig
```

afin de démontrer la pagination sur :

```php
[
    'source' => User::class,
]
```

avec un volume suffisant de données générées via Foundry.

## Validation

Tous les contrôles qualité doivent rester verts :

```bash
make tests
make phpstan
make lint
make lint-twig
make npm-build
```

## Critères d'acceptation

* Pagination fonctionnelle avec ArrayDataSource
* Pagination fonctionnelle avec DoctrineDataSource
* Tri conservé
* Filtres conservés
* AutoChoices conservés
* Compatibilité `rows` conservée
* Compatibilité `source` conservée
* Nombre total de résultats disponible
* Navigation entre les pages fonctionnelle
* Couverture de tests maintenue
* PHPStan niveau actuel conservé
* Aucune régression fonctionnelle

## Hors périmètre

Ne pas implémenter dans cette tâche :

* choix dynamique du nombre de lignes
* export CSV
* export XLSX
* import de données
* infinite scroll
* virtual scrolling
* chargement AJAX
* persistance des préférences utilisateur
* optimisation avancée des requêtes

Ces sujets seront traités dans des tâches ultérieures.

## Note d'architecture

Cette tâche n'a pas pour objectif d'ajouter de nouvelles capacités métier.

Son objectif est de compléter l'abstraction introduite lors de la tâche 014 afin de rendre la DataTable utilisable sur des volumes de données réels tout en conservant sa philosophie :

* composant autonome
* composant réutilisable
* architecture extensible
* indépendance vis-à-vis de la source de données
