# Task - DataTable v1.1.0 - DataSource Abstraction

## Contexte

La DataTable v1.0.2 est désormais pleinement fonctionnelle :

- colonnes dynamiques
- lignes dynamiques
- actions dynamiques
- tri dynamique
- filtres dynamiques
- autoChoices
- multi-filtres
- LiveComponent
- responsive
- dark mode
- couverture de tests complète

Validation systématique :

```bash
make test
make phpstan
make lint-twig
make npm-build
```

## Objectif

Supprimer le dernier couplage fort de la DataTable aux données fournies via :

```php
'rows' => [...]
```

Introduire une abstraction de source de données afin de permettre :

```php
'source' => User::class
```

tout en conservant une compatibilité complète avec :

```php
'rows' => [...]
```

## Principes d'architecture

### Important

La DataTable ne doit jamais dépendre directement de Doctrine.

L'objectif est d'introduire un mécanisme extensible permettant de supporter plusieurs sources de
données sans modifier le composant principal.

Architecture cible :

```text
DataTable
    │
    ▼
DataSourceResolver
    │
    ▼
DataSourceInterface
    ├── ArrayDataSource
    ├── DoctrineDataSource
    └── FutureDataSources
```

## Interface

Créer une interface :

```php
interface DataSourceInterface
{
    public function supports(mixed $source): bool;

    public function fetch(
        mixed $source,
        DataTableState $state
    ): DataTableResult;
}
```

## Resolver

Créer un resolver chargé de déterminer automatiquement la datasource adaptée.

```php
final class DataSourceResolver
{
    public function resolve(mixed $source): DataSourceInterface;
}
```

## ArrayDataSource

Première implémentation.

Cette datasource permet de conserver le fonctionnement actuel.

Exemples :

```php
[
    'source' => [
        [
            'name' => 'Admin',
            'email' => 'admin@example.test',
        ],
    ],
]
```

ou

```php
[
    'rows' => [...],
]
```

Responsabilités :

- lecture des données
- application des filtres
- application du tri
- pagination éventuelle
- retour d'un DataTableResult

## DoctrineDataSource

Deuxième implémentation.

Permet de fournir directement une entité Doctrine.

Exemple :

```php
[
    'source' => User::class,
]
```

Responsabilités :

- récupération du repository
- construction du QueryBuilder
- application des filtres
- application du tri
- récupération des résultats
- transformation vers DataTableResult

## Compatibilité ascendante

Le fonctionnement actuel doit rester totalement opérationnel.

Les configurations existantes :

```php
[
    'rows' => [...],
]
```

ne doivent pas être cassées.

Une phase de transition doit être prévue.

## Hors périmètre

Ne pas implémenter dans cette tâche :

- export CSV
- export XLSX
- import de données
- attributs PHP
- serializer groups
- générateur CRUD
- fonctionnalités de type EasyAdmin

Ces sujets seront traités dans des tâches ultérieures.

## Évolutions futures envisagées

Une fois cette abstraction en place, il sera possible d'ajouter facilement :

```php
ApiDataSource
ElasticDataSource
CsvDataSource
CustomDataSource
```

sans modifier le composant DataTable.

## Validation

Tous les contrôles qualité doivent rester verts :

```bash
make test
make phpstan
make lint-twig
make npm-build
```

## Critères d'acceptation

- DataTable fonctionnelle avec `rows`
- DataTable fonctionnelle avec `source`
- Resolver opérationnel
- ArrayDataSource implémentée
- DoctrineDataSource implémentée
- Aucun couplage direct entre DataTable et Doctrine
- Couverture de tests maintenue
- PHPStan niveau actuel conservé
- Aucune régression fonctionnelle

## Note d'architecture

Cette tâche n'a pas pour objectif d'ajouter une fonctionnalité visible.

Son objectif est de supprimer le dernier couplage fort du composant DataTable afin de garantir son
extensibilité future tout en conservant sa philosophie actuelle : un composant UX autonome, léger et
réutilisable.
