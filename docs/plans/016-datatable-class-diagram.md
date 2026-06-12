# Plan - 016 DataTable Class Diagram

## Analyse

La tache demande de documenter la structure technique actuelle de `DataTable` sous forme de
diagramme de classes PlantUML.

Le diagramme doit rester synchronise avec les classes existantes et couvrir le composant Twig UX,
les objets de transport `DataTableState` et `DataTableResult`, ainsi que l'abstraction de sources
de donnees.

## Fichiers impactes

- `docs/technical/diagrams/datatable-class-diagram.puml`
- `src/DataTable/DataSource/ArrayDataSource.php`
- `src/DataTable/DataSource/DoctrineDataSource.php`
- `src/Twig/Components/Navbar.php`
- `docs/retrospectives/016-datatable-class-diagram-retrospective.md`

## Strategie

1. Lire les classes existantes du perimetre DataTable.
2. Representer les classes, interfaces, attributs publics utiles et methodes publiques.
3. Ajouter les relations de dependance vers Symfony, Doctrine et les services internes.
4. Garder le diagramme technique et generique.
5. Corriger les typages PHPStan reveles par la validation, sans modifier le comportement.
6. Executer les validations Makefile requises.

## Hors perimetre

- Modifier le comportement PHP.
- Ajouter une nouvelle fonctionnalite DataTable.
- Generer une image PNG/SVG du diagramme.

## Note de validation

Le code PHP a finalement ete ajuste de facon minimale car `make phpstan` echouait au niveau 10
sur des types trop larges deja presents dans les classes DataTable et Navbar.
