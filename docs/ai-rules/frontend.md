# Frontend Guidelines

## Purpose

This document defines the frontend architecture, design system, component strategy and UI
conventions for the Symfony UX Starter Kit.

All frontend implementations must follow these guidelines.

---

# Frontend Stack

The following frontend stack is mandatory.

## Styling

- Tailwind CSS
- Preline UI

## Interaction

- Stimulus
- Symfony UX

## Components

- Symfony UX Twig Components
- Symfony UX Live Components

## UX Packages

- Symfony UX Autocomplete
- Symfony UX ChartJS
- Symfony UX Dropzone
- Symfony UX Icons
- Symfony UX Map
- Symfony UX Toolkit

---

# Forbidden Technologies

Do not introduce:

- Bootstrap CSS
- Flowbite
- DaisyUI
- React
- Vue
- Angular
- jQuery

Bootstrap Icons are allowed through Symfony UX Icons.

---

# Design Philosophy

The UI should follow modern SaaS and professional application standards.

Inspirations:

- Linear
- Vercel
- Stripe Dashboard
- GitHub
- Notion

The interface should be:

- clean
- minimalist
- responsive
- accessible
- dark-mode friendly

---

# Design System

## Colors

Primary background:

```text
slate-950
```

Surface:

```text
slate-900
```

Primary color:

```text
blue-600
```

Primary hover:

```text
blue-500
```

Primary active:

```text
blue-700
```

Accent:

```text
cyan-500
```

Text:

```text
gray-100
```

Muted text:

```text
gray-400
```

Borders:

```text
slate-800
```

Success:

```text
green-500
```

Warning:

```text
amber-500
```

Danger:

```text
red-500
```

---

# Typography

## Primary Font

```text
Plus Jakarta Sans
```

Used for:

- titles
- forms
- navigation
- cards
- tables
- content

## Monospace Font

```text
JetBrains Mono
```

Used for:

- technical values
- identifiers
- invoice references
- order references
- monetary values
- code snippets

---

# Forbidden Fonts

Do not use:

- Arial
- Verdana
- Tahoma
- Times New Roman

---

# Dark Mode

Dark mode is mandatory.

All components must support dark mode.

No light-only components are allowed.

---

# Responsive Design

All pages must support:

- desktop
- tablet
- mobile

Mobile-first principles should be preferred.

---

# Layout

The application uses a shared layout.

```text
Sidebar
Header
Content Area
```

Structure:

```text
+---------------------+
| Sidebar             |
|                     |
+------+--------------+
       | Header       |
       +--------------+
       | Content      |
       |              |
       +--------------+
```

---

# Sidebar

The sidebar is a reusable Symfony UX Component.

Responsibilities:

- navigation
- active state
- icons
- responsive collapse

Component:

```text
Sidebar
```

---

# Navbar

The navbar is a reusable Symfony UX Component.

Responsibilities:

- branding
- navigation
- account actions
- authentication links

Component:

```text
Navbar
```

---

# Component Strategy

Preline provides visual implementation.

Symfony UX Components provide application abstraction.

Architecture:

```text
Page
↓
Symfony UX Component
↓
Preline
↓
Tailwind
```

---

# Component First Rule

Before creating a new component:

1. Search existing components.
2. Reuse existing components.
3. Extend existing components.
4. Create a new component only if necessary.

---

# Reusable Components

Expected components:

```text
Card
Modal
Alert
Badge
Dropdown
Table
Navbar
Sidebar
StatWidget
Pagination
FormField
```

---

# Component Naming

Good:

```text
Card
Modal
Table
Badge
```

Bad:

```text
UserCard
CustomerCard
ProfileCard
DashboardCard
```

Prefer configurable generic components.

Example:

```twig
<twig:Card title="Profile">
    ...
</twig:Card>
```

---

# Component Compliance

When a task references an existing Preline component:

The generated component must visually resemble the official Preline component.

The objective is adaptation, not reinterpretation.

# Preline Compliance

Preline UI is the visual source of truth.

When implementing a component based on a Preline component:

1. Start from the official Preline markup.
2. Preserve the overall structure.
3. Preserve spacing conventions.
4. Preserve visual hierarchy.
5. Adapt only what is required for Symfony UX integration.

Do not redesign components.

Do not invent alternative layouts when a Preline component already exists.

# Cards

Cards are the primary container component.

Cards should:

- use rounded corners
- have subtle borders
- support headers
- support actions
- support footer areas

Example:

```twig
<twig:Card title="User Information">
    ...
</twig:Card>
```

---

# Tables

Tables must be responsive.

Tables should support:

- empty states
- loading states
- pagination
- sorting preparation

Component:

```text
Table
```

---

# Forms

Forms should use Symfony Forms.

All form fields should be wrapped using reusable components.

Example:

```text
FormField
```

Responsibilities:

- label
- error
- help text
- accessibility

---

# Buttons

Button hierarchy:

Primary:

```text
blue-600
```

Secondary:

```text
slate-700
```

Danger:

```text
red-500
```

Success:

```text
green-500
```

---

# Modals

Modals should be implemented through:

```text
Modal
```

Avoid custom modal implementations.

Use Preline modal behavior.

---

# Alerts

Alert component variants:

```text
success
warning
danger
info
```

Reusable component:

```text
Alert
```

---

# Badges

Badge component variants:

```text
success
warning
danger
info
neutral
```

Reusable component:

```text
Badge
```

---

# Icons

Icons must be provided through:

```text
Symfony UX Icons
```

Preferred icon set:

```text
Bootstrap Icons
```

No SVG duplication in templates.

No inline SVG unless strictly necessary.

---

# Charts

Charts use:

```text
Symfony UX ChartJS
```

Charts are demonstration-only.

Keep examples generic.

---

# Maps

Maps use:

```text
Symfony UX Map
```

Leaflet should be used for demonstrations.

The account page demonstrates user localisation.

---

# Dropzone

Uploads use:

```text
Symfony UX Dropzone
```

The home page should demonstrate upload capabilities.

---

# Autocomplete

Autocomplete uses:

```text
Symfony UX Autocomplete
```

The home page should contain a demonstration component.

---

# Home Page

Purpose:

Demonstrate available UI components.

Examples:

- Card
- Alert
- Badge
- Table
- Chart
- Dropzone
- Autocomplete
- Map
- Live Component

No business data.

---

# Account Page

Purpose:

Demonstrate authenticated profile management.

Displayed information:

- first name
- last name
- email
- avatar
- biography
- roles
- localisation

Use reusable components exclusively.

---

# Accessibility

Requirements:

- keyboard navigation
- labels on all inputs
- visible focus states
- proper contrast ratios

Accessibility is not optional.

---

# Custom CSS

Avoid custom CSS whenever possible.

Prefer:

- Tailwind utilities
- reusable components

Custom CSS should be exceptional.

---

# UI Generation Rule

When creating a new page:

1. Reuse existing layout.
2. Reuse existing components.
3. Respect dark mode.
4. Respect typography.
5. Respect palette.
6. Respect responsiveness.
7. Respect accessibility.

No exceptions.

# Variant Logic

Toute logique liée aux variantes visuelles doit être définie dans le composant PHP.

Cela inclut :

- classes CSS ;
- icônes ;
- labels internes ;
- styles d’état.

Les templates Twig ne doivent pas contenir de longues conditions `if/elseif` pour gérer les
variantes.
