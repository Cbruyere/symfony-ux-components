# Symfony UX Starter - Roadmap

## Vision

Créer un starter Symfony moderne basé sur des composants UX réutilisables, autonomes, testés et découplés des implémentations métier.

Le projet ne vise pas à devenir un framework de génération d'administration type EasyAdmin.

Le projet vise à fournir des composants UX robustes permettant de construire rapidement des applications métier modernes.

---

## Version actuelle v1.0.2

### Composants disponibles

- Card
- Badge
- Alert
- Navbar
- Carousel
- Account
- UX Map / Leaflet
- DataTable LiveComponent

### DataTable

Fonctionnalités :

- Colonnes dynamiques
- Lignes dynamiques
- Actions dynamiques
- Tri dynamique
- Filtres dynamiques
- AutoChoices
- Multi-filtres
- LiveComponent
- Responsive
- Dark Mode
- Tests

### Qualité

Validation systématique :

```bash
make test
make phpstan
make lint-twig
make npm-build
```

---

## Prochaine version

## v1.0.3

### Validation réelle de la DataTable

#### Faker Integration

- Installation de Faker en dépendance dev
- Génération de datasets réalistes
- Factory de démonstration

#### Pagination

- Validation sur plusieurs pages
- Validation du maintien des filtres
- Validation du maintien du tri

#### Tri

- Validation sur datasets volumineux

#### Filtres

- Validation autoChoices
- Validation multi-filtres
- Validation cas limites

#### Responsive

- Mobile
- Tablette
- Desktop

#### Dark Mode

- Validation complète

---

## Backlog prioritaire

### DataTable - DataSource Abstraction

### Objectif

Supprimer le dernier couplage fort :

```php
'rows' => [...]
```

### Architecture cible

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

### Fonctionnalités

- DataSourceInterface
- DataSourceResolver
- ArrayDataSource
- DoctrineDataSource

### Utilisation cible

```php
[
    'source' => User::class,
]
```

### Compatibilité

Conserver :

```php
[
    'rows' => [...]
]
```

---

## DataTable - Bulk Actions

### Objectifs

Permettre l'exécution d'actions sur plusieurs lignes sélectionnées.

### Fonctionnalités

- Sélection de lignes
- Sélection globale
- Bulk Actions
- Confirmation
- Intégration LiveComponent

### Cas d'usage

```text
☑ Devis #001
☑ Devis #002
☑ Devis #003

[Générer les factures]
```

---

## DataTable - Renderers

### Objectif

Permettre des rendus spécialisés.

### Renderers envisagés

- BadgeRenderer
- DateRenderer
- DateTimeRenderer
- MoneyRenderer
- BooleanRenderer
- EmailRenderer
- LinkRenderer

### Exemple

```php
[
    'key' => 'status',
    'renderer' => 'badge',
]
```

---

## Moyen terme

### Chart Component

#### Vision

Créer un composant graphique autonome basé sur la même philosophie que la DataTable.

#### Exemple

```php
[
    'type' => 'line',
    'source' => Invoice::class,
]
```

#### Types

- Line
- Bar
- Pie
- Doughnut

#### Architecture

```text
Chart
    │
    ▼
DataSourceResolver
    │
    ▼
DataSourceInterface
```

#### Fonctionnalités

- Responsive
- Dark Mode
- Live updates
- Tests

---

## Stat Card Component

### Objectif

Afficher rapidement des indicateurs métier.

### Exemples

- Chiffre d'affaires
- Nombre de clients
- Nombre de devis
- Nombre de factures

### Exemple

```php
[
    'label' => 'Clients',
    'value' => 245,
]
```

---

## Timeline Component

### Objectif

Afficher des événements métier chronologiques.

### Cas d'usage

- Historique client
- Historique devis
- Historique factures
- Logs applicatifs

---

## Long terme

### Metadata System

#### Objectif

Permettre la configuration automatique des composants.

#### Exemple

```php
#[DataTableColumn(
    label: 'Email',
    sortable: true,
    filterable: true
)]
private string $email;
```

#### Utilisations futures

- DataTable
- Export
- Import
- Statistiques
- API

---

### Export System

#### Formats

- CSV
- XLSX

#### Intégration DataTable

```php
'export' => [
    'csv' => true,
    'xlsx' => true,
]
```

---

## Import System

### Formats

- CSV
- XLSX

### Validation

- Mapping
- Validation métier
- Prévisualisation

---

# CRM v2

## Vision

Reconstruire le CRM à partir du Starter UX.

### Approche

- Capitaliser sur l'expérience du CRM v1
- Réutiliser les composants existants
- Découper systématiquement les tâches
- Conserver une architecture modulaire

### Modules envisagés

- Société
- Clients
- Produits
- Devis
- Factures
- Dashboard
- Documents PDF

---

# Hors périmètre

Le projet ne vise pas à devenir :

- EasyAdmin
- Générateur CRUD
- Générateur de Dashboard
- Générateur de Menus
- Framework d'administration complet

La philosophie reste :

> Construire des composants UX autonomes, réutilisables, testés et découplés du métier.

---

# Qualité

Chaque évolution doit respecter :

```bash
make test
make phpstan
make lint-twig
make npm-build
```

Aucune régression n'est acceptée.
