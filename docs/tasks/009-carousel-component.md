# tasks/004-carousel-component.md

## Objective

Créer un composant Symfony UX `Carousel` générique encapsulant un composant Preline.

Le composant doit être démontré sur la page `/home`.

La démonstration doit utiliser un flux complet :

Controller → Twig → Symfony UX Component → Preline UI → HTML/JS

## Allowed Files

- src/Twig/Components/Carousel.php
- templates/components/Carousel.html.twig
- src/Controller/HomeController.php
- templates/home/index.html.twig
- tests/Functional/HomeControllerTest.php

## Constraints

- Respecter AGENTS.md.
- Respecter ai-rules/frontend.md.
- Respecter ai-rules/coding-rules.md.
- Respecter la structure officielle Preline pour le carousel.
- Diff minimal.
- Ne pas ajouter de dépendance.
- Ne pas créer de logique métier.
- Ne pas réimplémenter un carousel custom en JavaScript.

## Expected Data Flow

Le contrôleur doit fournir les données.

Exemple :

```php
$carouselItems = [
    [
        'title' => 'Symfony UX',
        'description' => 'Construire des interfaces modernes avec Symfony UX.',
        'image' => '/images/demo/carousel-1.jpg',
        'alt' => 'Symfony UX demo image',
    ],
    [
        'title' => 'Preline UI',
        'description' => 'Utiliser des composants Tailwind prêts à l’emploi.',
        'image' => '/images/demo/carousel-2.jpg',
        'alt' => 'Preline UI demo image',
    ],
];
```

Puis la vue doit utiliser :

```twig
<twig:Carousel :items="carouselItems" />
```

## Expected Component API

Le composant doit accepter :

- `items`
- optionnellement `id`
- optionnellement `withControls`
- optionnellement `withIndicators`

Exemple :

```twig
<twig:Carousel
    :items="carouselItems"
    id="home-demo-carousel"
    :withControls="true"
    :withIndicators="true"
/>
```

## Expected Rendering

Le carousel doit afficher :

- une image ;
- un titre ;
- une description ;
- des boutons précédent / suivant ;
- des indicateurs si activés.

## Preline Requirement

Le composant doit utiliser le comportement Preline existant.

Ne pas créer de Stimulus Controller custom sauf nécessité absolue.

Ne pas coder manuellement la logique de slide.

## Functional Validation

Mettre à jour le test fonctionnel de `/home` pour vérifier :

- que la page répond avec succès ;
- que le carousel est présent ;
- qu’au moins deux slides sont rendues ;
- qu’un titre de slide est visible ;
- que les contrôles précédent / suivant sont présents.

## Validation

Exécuter :

```bash
make npm-build
make phpstan
make lint-twig
make test
```

## Final Report

À la fin, fournir :

- fichiers modifiés ;
- validations exécutées ;
- éventuelles limites ou décisions techniques.
