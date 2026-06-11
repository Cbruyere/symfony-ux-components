# Plan - 015 DataTable Pagination

## Analyse

La DataTable affiche actuellement un faux bloc de pagination statique et les datasources retournent toutes les lignes disponibles.
La tâche demande une pagination réelle, appliquée dans chaque datasource après filtrage et tri, avec conservation de la compatibilité `rows` et `source`.

Le composant peut transporter l'état de pagination et afficher les liens, mais il ne doit pas contenir de logique spécifique à Doctrine.

## Fichiers impactés

- `src/DataTable/DataTableState.php`
- `src/DataTable/DataTableResult.php`
- `src/DataTable/DataSource/ArrayDataSource.php`
- `src/DataTable/DataSource/DoctrineDataSource.php`
- `src/Twig/Components/DataTable.php`
- `templates/components/DataTable.html.twig`
- `tests/DataTable/DataSource/ArrayDataSourceTest.php`
- `tests/DataTable/DataSource/DoctrineDataSourceTest.php`
- `tests/Twig/Components/DataTableTest.php`
- `demo/templates/demo/index.html.twig`
- `docs/retrospectives/015-datatable-pagination-retrospective.md`

## Stratégie

1. Ajouter des tests de pagination sur `ArrayDataSource`.
2. Ajouter des tests de métadonnées et URLs de pagination côté `DataTable`.
3. Étendre `DataTableState` avec `page`, `perPage` et une option pour désactiver la pagination quand nécessaire.
4. Étendre `DataTableResult` avec `totalItems`, `currentPage`, `perPage` et `totalPages`.
5. Appliquer la pagination dans `ArrayDataSource` après filtres et tri.
6. Appliquer la pagination dans `DoctrineDataSource` via `COUNT`, `setFirstResult` et `setMaxResults`.
7. Remplacer le faux pager Twig par une navigation basée sur le résultat réel.
8. Configurer la démonstration `User::class` avec un volume par page explicite.
9. Exécuter les validations Docker/Makefile.

## Hors périmètre

- Choix dynamique du nombre de lignes.
- Infinite scroll.
- Chargement AJAX dédié.
- Optimisations avancées de requêtes.
