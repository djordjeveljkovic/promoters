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
- [x] **P-025 FestivalUser self-management (admin can change promoter roles)** ✅ — inline role-changer on promoters index + `PUT /admin/festivals/{festival}/promoter/{id}/role`
- [ ] P-041 Reassign order to another promoter
- [ ] P-042 Bulk promoter invite
- [ ] P-043 Import ticket types from another festival
- [ ] P-048 Bulk user import
- [ ] P-049 Clone festival from template
- [ ] P-051 New mail template types (promoter / admin)
- [ ] P-054 Watch demo / contact form on help
- [ ] P-060 Notifications center
- [ ] P-063 Financial reports

## Recently shipped (since last audit — 2026-06-22)
- [x] **B-001 Admin can create orders** ✅ — `AdminOrderController::create/store` implemented; admin order form posts to `admin.orders.store`.
- [x] **B-002 Admin order detail has rerun/email/print buttons** ✅ — added the same buttons the promoter show page has, plus a stats card.
- [x] **B-003 Festival colour NOT NULL fallback** ✅ — `normaliseColor(null)` no longer forces a NULL into the column.
- [x] **B-005 `downloadQRCodes` checks festival scope** ✅ — added the defensive check.
- [x] **B-006 Order create route pluralised** ✅ — `/orders/create` redirects to `/order/create`.
- [x] **Implicit binding for `{festival}`** ✅ — `Route::bind('festival')` resolves by id or slug; `Festival::__toString` returns the slug so route() helpers never break.
- [x] **Listener auto-queue serialization fix** ✅ — `NotifyUserOfFailedImageGeneration` is now synchronous (was ShouldQueue) to fix the chain-dispatch "Serialization of Closure" error.
- [x] **Bus::chain wrapped in try/catch in both order controllers** ✅ — a job-dispatch failure no longer rolls back a successful order.
- [x] **Serbian pagination + passwords translations** ✅ — `lang/sr/pagination.php`, `lang/sr/passwords.php`.
- [x] **Dead code moved to `_deprecated/`** ✅ — `OrderController1.php` → `docs/_deprecated/`; `SetLocaleMiddleware.php` → `app/Http/Middleware/_deprecated/`.
- [x] **Regression tests** ✅ — `tests/Feature/AdminOrderCreateTest.php`, `tests/Feature/FestivalColorFallbackTest.php`.

## Recently shipped (2026-06-22 — second audit)
- [x] **U-004 Mail template editor surfaces promoter / admin templates** ✅ — added `promoter.new_order`, `admin.daily_summary`, `admin.image_generation_failed` keys.
- [x] **U-005 Promoter avatar upload** ✅ — added file upload to the admin promoter-edit form, controller moves the file to `public/img/promoter_avatars/`.
- [x] **U-007 "Resend last 5" on promoter dashboard** ✅ — added the action button next to "New order".
- [x] **M-005 N+1 in `AdminController::promoters()`** ✅ — replaced per-promoter loops with a single grouped aggregate.
- [x] **M-011 Explicit route binding for `{festival}`** ✅ — slugs resolve everywhere.
- [x] **B-011 Festival `__toString` returns slug** ✅ — route generators that interpolate the model get a stable URL.
- [x] **M-001 Serbian pagination + passwords** ✅ — already done in first pass.

## Previously shipped (since last audit)

- [x] **P-072 Language switcher** ✅ — Livewire component in sidebar + auth pages, session-persisted, `SetLocale` middleware honors `?lang=` / `Accept-Language`.
- [x] **P-069 Global search** ✅ — Livewire component (debounced 250 ms) over festivals/promoters/orders/ticket-types, scoped to accessible festivals, top-bar wired on every page.
- [x] **P-070 Promoter public profile** ✅ — `/p/{id}` route + view, `is_public` toggle + bio field on `users`, 404 for private profiles and non-promoter users.
- [x] **P-027 Promoter commission statement** ✅ — `/admin/festivals/{festival}/promoter/{id}/statement` page with summary stats, per-ticket-type breakdown, order-by-order ledger, and print-friendly CSS for Ctrl+P → PDF.

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
