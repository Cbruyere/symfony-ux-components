# Plan: Navbar Component

## Analyse

- The layout currently hardcodes navigation in `templates/base.html.twig`.
- No reusable `Navbar` Twig component exists.
- Routes already exist for home, account, login, and logout.
- The repository stores functional home tests in `tests/HomeControllerTest.php`, not
  `tests/Functional/HomeControllerTest.php`.

## Impacted Files

- `src/Twig/Components/Navbar.php`
- `templates/components/Navbar.html.twig`
- `templates/base.html.twig`
- `tests/HomeControllerTest.php`

## Steps

1. Add functional assertions for the navbar component behavior.
2. Verify the assertions fail before implementation.
3. Add a generic `Navbar` Twig component with PHP-side brand, items, auth visibility, and active
   state.
4. Replace the hardcoded layout navigation with `<twig:Navbar />`.
5. Run the requested Docker-first validations.
