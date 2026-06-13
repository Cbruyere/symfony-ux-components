# Plan - 016 Plantuml Command Generator

## Analyse

La tache demande une commande Symfony generique nommee `export:diagram:plantuml` pour generer
des diagrammes de classes depuis un fichier PHP ou un dossier de classes PHP.

La commande doit rester technique, reutilisable et sans workflow metier. Le changement doit etre
limite a un outil d'export documentaire utilisable par le bundle et extensible pour d'autres
formats de sortie.

Le chemin `docs/016-plantuml-command-generator.md` indique dans le prompt n'existe pas. Le fichier
de tache lu est `docs/tasks/016-plantuml-command-generator.md`.

## Fichiers impactes

- `src/Command/ExportPlantUmlDiagramCommand.php`
- `src/Diagram/ClassDiagram/ClassDiagramClass.php`
- `src/Diagram/ClassDiagram/ClassDiagramExtractor.php`
- `src/Diagram/ClassDiagram/ClassDiagramRelation.php`
- `src/Diagram/ClassDiagram/PlantUmlClassDiagramRenderer.php`
- `src/Diagram/ClassDiagram/DiagramFileWriter.php`
- `tests/Command/ExportPlantUmlDiagramCommandTest.php`
- `docs/plans/016-plantuml-command-generator.md`
- `docs/retrospectives/016-plantuml-command-generator-retrospective.md`

## Strategie

1. Ajouter des tests de commande couvrant les cas fonctionnels principaux.
2. Verifier que les tests echouent avant implementation.
3. Ajouter un extracteur PHP minimal base sur `token_get_all`.
4. Ajouter un renderer PlantUML natif et un writer de fichiers.
5. Ajouter la commande Symfony avec options `-f`, `-d`, `-o` et `--format`.
6. Generer un fichier par classe et un fichier global quand un dossier est fourni.
7. Creer le dossier de sortie s'il n'existe pas.
8. Retourner une erreur explicite `Dossier de sortie manquant` si `-o` est absent.
9. Ajouter un profil de rendu `detailed` et un profil `architecture`.
10. Separer le modele extrait des renderers de sortie.
11. Supporter plusieurs options `--format` cumulables.
12. Ajouter un rendu PNG autonome si aucune dependance externe n'est disponible.
13. Ajouter un filtre `--component` pour limiter la sortie a une classe, un namespace ou un module.
14. Executer les validations disponibles via Makefile.
15. Produire la retrospective de tache si les validations sont vertes.

## Formats

- `puml` est le format par defaut.
- `plantuml` est accepte comme alias de `puml`.
- `svg` est rendu par le CLI PlantUML local.
- `png` est rendu par le CLI PlantUML local.
- Si le CLI PlantUML est absent, `svg` et `png` echouent clairement et `puml` reste disponible sans
  dependance externe.

## Modes

- `detailed` conserve les membres, signatures et dependances detaillees.
- `architecture` masque les membres et filtre les relations provenant des signatures de methodes
  ainsi que les relations vers les objets de transport.

## Generation ciblee

- `--component=<nom>` conserve les classes dont le nom court, le nom complet ou le namespace
  correspond au composant demande.
- Le diagramme global est filtre sur ce sous-ensemble.

## Hors perimetre

- Installer Java, Graphviz ou PlantUML.
- Ajouter une dependance Composer.
- Modifier les composants UX existants.
- Installer Java, Graphviz ou PlantUML.

## Validation

- `make tests`
- `make phpstan`
- `make lint`
