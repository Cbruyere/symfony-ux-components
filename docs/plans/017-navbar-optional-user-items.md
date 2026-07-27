# Plan: Optional Navbar User Items

## Context

The `Navbar` component currently assumes that both `logged_in` and `logged_out` entries exist in
`userItems`.

Some applications using this reusable package may not have authentication and must be able to omit
those entries.

## Scope

- Update the `Navbar` component contract so `logged_in` and `logged_out` are optional.
- Return no user item when the item matching the current authentication state is not configured.
- Update the bundle configuration so `user_items: []` or partial user item configuration is valid.
- Add focused tests for missing user items.

## Verification

- `make tests`
- `make phpstan`
- `make lint`
