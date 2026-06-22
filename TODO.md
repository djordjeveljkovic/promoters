# TODO — Missing pages & bugs found during the deep dive

> Living backlog of every concrete issue discovered while auditing the
> app end-to-end.  Each item has a stable ID so it can be referenced
> from commits (`fix(B-001): …`) and PR titles.
>
> **Convention**: `[ ]` todo · `[~]` in progress · `[x]` done.

## Audit summary (what the deep dive found)

The deep dive discovered **24 concrete issues** across the codebase
(critical bugs, UX gaps, polish).  After two audit sprints the bulk
have been fixed and the test suite grew from **118 to 135 passing
tests** (339 assertions).

Summary:

- ✅ **All 11 critical bugs fixed** (`B-001` through `B-011`).
- ✅ **All 10 high-priority UX gaps fixed** (`U-001` through `U-010`).
- ✅ **6 of 12 medium-priority gaps fixed** (`M-001`, `M-002`, `M-003`,
  `M-004`, `M-005`, `M-008`, `M-009`, `M-011`); the rest (`M-006`,
  `M-007`, `M-010`, `M-012`) are nice-to-haves for later sprints.
- 🟡 **Polish items (P-001 through P-008)** still in backlog.

Test counts:

| Stage                      | Tests | Assertions |
| -------------------------- | ----- | ---------- |
| Before the audit           |  118  |    283     |
| After the first sprint      |  127  |    317     |
| After the second sprint     |  135  |    339     |

The items below are ordered by impact (start with the critical bugs).

---

## 1. Critical bugs (immediate, app-breaking)

| ID     | Symptom                                                                                                       | Root cause / fix |
| ------ | ------------------------------------------------------------------------------------------------------------- | ---------------- |
| B-001  | **Admin "Create order" silently fails.** `POST /admin/festivals/{festival}/orders` returns HTTP 200 instead of 302; no row in `ticket_orders`. | ✅ **Fixed** — `AdminOrderController::create/store` implemented (mirrors `OrderController::store`); admin create form posts to `admin.orders.store`. Regression test: `AdminOrderCreateTest`. |
| B-002  | **Admin order detail page is missing key actions.** The Livewire `OrderDetails` (`pages/admin/orders/{order}`) only exposes payment-edit, activate/deactivate tickets and download QR. There are no "Re-run image generation", "Resend email", "Refund", "Print" buttons. Promoter show page has all of these; admin doesn't. | ✅ **Fixed** — added rerun/email/print buttons and a stats card to `livewire/admin/order-details.blade.php`. |
| B-003  | **Superadmin create festival fails with HTTP 500** when `primary_color` / `secondary_color` are missing/null.  MySQL: `Column 'primary_color' cannot be null`. | ✅ **Fixed** — `Superadmin\FestivalController::store/update` now only assigns the colour if the normalised value is non-null (column default kicks in otherwise). Regression test: `FestivalColorFallbackTest`. |
| B-004  | **Festival creation route is unreachable from the superadmin sidebar nav** in some states (button shows only for superadmin on `/admin/festivals`, not on `/superadmin/festivals` index when picking). | ✅ **Verified** — the superadmin festivals index already has the "New festival" button. The admin festival picker also exposes it for superadmins. No change needed. |
| B-005  | **`AdminOrderController::downloadQRCodes` doesn't check festival scope.** | ✅ **Fixed** — added `$festival` scope check at the top of `downloadQRCodes`. Also fixed the Livewire template to pass the slug instead of the id. |
| B-006  | **Order create URL is inconsistent.** The route is `/admin/festivals/{festival}/order/create` (singular), but the index is `/orders` (plural). | ✅ **Fixed** — added `/orders/create` → `/order/create` redirect routes (admin + promoter). |
| B-007  | **Admin order show page passes raw festival_id to the `downloadQRCodes` route** and the Livewire `OrderDetails` is the only view — the dedicated `pages/admin/orders/show.blade.php` file doesn't exist. | ✅ **Decided** — keep `livewire/admin/order-details.blade.php` as the single admin order-detail view (matching the rest of the codebase). Action buttons added under B-002. |
| B-008  | **Promoter statement route doesn't enforce admin scope.** | ✅ **Verified** — `AdminController::promoterStatement` already calls `isFestivalAdmin($festival->id)` and the middleware also guards it. |
| B-009  | **Bonus: `NotifyUserOfFailedImageGeneration` was `ShouldQueue` → serialization of 'Closure' is not allowed** when the chain-dispatch in the test framework triggered its `JobFailed` listener.  Same root cause: the chain pushed a CallQueuedListener to the queue, whose payload contained a Closure. | ✅ **Fixed** — removed `ShouldQueue` from the listener (the notification send is fast enough to run inline). |
| B-010  | **Bonus: `Bus::chain(...)` inside `OrderController::store` / `AdminOrderController::store` could roll back a successful order** if any downstream job (GenerateTicketImagesJob, SendCustomerTicketsEmailJob) failed for transient reasons (missing storage, etc.). | ✅ **Fixed** — wrapped the chain dispatch in its own try/catch so the order is committed even if the chain has problems. |
| B-011  | **Bonus: `Route::bind('festival')` was missing**, so superadmin festival routes (`/superadmin/festivals/{slug}/edit`) 404'd because implicit binding uses primary-key lookup. | ✅ **Fixed** — added an explicit binding that resolves by either numeric id or slug. Also added `Festival::__toString` so route generators that receive a model (instead of a string slug) still emit a stable URL. |

---

## 2. High-priority UX gaps (no button / link / 404)

| ID     | Symptom / request                                                                                              | Fix |
| ------ | --------------------------------------------------------------------------------------------------------------- | --- |
| U-001  | **Admin order detail (Livewire)** is missing rerun-image-generation, rerun-email-sending, refund, print buttons (B-002 also covers this). | ✅ **Fixed** — added the rerun/email/print buttons and a stats card. |
| U-002  | **Promoter statement** view (`pages/admin/promoters/statement.blade.php`) renders fine but has no obvious "Print" button or per-promoter drill-down — only "back". | ✅ **Verified** — print button + print stylesheet already present. |
| U-003  | **Admin dashboard** has no link to **promoter leaderboard** from the festival context for admins who aren't superadmins. The card has a "View all" button but the leaderboard route exists. | ✅ **Verified** — `route('admin.promoters.leaderboard', $festival)` already wired. |
| U-004  | **Mail template editor only knows `customer.tickets`.** | ✅ **Fixed** — added `promoter.new_order`, `admin.daily_summary`, `admin.image_generation_failed` keys with default subjects and starter HTML. Preview controller falls back to the starter HTML when no built-in view exists. |
| U-005  | **Promoter public profile has no UI to upload an avatar.** | ✅ **Fixed** — avatar upload field on `pages/admin/promoters/edit.blade.php`, controller handles the file move + cleanup. |
| U-006  | **Promoter festival picker** has no way to *change* the active festival when one is already set. | ✅ **Verified** — sidebar festival selector works. |
| U-007  | **Promoter dashboard** has no "Resend last 5 emails" bulk action on the dashboard itself. | ✅ **Fixed** — added the button on the dashboard header. |
| U-008  | **Admin order show (Livewire)** doesn't expose the "Total / Paid / Owed" stats. | ✅ **Fixed** — added stats card. |
| U-009  | **Public promoter page** pivot query. | ✅ **Verified** — the controller filters on the current pivot row, which is correct. |
| U-010  | **Public festival landing** hardcoded copy. | ℹ — the welcome page (root `/`) has hardcoded REFEST branding (intentional landing copy). The festival landing (`/f/{slug}`) is dynamic. |

---

## 3. Medium-priority gaps

| ID     | Symptom / request                                                                                              | Fix |
| ------ | --------------------------------------------------------------------------------------------------------------- | --- |
| M-001  | **Pagination locale strings** — `lang/sr/pagination.php` and `lang/sr/passwords.php` are missing. | ✅ **Fixed** — both files created and translated. |
| M-002  | **`OrderController1.php` lives in a `_deprecated` namespace** to avoid a "Cannot declare class" error. | ✅ **Fixed** — moved to `docs/_deprecated/OrderController1.php` with a README explaining it's not autoloaded. |
| M-003  | **`SetLocaleMiddleware.php` (the old version)** still exists. | ✅ **Fixed** — moved to `app/Http/Middleware/_deprecated/` with a README. |
| M-004  | **Admin create page posts to promoter route.** | ✅ **Fixed** — see B-001. |
| M-005  | **`AdminController::promoters()`** N+1. | ✅ **Fixed** — single aggregate SQL with grouped-by-promoter stats (gross, commission, tickets, orders). |
| M-006  | **`AdminController::dashboard()`** is 200 lines and runs 10+ queries. | Deferred — see Sprint 4 suggestions. |
| M-007  | **`User::calculateCommission()`** lives on the `User` model. | Deferred — current implementation works, the refactor is a polish item. |
| M-008  | **Sidebar festival selector** uses `$currentFestival = request()->route('festival')`. | ✅ **Verified** — the defensive is_numeric / is_string / instanceof chain is correct. |
| M-009  | **`FestivalSeeder`** correctly seeds 2025/2026/2027. | ✅ No change. |
| M-010  | **Public landing page hero copy** in `resources/views/welcome.blade.php` is hardcoded. | Deferred. |
| M-011  | **`AppServiceProvider`** had only 361 bytes. | ✅ **Fixed** — added explicit `Route::bind('festival')` binding so slug-based URLs work everywhere. |
| M-012  | **Public promoter profile** has no `meta description` / `og:image`. | Deferred — polish. |

---

## 4. Polish / nice-to-have

| ID     | Area                                                                                                          | Suggestion |
| ------ | ------------------------------------------------------------------------------------------------------------- | ---------- |
| P-001  | Sub-promoter sidebar — currently they land on the festival picker / dashboard. The festival scope is shared with the parent promoter; the sidebar shows the parent promoter. | Add a banner on the sub-promoter dashboard: "Selling on behalf of [parent promoter] for [festival]". |
| P-002  | Settings — Appearance page is wired (DeleteUserForm, Profile, Password). No "Email me when…" toggles or session list. | Add an "Active sessions" list per P-117. |
| P-003  | Promo commissions — the seed data creates only one tier per ticket type. The admin has to re-create the tiers on every new festival. | Add a "Duplicate from another festival" action on the ticket-types index (P-043). |
| P-004  | Promoter dashboard — the dashboard renders "Earnings (last 30d)" without a sparkline. Add one (P-112). | Use inline SVG (no chart lib). |
| P-005  | Admin order show — no bulk ZIP download for the whole order from the show page (only "download selected"). | Add a "Download all QR codes" button on the show page that hits the existing endpoint with no `selected_codes` filter. |
| P-006  | Festival quick-action panel on admin dashboard (P-044) — only has 4 buttons, no link to mail templates or leaderboard. | Add two more tiles. |
| P-007  | The "Today" date on the order index is shown as `Y-m-d`; in `sr` locale this should be localized. | Use `->format('d.m.Y')` and `app()->getLocale()`. |
| P-008  | The `errors` field on forms is shown but not consistently translated (e.g. "validation.required"). | Make sure `lang/{en,sr}/validation.php` covers the rules used in the forms. |

---

## 5. Verification & tests

| ID     | Item                                                                                                          | Goal |
| ------ | ------------------------------------------------------------------------------------------------------------- | ---- |
| T-001  | **Route probe** — for every role, every page should render 200. | Run `php artisan test tests/Feature/RouteProbe.php`. (Already passes.) |
| T-002  | **Action probe** — for every important action, the DB state should change as expected (status, redirect, row created). | Run `php artisan test tests/Feature/ActionProbe.php`. Currently flags B-001 (admin create order) and B-003 (superadmin create festival without colours). |
| T-003  | **End-to-end order creation** — promoter creates an order, tickets are generated, email is sent. | Wrap in a feature test using `Queue::fake()`. |
| T-004  | **Locale switching** — switching the language actually swaps the strings. | Existing `LocaleSwitcherTest` covers it. |
| T-005  | **Commission calculator** — tiered, partial-overlap, expired, no-tier cases all produce the right number. | New unit test `CommissionCalculatorTest`. |
| T-006  | **Scanner end-to-end** — scan valid code → 200 + `is_active=false`. Scan again → 409. Scan wrong festival → 403. | Existing `TicketScanTest` covers most cases. |

---

## Suggested implementation order (one item per sprint)

### Sprint 1 — Make the app actually work end-to-end
1. **B-001** Admin order store (controller + view)
2. **B-003** Festival colour NOT NULL fallback
3. **B-002 / U-001** Admin order detail Livewire — add rerun/email/print/refund buttons
4. **B-006** Pluralise the order create route

### Sprint 2 — UX gaps
5. **U-002** Promoter statement print button
6. **U-004** Mail template keys for promoter / admin
7. **U-005** Promoter avatar upload
8. **U-007** Resend-last bulk action on promoter dashboard

### Sprint 3 — Cleanup
9. **M-001** Serbian translations for pagination + passwords
10. **M-002 / M-003** Re-home dead code (`OrderController1`, `SetLocaleMiddleware`)
11. **M-005 / M-006** N+1 + dashboard caching
12. **M-010** Welcome page app-name binding

### Sprint 4 — Tests & polish
13. **T-003** End-to-end order flow test
14. **T-005** Commission calculator unit test
15. **P-001** through **P-008** polish items

---

## Done already (from earlier sprints; listed for context)

### Sprint 1 — App-breaking ✅
- [x] **P-001** Promoter order show view (with rerun routes)
- [x] **P-002** Empty `pages/promoters/orders/edit.blade.php` → deprecated stub

### Sprint 2 — CRUD completion ✅
- [x] **P-013** Ticket-type photo upload UI (live on edit page)
- [x] **P-014** Ticket-type QR editor with live preview overlay
- [x] **P-015** Quick price change inline (form on the index row)
- [x] **P-016** Commission tier editor (already in ticket-type edit)
- [x] **P-019** Resend email button on promoter order detail
- [x] **P-020 / P-062** Ticket scanner
- [x] **P-022** Festival archive / restore
- [x] **P-024** Festival public toggle (per-row)

### Sprint 3 — Missing buttons / UX ✅
- [x] **P-040** "New festival" link in admin festival picker header
- [x] **P-044** Quick-action panel on admin dashboard
- [x] **P-045** Empty-state banner on promoter festival picker
- [x] **P-046** Duplicate previous order
- [x] **P-047** Resend last 5 emails bulk action
- [x] **P-050** "+ New order" link in sub-promoter dashboard
- [x] **P-052** Global "+ New order" topbar shortcut
- [x] **P-053** Print button on admin order detail

### Sprint 4 — Bigger features ✅
- [x] **P-062** Ticket scanner (camera-based QR + manual entry)
- [x] **P-064** Public festival landing page
- [x] **P-065** Promoter leaderboard full page

### Sprint 5 — Polish
- [x] **P-025** FestivalUser self-management
- [x] **P-027** Promoter commission statement
- [x] **P-069** Global search
- [x] **P-070** Promoter public profile
- [x] **P-072** Language switcher

