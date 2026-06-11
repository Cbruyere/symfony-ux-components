# tasks/005-navbar-component.md

## Objective

Créer un composant Symfony UX `Navbar` générique encapsulant un composant Preline.

La configuration de la navbar doit être portée par le composant PHP, pas directement dans Twig.

Le composant doit être intégré au layout principal.

## Allowed Files

- src/Twig/Components/Navbar.php
- templates/components/Navbar.html.twig
- templates/base.html.twig
- tests/Functional/HomeControllerTest.php

## Constraints

- Respecter AGENTS.md.
- Respecter ai-rules/frontend.md.
- Respecter ai-rules/coding-rules.md.
- Utiliser une structure compatible Preline.
- Diff minimal.
- Ne pas ajouter de dépendance.
- Ne pas créer de logique métier.
- Ne pas hardcoder la navigation directement dans Twig.

## Expected PHP Configuration

Le composant PHP doit gérer :

- le logo ;
- le nom de l’application ;
- les liens de navigation ;
- les routes associées ;
- l’état actif ;
- les liens visibles selon l’état d’authentification.

Exemple :

```php
public function getBrand(): array
{
    return [
        'label' => 'Symfony UX Starter',
        'route' => 'app_home',
        'logo' => 'bi:boxes',
    ];
}

public function getItems(): array
{
    return [
        [
            'label' => 'Accueil',
            'route' => 'app_home',
            'icon' => 'bi:house',
        ],
        [
            'label' => 'Compte',
            'route' => 'app_account',
            'icon' => 'bi:person',
        ],
    ];
}
```

## Active Link Requirement

Le composant doit pouvoir déterminer le lien actif.

La logique active/inactive doit être gérée côté PHP.

Exemple :

```php
public function isActive(string $route): bool
{
    return $this->currentRoute === $route;
}
```

Le Twig ne doit pas contenir de logique complexe pour déterminer l’état actif.

## Expected Twig Usage

Le layout doit pouvoir utiliser :

```twig
<twig:Navbar />
```

Le template Navbar doit uniquement rendre :

- brand ;
- logo ;
- liens ;
- état actif ;
- actions utilisateur.

## Expected Rendering

La navbar doit afficher :

- logo ;
- nom de l’application ;
- lien Accueil ;
- lien Compte ;
- lien Login ou Logout selon l’état utilisateur ;
- état actif visible ;
- dark mode ;
- responsive.

## Authentication Awareness

Si l’utilisateur est connecté :

- afficher Compte ;
- afficher Logout.

Si l’utilisateur est anonyme :

- afficher Login ;
- masquer Compte si nécessaire.

Ne pas introduire de logique métier.

## Implementation Rules

Toute configuration évolutive doit être définie dans `Navbar.php`.

Le Twig ne doit pas contenir de tableaux de navigation hardcodés.

Préférer :

```php
getBrand()
getItems()
getUserItems()
getActiveClasses()
getInactiveClasses()
```

Les classes CSS associées aux états doivent être définies dans le composant PHP.

Le template Twig doit rester principalement déclaratif.

Important: The Navbar component is expected to evolve over time.

Design it so new navigation items can be added by modifying Navbar.php only.

Avoid hardcoded navigation structures in Twig.

## Functional Validation

Mettre à jour ou ajouter un test fonctionnel afin de vérifier :

- la navbar est affichée sur `/home` ;
- le lien Accueil est présent ;
- le lien Login est présent pour un utilisateur anonyme ;
- le lien Compte est présent pour un utilisateur authentifié ;
- le lien actif est correctement rendu.

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
- décisions techniques ;
- limites éventuelles.
