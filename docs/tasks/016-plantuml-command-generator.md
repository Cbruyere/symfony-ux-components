# Task 016 - Plantuml command generator

## Contexte

Nous souhaitons documenter au fur et à mesure de l'avancement du projet 
avec des fichiers au format plantuml et png

## Objectif

Avoir une commande symfony qui permet de générer les diagrammes de classes au format plantuml

La commande doit pouvoir etre étendue facilement pour générer dans le futur d'autres formats de sortie

nom de la commande `export:diagram:plantuml`

La commande doit pouvoir prendre plusieurs arguments:

-f <chemin du fichier a exporter>
-d <chemin chemin du dossier contenant les classes a exporter (recursive)>
-o <chemin de sortie (dossier ou seront générés les fichiers)> => obligatoire, si non fourni arret et message "Dossier de sortie manquant"

Dans le cas ou seul un dossier est passé en paramètre, il faut généré chaque classe dans un
fichier séparé, et un diagramme complet représentant l'ensemble des classes et leurs relations

Si le dossier de sortie n'existe pas, il faut le créer avant export

IMPORTANT : si le format n'est pas précisé, par défaut on génère un fichier plantuml natif

## Validation

Tous les contrôles qualité doivent rester verts :

```bash
make phpstan
make lint
```

## Tolérance admises

Si le format de sortie PNG n'est pas acceptable, le format SVG peut etre accepté

## Critères d'acceptation

* la commande symfony génère un fichier au format plantuml valide
* la commande symfony génèreun fichier au format png valide
* la commande génére tous les fichiers dans le cas ou un dossier seul est passé en paramètre
* la commande génère un fichier global avec toutes les classes et leurs relations si un dossier est passé en paramètre

## Hors périmètre

Ne pas implémenter dans cette tâche :

* installation java
* dépendances externes sauf si nécessaire pour le fonctionnement de la commande uniquement

## Note d'architecture

Cette tâche n'a pas pour objectif d'ajouter de nouvelles capacités métier.
Son objectif est de pouvoir alimenter la documentation du projet au fur et à mesure
