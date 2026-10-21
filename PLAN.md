# რა წამოვიღო — Roadmap to a fully functional app

Mobile-first shared shopping list. Stack: Laravel 13, Livewire 4, Alpine, Tailwind 4, Pest, SQLite (dev).
Design source: the Claude artifact `Hfk3W2K7pbXGLGvv2qZCnV` (index, login, register, invite, lists, list, profile, brand).
Rule for every phase: custom Blade/Livewire only (no Blaze, no UI kits), Georgian UI copy, Pest tests for every action, Pint clean.

## Status

| Phase | Scope | State |
| ----- | ----- | ----- |
| 0 | Foundation: theme tokens, layouts, components, landing | Done |
| 1 | Auth (register, login, logout), invite join flow | Done |
| 2 | Lists index (create, pinned chips), single list (items, priority, qty/unit, claim, bought modes, share, settings) | Done |
| 3 | Live updates | Done (polling) |
| 4 | Profile, themes, account | Done |
| 5 | Saved items | Done |
| 6 | Saved dishes and dish groups | Done |
| 7 | Polls | Done |
| 8 | "Going to the store" notifications | Done (database + email) |
| 9 | Cover photo | Done |
| 10 | Auth completion (password reset, verification) | Done (verification optional) |
| 11 | List and member management gaps | Done |
| 12 | Mobile polish (PWA is out of scope) | Done |
| 13 | Quality: a11y, performance, security, errors | Done |
| 14 | Release prep (deployment is out of scope) | Done |

Phases are ordered by dependency and user value. Do them in order; each ends with green tests and a short manual check on a phone-width viewport.

---

## Phase 3 — Live updates

Goal: when someone adds or checks an item, every other member sees it without refreshing.

- [x] Decision needed: **polling** (`wire:poll.5s` on the list and index pages, no new dependency) vs **Laravel Reverb** websockets (new dependency, needs approval). Default: ship polling first, keep the component structure ready for `#[On('echo-private:...')]`.
- [x] Add `wire:poll.visible` to `Lists\Show` and `Lists\Index`; pause when the tab is hidden.
- [x] Do not clobber an open bottom sheet or in-progress draft on refresh (drafts live in Alpine, so a re-render must keep `x-data` state; verify).
- [x] "Someone is adding items" indicator from the landing mock is out of scope unless Reverb is chosen.
- [x] Tests: component re-render shows items added by another user.

## Phase 4 — Profile, themes, account

Design: `profile.html`. Route `/settings/profile` (Livewire `Settings\Profile`).

- [x] Header with back link, avatar, name, email, logout.
- [x] Theme picker (system, night, matsoni, tarkhuna, churchkhela) with mini previews in each palette's colours. Persist to `users.theme` and `localStorage` (`rwTheme.set`); apply instantly.
- [x] Edit name and email (email change re-validates uniqueness).
- [x] Change password (current password required).
- [x] Delete account (confirmation, requires password; transfers or deletes owned lists: decide rule, default = delete lists the user owns that have no other members, otherwise block with a message asking to transfer ownership).
- [x] Link from the avatar menu on `/lists` and `/lists/{list}`; add the "პროფილი და შენახული" and "თემა" menu items from the design.
- [x] Tests: each action, validation, theme persisted and rendered as `data-palette` seed.

## Phase 5 — Saved items

Tables exist (`saved_items`). Profile tab "შენახული ნივთები".

- [x] Profile tab: list saved items, add by name (+ optional quantity/unit), delete.
- [x] Item edit sheet in a list: "შენახვა" toggle that saves/unsaves the item for the current user (matches by normalized name). Show the bookmark icon on rows that are saved.
- [x] Add-menu "შენახულიდან" sheet in a list: multi-select saved items, add all at once.
- [x] Quick-add suggestions: while typing, show matching saved items as chips (tap = add immediately).
- [x] Pinned chips on `/lists`: define the rule (saved items remembered from a specific list; tap re-adds to that list). Decide whether `shopping_list_id` stays or pinned = all saved items with a target list picker.
- [x] Tests: save/unsave, add from saved, normalization (case, trim), a user only sees their own.

## Phase 6 — Saved dishes and dish groups

Needs migrations: `dishes` (user_id, name), `dish_ingredients` (dish_id, name, quantity, unit), and `list_items.group_name` (nullable, the dish a row came from).

- [x] Profile tab "შენახული კერძები": create dish with ingredient rows (name, qty, unit), edit, delete.
- [x] In a list: add menu "კერძი" sheet: name + ingredients, optional "remember this dish" toggle.
- [x] "შენახულიდან" sheet tab "კერძები": pick a dish, tick which ingredients to add.
- [x] List page: dish sections (title, progress, "toggle all" check/uncheck, remove dish). Dish ingredients have no priority (design hides it).
- [x] Tests: create/edit dish, add ingredients to list, toggle group, delete group.

## Phase 7 — Polls

Migrations: `polls` (shopping_list_id, created_by, question), `poll_options` (poll_id, label), `poll_votes` (poll_id, poll_option_id, user_id, unique poll_id+user_id).

- [x] Add menu "გამოკითხვა" sheet: question + 2..6 options.
- [x] Poll card on the list: collapsed summary / expanded options with percentages, voter avatars, one vote per user (changing vote moves it).
- [x] Creator can delete the poll; any member can promote the winner into the list as an item (poll then closes or is deleted: decide, default = delete after promoting).
- [x] Tests: vote, change vote, percentages, only members vote, promote winner, ties.

## Phase 8 — "Going to the store" notifications

Replaces the evening phone call. Recipients = other list members.

- [x] Sheet from the banner "მაღაზიაში მიდიხარ?": choose recipients, editable text (max 160) with presets, "remember this text" (`users.going_message`).
- [x] Banner switches to the green "sent" state with "დავასრულე" to end the trip (store trip state on the list: `shopping_list_trips` or a nullable `shopping_by` + `shopping_started_at` on `shopping_lists`).
- [x] Delivery channels. Decision needed:
  - In-app (database notifications + unread dot/inbox) — no new dependency.
  - Email fallback via a queued mail notification.
  - SMS only if a provider is chosen.
  Default plan: database + email. Web Push is out of scope (no PWA).
  Also shipped: `ListActivityNotification` (database, instant) for a new member, an urgent item and a new poll. Each user can switch these off on the profile; the store-trip notice always arrives.
- [x] Tests: `Notification::fake()`, recipients exclude sender, non-members rejected, message length validation.

## Phase 9 — Cover photo

- [x] `shopping_lists.cover_path` (storage disk `public`), Livewire `WithFileUploads`, max 2 MB, image mime check, resize/compress server-side (GD) to a sensible width.
- [x] Owner-only: add/replace/remove from list settings; show the banner on the list and the thumbnail on `/lists` rows.
- [x] Delete the file when replaced or when the list is deleted.
- [x] `php artisan storage:link` documented in the release checklist.
- [x] Tests: upload, validation (type, size), removal cleans the file (`Storage::fake`).

## Phase 10 — Auth completion

- [x] Forgot/reset password flow (Georgian emails, rate limited). The login page already links "დაგავიწყდა პაროლი?".
- [x] Email verification: decide whether to require it (default = optional, nag banner only).
- [x] Mail setup: `MAIL_*` configured through `.env`; queue worker needed for queued mail.
- [x] Registration guard: rate limiting on register and invite join.
- [x] Tests: reset request/complete, expired token, throttling.

## Phase 11 — List and member management gaps

- [x] Owner can remove a member (their claims are released) and transfer ownership.
- [x] Regenerate the invite link (invalidates the old token).
- [x] Invite page for an already-joined user ("უკვე წევრი ხარ" -> open list).
- [x] Rename and leave flows verified end to end; leaving with claimed items releases them (done) — add UI feedback toast.
- [x] "ადრე ნაყიდი" suggestions in quick add (bought items matching the typed name can be re-opened in one tap).
- [x] Optimistic UI for toggle/claim so taps feel instant on slow mobile networks (Alpine state first, then `$wire` call, roll back on error).
- [x] Empty/error states for every screen (no lists, no items, 403, 404, 419 session expired).

## Phase 12 — Mobile polish (PWA is out of scope)

- [x] Self-host the two fonts (remove Google Fonts request) for privacy and offline.
- [x] Safe-area handling (`env(safe-area-inset-*)` on every fixed bar, `100dvh`, `viewport-fit=cover`) implemented. Not yet checked on a physical iPhone or at 320px in a real browser.
- [x] Touch targets >= 44px, `enterkeyhint`, correct `inputmode` on all inputs.

## Phase 13 — Quality: accessibility, performance, security

- [x] Accessibility pass (WCAG 2.1 AA) on all five palettes: contrast of text, focus order inside bottom sheets (focus trap, restore focus on close), `aria-live` for toasts, reduced motion.
- [x] Contrast check for the two accent text colours (`primary-fg`, `urgent-fg`) in every palette.
- [x] N+1 audit with `Model::preventLazyLoading()` in non-production; index composite DB indexes (`list_items(shopping_list_id, bought_at)`, `shopping_list_user`).
- [x] Authorization audit: every Livewire action calls the policy (already true for `Show`; verify all new components). Add a test per action for non-member.
- [x] Input hardening: length caps, rate limits on add item/vote/notify, CSRF/session on Livewire (default), security headers.
- [x] Georgian pluralization/forms helper for counts ("1 ნივთი", etc.) and a single place for UI strings (lang files) if localization is wanted.
- [x] Larastan level check (`phpstan.neon` exists) and fix findings.

## Phase 14 — Release prep (deployment is out of scope)

- [x] Seeders: `DemoFamilySeeder` (3 users, 2 lists, items, dish group, poll, saved items, dish). `php artisan db:seed` runs it outside production only. Logins: dato@, nino@, gio@example.com, password `password`.
- [x] Feature tests green plus an end-to-end smoke journey (`SmokeJourneyTest`: register, create list, add item, invite, second user joins, claims and checks the item, first user sees it). Written as a Pest feature test; a real browser test (Pest browser or Dusk) needs a new dependency, which needs approval.
- [x] Backups: `php artisan app:backup-database` (SQLite `VACUUM INTO`, 14 daily copies, scheduled 03:00). Skips non-SQLite connections; use the provider's backups there.
- [x] Privacy page (`/privacy`) and terms (`/terms`): accounts, invite links, data deletion, offline copy. Linked from the landing page and the register form. Review the wording before launch; it is plain-language copy, not legal advice.

## Definition of "100% functional"

Every screen in the design exists and works on a phone: landing, register, login, invite, lists, list (items, groups, polls, dishes, saved, share, settings, going-to-store), profile (themes, saved items, saved dishes, account). Every action is authorized, validated, covered by a Pest test, and works across all five palettes. Members see each other's changes live. Password reset works. Full test suite and Pint pass.

## Open decisions (answer when the phase starts)

1. Live updates: polling or Reverb? (Phase 3) Answered with the default: polling (5 s on a list, 10 s on /lists).
2. Notification channels: in-app + email now, Web Push later? SMS provider? (Phase 8) Answered with the default: in-app + email. Web Push is out of scope; SMS waits for a provider.
3. Account deletion rule for owned lists with other members. (Phase 4) Answered with the default: solo lists are deleted, shared lists block deletion until ownership is transferred or the list is deleted.
4. Is email verification required? (Phase 10) Answered with the default: optional, banner only.

## Backlog (not required for 100%)

- Offline writes with sync queue.
- Item history/analytics ("what we buy most").
- Recurring items on a schedule.
- Barcode scan, store aisle ordering.
- Multiple languages (Georgian only for now).
