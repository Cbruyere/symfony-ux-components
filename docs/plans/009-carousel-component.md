# Plan: Carousel Component

## Analyse

- The task file is `docs/tasks/009-carousel-component.md`; it contains the
  `tasks/004-carousel-component.md` objective.
- No existing `Carousel` Twig component exists, so a new generic component is justified.
- Preline is already installed and imported through `assets/app.js` and `assets/styles/app.css`.
- The home functional test currently lives at `tests/HomeControllerTest.php`, not
  `tests/Functional/HomeControllerTest.php`.

## Steps

1. Update the home functional test to assert the Preline carousel markup and demo content.
2. Verify the updated test fails before implementation.
3. Add a generic `Carousel` Symfony UX Twig component using the official Preline carousel selectors.
4. Provide generic demo data from `HomeController` and render `<twig:Carousel>` from the home page.
5. Run the requested Docker-first validations.
