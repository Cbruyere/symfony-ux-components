# tasks/001-card-component.md

## Objective

Créer un composant Symfony UX `Card` générique encapsulant un composant Preline et l'afficher sur la
home page du site pour validation visuelle.

Propriétés requises :

- Une image (un icone svg suffira)
- un titre
- un contenu
- un bouton read more avec une action et un texte personnalisés

Tous les élements sont paramétrables depuis le composant Twig (image, title, content, button-action
et button-text)

## Allowed Files

- src/Twig/Components/Card.php
- templates/components/Card.html.twig
- templates/home/index.html.twig

## Constraints

- Respecter AGENTS.md.
- Respecter ai-rules/frontend.md.
- Respecter ai-rules/coding-rules.md.
- Diff minimal.
- Ne pas ajouter de dépendance.
- Ne pas créer de composant métier spécialisé.

## Expected Result

Le composant doit permettre d'afficher

- une image
- un titre
- un contenu
- un bouton d'action

All four areas must be clearly distinguishable in the UI.

Exemple d'utilisation :

```twig
<twig:Card
    title="Symfony UX Starter Kit"
    image="/images/demo/starter.jpg"
    buttonLabel="En savoir plus"
>
    Ceci est une carte de démonstration utilisant Symfony UX et Preline.
</twig:Card>
```

Le bouton est purement démonstratif.

Aucune logique métier n'est attendue.

Le composant doit rester générique et réutilisable.

## Demonstration

La page :

```text
/home
```

doit afficher une démonstration complète du composant.

La démonstration doit inclure :

- une image ;
- un titre ;
- un contenu ;
- un bouton.

L'objectif est de permettre une validation visuelle immédiate du composant.

## Image Requirement

The image area must occupy the top section of the card.

A simple placeholder image is acceptable.

An icon alone does not satisfy this requirement.

## Validation

- `make phpstan`
- `make lint-twig`
