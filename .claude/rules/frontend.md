---
paths:
  - "resources/views/**"
  - "resources/css/**"
  - "resources/js/**"
  - "app/Livewire/**"
---

# Frontend changes

When you change the front end (Blade views, Livewire components' markup, CSS, JS, themes):

1. Before building or restyling, invoke the `frontend-design:frontend-design` skill and follow it.
2. After the change is done, invoke the `design:design-critique` skill on the result and fix the issues it raises, or tell the user which ones you skipped and why.

Skip both for copy-only edits and for changes that do not affect what the user sees.

## No slop UI

This app has its own look. New UI must read as part of it, not as generic AI output.

- Reuse before inventing: existing Blade components, the `ink-*` and palette tokens, and the spacing, radius and type already used on sibling screens. Read two neighbouring views first and match them.
- Colours come from palette CSS variables only. No hardcoded hex, no default Tailwind blue/indigo/purple, no gradient-on-white hero looks.
- Every new surface must work in all palettes (system, night, matsoni, tarkhuna, churchkhela, glass). Check at least night and one light palette.
- Banned defaults: centered card-in-card stacks, emoji as icons, decorative gradients and glows with no purpose, uniform rounded-2xl-plus-shadow on everything, filler sections and badges, lorem-style placeholder copy.
- Hierarchy first: one clear primary action per screen, real type scale contrasts, restrained accent colour. If everything is emphasised, nothing is.
- Mobile-first at 320-430px. Touch targets at least 44px, safe-area insets on fixed bars, and visible loading, empty and error states.
- UI copy is Georgian, short and specific. No generic "Welcome!" or "Get started" filler.
- Do not add UI kits or component libraries, and do not add a feature or flourish that was not asked for.
- No change is done until it has been looked at in a real viewport (screenshot or browser), not just built.
