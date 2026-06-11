# Plan - 014 DataTable DataSource Abstraction

## Analyse

La DataTable filtre et trie actuellement directement la propriete `rows`.
La tache demande d'introduire une couche extensible de sources de donnees tout en conservant la compatibilite avec `rows`.

Le composant principal doit rester independant de Doctrine. Le support Doctrine sera isole dans une datasource dediee.

## Fichiers impactes

- `src/Twig/Components/DataTable.php`
- `src/DataTable/DataTableState.php`
- `src/DataTable/DataTableResult.php`
- `src/DataTable/DataSource/DataSourceInterface.php`
- `src/DataTable/DataSource/DataSourceResolver.php`
- `src/DataTable/DataSource/ArrayDataSource.php`
- `src/DataTable/DataSource/DoctrineDataSource.php`
- `config/services.yaml`
- `tests/DataTable/DataSource/*`
- `tests/Twig/Components/DataTableTest.php`
- `demo/templates/demo/index.html.twig`
- `docs/retrospectives/014-datatable-data-source-abstraction-retrospective.md`

## Strategie

1. Ajouter des tests couvrant `rows`, `source`, le resolver, la datasource tableau et la validation Doctrine.
2. Introduire `DataTableState` et `DataTableResult` comme objets de transport techniques.
3. Deplacer le filtrage et le tri des tableaux dans `ArrayDataSource`.
4. Ajouter `DataSourceResolver` pour selectionner la datasource compatible.
5. Ajouter `DoctrineDataSource` en gardant tout acces Doctrine hors de `DataTable`.
6. Adapter `DataTable` pour utiliser `source`, avec `rows` comme alias de compatibilite.
7. Ajouter une demonstration `source` dans `demo/templates/demo/index.html.twig`.
8. Executer les validations Docker/Makefile disponibles.

## Hors perimetre

- Export CSV/XLSX.
- Import de donnees.
- CRUD ou integration EasyAdmin.
- Nouvelle dependance non necessaire.
