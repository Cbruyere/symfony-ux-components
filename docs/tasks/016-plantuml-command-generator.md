# Task 016 - PlantUML Documentation Generator

## Contexte

Nous souhaitons documenter automatiquement le projet au fur et à mesure de son évolution.

La documentation technique doit être générée directement à partir du code source afin de garantir sa cohérence avec l'architecture réelle.

Les diagrammes doivent être exploitables dans :

* Obsidian
* GitHub
* Documentation technique
* Présentations techniques
* Revues d'architecture

## Objectif

Mettre à disposition une commande Symfony permettant de générer des diagrammes UML à partir du code source PHP.

La commande doit être conçue de manière extensible afin de supporter :

* plusieurs formats de sortie
* plusieurs modes de rendu
* plusieurs niveaux d'abstraction

Nom de la commande :

```bash
export:diagram:plantuml
```

## Fonctionnalités

### Sources

La commande doit permettre :

```bash
-f <fichier PHP>
-d <dossier PHP (récursif)>
```

Les deux options doivent pouvoir être utilisées indépendamment.

### Sortie

```bash
-o <dossier de sortie>
```

Option obligatoire.

Si le dossier n'existe pas :

* le créer automatiquement

Si l'option est absente :

```txt
Dossier de sortie manquant
```

et arrêt de la commande.

## Formats de sortie

La commande doit supporter :

```bash
--format=puml
--format=svg
--format=png
```

Formats cumulables :

```bash
--format=puml --format=svg
```

Par défaut :

```bash
--format=puml
```

### Tolérance

Les formats `svg` et `png` doivent être rendus par un backend PlantUML réel.

Si le CLI PlantUML local n'est pas disponible, la commande doit échouer explicitement pour ces
formats et recommander l'utilisation de `--format=puml` ou l'installation de PlantUML.

Si PNG n'est pas disponible ou ne produit pas un résultat acceptable :

```bash
--format=svg
```

est considéré comme valide.

## Modes de rendu

### Detailed

Mode par défaut.

Affiche :

* classes
* interfaces
* traits
* enums
* propriétés
* méthodes
* paramètres
* types de retour
* dépendances détaillées

Exemple :

```txt
DataSourceInterface
 ├ supports(source: mixed): bool
 └ fetch(source: mixed, state: DataTableState): DataTableResult
```

### Architecture

Mode simplifié destiné à la documentation.

Affiche uniquement :

* classes
* interfaces
* traits
* enums
* héritages
* implémentations
* stéréotypes (`readonly`, `final`, `enum`, etc.)

Ne doit pas afficher :

* méthodes
* paramètres
* types de retour
* dépendances générées uniquement par les signatures

Objectif :

```txt
Compréhension de l'architecture en moins de 30 secondes.
```

Exemple :

```txt
DataSourceInterface
       ▲
       │
 ┌─────┴─────────────┐
 │                   │
ArrayDataSource   DoctrineDataSource
```

## Génération ciblée

La commande doit permettre de générer uniquement une partie du projet.

Exemples :

```bash
--component=DataTable
--component=Card
--component=Navbar
```

Le composant peut correspondre :

* à une classe
* à un namespace
* à un sous-module

La sortie doit contenir uniquement les éléments liés au composant demandé.

## Génération globale

Lorsqu'un dossier est fourni :

```bash
-d src
```

La commande doit produire :

### Diagrammes individuels

Un fichier par classe.

Exemple :

```txt
DataTable.puml
DataTableResult.puml
DoctrineDataSource.puml
```

### Diagramme global

Un diagramme regroupant :

* toutes les classes
* toutes les interfaces
* leurs relations

Exemple :

```txt
classes.puml
```

## Extensibilité

La conception doit permettre l'ajout futur de nouveaux formats :

```txt
Mermaid
Graphviz
C4
Architecture Overview
```

L'extraction du modèle doit être indépendante du moteur de rendu.

Architecture recommandée :

```txt
Extractor
      ↓
Diagram Model
      ↓
Renderer
      ├ PlantUML
      ├ SVG
      ├ PNG
      └ Future Renderers
```

## Validation

Tous les contrôles qualité doivent rester verts :

```bash
make phpstan
make lint
```

## Critères d'acceptation

* génération PlantUML valide
* génération SVG valide
* génération PNG valide si disponible
* génération récursive d'un dossier
* génération d'un diagramme global
* support du mode `detailed`
* support du mode `architecture`
* support du filtrage par composant
* création automatique du dossier de sortie
* architecture extensible pour de futurs renderers

## Hors périmètre

Ne pas implémenter dans cette tâche :

* installation Java
* intégration Obsidian
* génération Mermaid
* génération C4
* génération de diagrammes de séquence

## Note d'architecture

Cette tâche n'a pas pour objectif d'ajouter de nouvelles capacités métier.

Son objectif est de construire une chaîne de génération de documentation technique automatisée à partir du code source afin d'accompagner durablement le projet.
