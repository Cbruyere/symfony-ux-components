# Plan: Navbar Auth Config and Collapse Fix

## Context

After `v1.0.3`, an application can still fail when `user_items.logged_in` is configured without all
leaf fields. The hamburger can also remain visible in consuming applications when responsive
Tailwind classes from vendor templates are not generated or when Preline keeps a collapse state.

## Scope

- Allow incomplete `logged_in` and `logged_out` configuration without throwing a Symfony
  configuration exception.
- Return no user item when the matching user item is incomplete.
- Add a scoped CSS fallback so the collapse toggle and panel are hidden at the desktop breakpoint.
- Add focused test coverage for incomplete user items.

## Verification

- `make tests`
- `make phpstan`
- `make lint`
- `make npm-build`
