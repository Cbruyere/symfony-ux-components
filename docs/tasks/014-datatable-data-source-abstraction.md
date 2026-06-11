# Task - DataTable v1.1.0 - DataSource Abstraction

## Contexte

La DataTable v1.0.2 est désormais pleinement fonctionnelle :

* colonnes dynamiques
* lignes dynamiques
* actions dynamiques
* tri dynamique
* filtres dynamiques
* autoChoices
* multi-filtres
* LiveComponent
* responsive
* dark mode
* couverture de tests complète

Validation systématique :

```bash
make phpstan
make lint
make tests
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

## Environnement de test

Un environnement de test est présent dans le dossier `demo`.

Il s'agit d'une installation Symfony minimale utilisée uniquement pour valider les composants.

Tout ce qui ne concerne pas directement le composant doit se trouver dans `demo` :

* entities
* factories
* stories
* fixtures
* migrations
* repositories
* données de démonstration

Il existe un `HomeController` qui renvoie la page de démonstration.

La validation visuelle devra être effectuée uniquement via :

```text
demo/templates/demo/index.html.twig
```

Aucun élément spécifique à la démonstration ne doit être ajouté dans le package principal.

## Principes d'architecture

### Important

La DataTable ne doit jamais dépendre directement de Doctrine.

L'objectif est d'introduire un mécanisme extensible permettant de supporter plusieurs sources de données sans modifier le composant principal.

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

### Isolation Doctrine

Le composant DataTable ne doit contenir :

* aucun appel direct à Doctrine
* aucun `EntityManagerInterface`
* aucun `Repository`
* aucun `QueryBuilder`
* aucune dépendance forte à une entité

Le support Doctrine doit être isolé dans une datasource dédiée.

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

* lecture des données
* application des filtres
* application du tri
* pagination éventuelle
* retour d'un `DataTableResult`

## Compatibilité ascendante

Le fonctionnement actuel doit rester totalement opérationnel.

Les configurations existantes :

```php
[
    'rows' => [...],
]
```

ne doivent pas être cassées.

### Normalisation

`rows` doit être considéré comme un alias de compatibilité de :

```php
[
    'source' => [...]
]
```

Une phase de transition doit être prévue.

Le composant doit continuer à accepter les deux syntaxes.

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

* récupération du repository
* construction du QueryBuilder
* application des filtres
* application du tri
* récupération des résultats
* transformation vers `DataTableResult`

## Mapping des colonnes Doctrine

Lorsque la source est une entité Doctrine :

```php
[
    'source' => User::class,
]
```

les colonnes doivent correspondre à des propriétés réellement disponibles sur l'entité.

Si une colonne ne peut pas être résolue, une erreur explicite doit être levée.

Exemple :

```text
Column "status" cannot be mapped on entity App\Entity\User.
Use a computed column/value callback or expose a real property.
```

L'objectif est d'éviter les erreurs silencieuses et de faciliter le débogage.

## Hors périmètre

Ne pas implémenter dans cette tâche :

* export CSV
* export XLSX
* import de données
* attributs PHP
* serializer groups
* générateur CRUD
* fonctionnalités de type EasyAdmin

Ces sujets seront traités dans des tâches ultérieures.

## Évolutions futures envisagées

Une fois cette abstraction en place, il sera possible d'ajouter facilement :

```text
ApiDataSource
ElasticDataSource
CsvDataSource
CustomDataSource
```

sans modifier le composant DataTable.

## Validation

Tous les contrôles qualité doivent rester verts :

```bash
make tests
make phpstan
make lint-twig
make npm-build
```

## Critères d'acceptation

* DataTable fonctionnelle avec `rows`
* DataTable fonctionnelle avec `source`
* Resolver opérationnel
* ArrayDataSource implémentée
* DoctrineDataSource implémentée
* Compatibilité complète avec `rows`
* Aucun couplage direct entre DataTable et Doctrine
* Support Doctrine isolé dans une datasource dédiée
* Couverture de tests maintenue
* PHPStan niveau actuel conservé
* Aucune régression fonctionnelle

## Note d'architecture

Cette tâche n'a pas pour objectif d'ajouter une fonctionnalité visible.

Son objectif est de supprimer le dernier couplage fort du composant DataTable afin de garantir son extensibilité future tout en conservant sa philosophie actuelle :

* composant UX autonome
* composant léger
* composant réutilisable
* architecture extensible basée sur des DataSources

Cette abstraction constitue la fondation permettant d'introduire ultérieurement de nouvelles sources de données sans modifier le cœur du composant.
