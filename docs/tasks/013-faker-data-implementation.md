# Task - DataTable v1.0.3 - Faker Integration & Real Dataset Validation

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

Actuellement, les démonstrations utilisent un jeu de données très réduit :

```php
[
    [
        'id' => 1,
        'name' => 'Admin Demo',
    ],
    [
        'id' => 2,
        'name' => 'User Demo',
    ],
]
```

Ce volume est suffisant pour valider le rendu mais ne permet pas de tester correctement :

- pagination
- tri réel
- filtres complexes
- autoChoices
- responsive sur plusieurs pages
- performances LiveComponent

## Objectif

Ajouter Faker comme dépendance de développement afin de générer des jeux de données réalistes pour
les démonstrations et les validations fonctionnelles.

## Dépendance

Installer :

```bash
composer require --dev fakerphp/faker
```

## Architecture

Créer une factory dédiée aux données de démonstration.

Exemple :

```php
final class UserTableDemoDataFactory
{
    public static function create(
        int $count = 100
    ): array {
    }
}
```

## Jeu de données

Générer des utilisateurs réalistes.

Champs :

- id
- name
- email
- status
- role
- createdAt

### Status

Répartition réaliste :

- Actif
- En attente
- Suspendu
- Archivé

### Rôles

Répartition réaliste :

- Administrateur
- Manager
- Support
- Utilisateur

## Démonstration

Remplacer le dataset statique actuel par :

```php
'rows' => UserTableDemoDataFactory::create(250),
```

## Validation pagination

Valider :

- pagination sur plusieurs pages
- navigation précédente / suivante
- changement direct de page
- conservation du tri
- conservation des filtres
- conservation des multi-filtres

## Validation filtres

Valider :

- autoChoices
- filtres simples
- multi-filtres
- filtres avec peu de résultats
- filtres sans résultat

## Validation tri

Valider :

- tri ascendant
- tri descendant
- tri sur plusieurs pages
- cohérence des résultats

## Validation responsive

Valider :

- mobile
- tablette
- desktop

avec plusieurs pages de données.

## Validation dark mode

Valider :

- pagination
- filtres
- tableau
- actions

sur dataset volumineux.

## Préparation futures fonctionnalités

Ce dataset devra également permettre de valider ultérieurement :

- bulk actions
- export CSV
- export XLSX
- DataSourceInterface
- DoctrineDataSource

sans nécessiter de données supplémentaires.

## Tests

Ajouter des tests couvrant :

- génération des données
- structure des lignes
- volume demandé
- cohérence des valeurs générées

## Validation

```bash
make test
make phpstan
make lint-twig
make npm-build
```

## Critères d'acceptation

- Faker installé en dépendance dev
- Factory dédiée créée
- Dataset de démonstration réaliste
- Pagination validée sur plusieurs pages
- Tri validé sur dataset volumineux
- Filtres validés sur dataset volumineux
- Responsive validé
- Dark mode validé
- Aucune régression fonctionnelle
- Tous les contrôles qualité restent verts

## Note

Cette tâche n'ajoute aucune fonctionnalité métier.

Elle vise à renforcer la validation fonctionnelle de la DataTable dans des conditions proches d'une
utilisation réelle afin de préparer sereinement les futures évolutions du composant.
