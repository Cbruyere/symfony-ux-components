# Plan: Navbar Active Route Highlight

## Context

The navbar already resolves the current Symfony route and exposes active-state helpers, but the
active visual treatment is too subtle to reliably identify the current navigation entry.

## Scope

- Keep active-route detection inside the existing `Navbar` Twig Component.
- Strengthen desktop and mobile active link classes with a clearer accent treatment.
- Preserve `aria-current="page"` for active links.
- Add focused assertions for active and inactive desktop/mobile class output.

## Verification

- `make tests`
- `make phpstan`
- `make lint`
- `make npm-build`
