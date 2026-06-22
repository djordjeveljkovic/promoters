# Implementation status — sprint log

> Each item is one TODO entry (see `TODO.md`).  ✅ = done, [ ] = still open.
> This is a living doc — cross off items as you finish them.

## Sprint 1 — App-breaking ✅
- [x] **P-001** Promoter order show view (with rerun routes)
- [x] **P-002** Empty `pages/promoters/orders/edit.blade.php` → deprecated stub

## Sprint 2 — CRUD completion ✅
- [x] **P-013** Ticket-type photo upload UI (live on edit page)
- [x] **P-014** Ticket-type QR editor with live preview overlay
- [x] **P-015** Quick price change inline (form on the index row)
- [x] **P-016** Commission tier editor (already in ticket-type edit)
- [x] **P-019** Resend email button on promoter order detail
- [x] **P-020** + **P-062** Ticket scanner (controller + view + route + migration + 3 tests)
- [x] **P-022** Festival archive / restore
- [x] **P-024** Festival public toggle (per-row)

## Sprint 3 — Missing buttons / UX ✅
- [x] **P-040** "New festival" link in admin festival picker header (superadmins only)
- [x] **P-044** Quick-action panel on admin dashboard (4 quick links)
- [x] **P-045** Empty-state banner on promoter festival picker
- [x] **P-046** Duplicate previous order (with `?from=ID` prefill)
- [x] **P-047** Resend last 5 emails bulk action
- [x] **P-050** "+ New order" link in sub-promoter dashboard
- [x] **P-052** Global "+ New order" topbar shortcut (festival-scoped)
- [x] **P-053** Print button on admin order detail

## Sprint 4 — Bigger features ✅ (where high-value)
- [x] **P-062** Ticket scanner (camera-based QR + manual entry, live result card)
- [x] **P-064** Public festival landing page (`/f/{slug}`, themed with festival colours)
- [x] **P-065** Promoter leaderboard full page (`/admin/.../promoter/leaderboard`)

## Deferred to later sprints
- [ ] P-010/P-011/P-012 Mail template show/version history/preview with real data
- [ ] P-018 Refund / void flow
- [ ] P-021 User profile edit verification (existing edit page is comprehensive)
- [ ] P-025 FestivalUser self-management (admin can change promoter roles)
- [ ] P-041 Reassign order to another promoter
- [ ] P-042 Bulk promoter invite
- [ ] P-043 Import ticket types from another festival
- [ ] P-048 Bulk user import
- [ ] P-049 Clone festival from template
- [ ] P-051 New mail template types (promoter / admin)
- [ ] P-054 Watch demo / contact form on help
- [ ] P-060 Notifications center
- [ ] P-063 Financial reports

## Sprint 5 — Polish (P-100+)
- Deferred.

## Bonus fixes (during the sprint)
- Bumped all ticket-type controller signatures to accept the `{festival}` route param
  first, matching Laravel 12's parameter-order quirk (the same fix went into
  `OrderController@show` and the `TicketController` CRUD).
- Moved the dead `OrderController1.php` to a non-autoloaded `_deprecated`
  namespace so it can't cause a "Cannot declare class" error if anyone ever
  autoloads it.
- Added `festival_id` to the `Ticket` model `$fillable` (was missing —
  the scanner couldn't persist `scanned_at`).
- Added the `tickets.scanned_at` column via a new migration.
