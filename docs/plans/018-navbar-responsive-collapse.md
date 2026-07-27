# Plan: Navbar Responsive Collapse

## Context

The navbar can show both the full menu and the collapsed mobile menu when the viewport is wide
enough for the full navigation.

## Scope

- Keep a single reusable `Navbar` component.
- Ensure the collapse toggle is only available below the desktop navigation breakpoint.
- Ensure the collapsed panel is forcibly hidden at the desktop navigation breakpoint, even if
  Preline previously opened it.
- Preserve the existing desktop and mobile navigation markup.

## Verification

- `make tests`
- `make phpstan`
- `make lint`
