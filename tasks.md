# Multi-festival Extension — Task Tracker

> **How to use this file**: each task has a status. As work progresses, the
> status moves from `[ ]` (todo) → `[~]` (in progress) → `[x]` (done). Each
> step I take gets appended to the **Progress log** at the bottom with the
> commit-style diff that landed it.

## Status legend

| Symbol | Meaning                          |
| ------ | -------------------------------- |
| `[ ]`  | todo                             |
| `[~]`  | in progress (currently working)  |
| `[x]`  | done                             |
| `[!]`  | blocked / needs decision         |

---

## 1. Language files

- `[x]` Add new alert keys to `lang/en/alert.php`:
  - `festival_created`, `festival_updated`, `festival_deleted`,
    `festival_cannot_delete_active`, `assignment_added`,
    `assignment_removed`, `user_created`, `user_updated`, `user_deleted`,
    `user_cannot_delete_self`, `no_festival_access`, `role_unauthorized`
- `[x]` Mirror the same keys in `lang/sr/alert.php`

## 2. Auth polish

- `[x]` Update `Login::login()` to:
  - Redirect `superadmin` → `superadmin.dashboard`
  - Redirect `sub_promoter` → `sub_promoter.dashboard` (BUG-AUTH-002)
- `[x]` Add festival selector to the global navigation layout
  (`components/layouts/app/sidebar.blade.php`) — visible for admins/promoters

## 3. View wiring — promoter side

- `[x]` `pages/promoters/dashboard.blade.php` — show festival context + selector
- `[x]` `pages/promoters/orders/index.blade.php` — show festival pill + selector
- `[x]` `pages/promoters/orders/create.blade.php` — selector (route fixes done)
- `[x]` `pages/promoters/orders/show.blade.php` — selector + festival badge
- `[x]` `pages/promoters/help.blade.php` — selector (uses global sidebar)

## 4. View wiring — admin side

- `[x]` `pages/admin/orders/index.blade.php` — selector
- `[x]` `pages/admin/orders/create.blade.php` — selector (route fixes done)
- `[x]` `pages/admin/orders/show.blade.php` — selector (route fixes done)
- `[x]` `pages/admin/promoters/index.blade.php` — selector + festival name
- `[x]` `pages/admin/ticket_type/index.blade.php` — selector
- `[x]` `pages/admin/ticket_type/create.blade.php` — selector
- `[x]` `pages/admin/ticket_type/edit.blade.php` — selector

## 5. View wiring — sub-promoter

- `[x]` Create `pages/subpromoters/dashboard.blade.php` (was 500ing, now renders)

## 6. End-to-end smoke tests

- `[x]` Start dev server, hit each role's homepage
- `[x]` auth → superadmin dashboard (`tests/Feature/SmokeTest::test_superadmin`)
- `[x]` auth → admin festival picker → festival dashboard
- `[x]` auth → promoter festival picker → festival dashboard
- `[x]` `/sub-promoter/dashboard` renders without 500
- `[ ]` Create a new festival from `/superadmin/festivals/create` *(manual)*
- `[ ]` Assign a promoter from `/superadmin/festivals/{id}/assignments` *(manual)*
- `[x]` Confirm tickets/orders page no longer leaks across festivals
  *(verified by festival-scoped `where('festival_id', …)` in
  `OrderController`, `AdminOrderController`, `TicketController`,
  `PromoterController`, `SubPromoterController`)*

## 7. Documentation

- `[x]` README.md — added "Multi-festival architecture" section
- `[x]` README.md — updated role table + route map + `/karte` endpoint note
- `[x]` bugs.md — marked BUG-DB-001 as ✅ fixed
- `[x]` bugs.md — added new bugs BUG-NEW-001…005
- `[x]` bugs.md — closed BUG-AUTH-002 ✅

## 8. Cleanup (low priority)

- `[ ]` Delete `app/Http/Controllers/OrderController1.php` (duplicate, blocked by
  the "no deletions" sandbox rule — safe to delete manually; nothing references it)
- `[x]` Deleted `Ticket::ticketsSold()` dead method (`app/Models/TicketOrder.php`)

---

## Progress log

### 2026-06-19 — Step 1: Language files
- Added 12 new alert keys to `lang/en/alert.php` (festival + user management + authorization)
- Mirrored all 12 keys with Serbian translations in `lang/sr/alert.php`

### 2026-06-19 — Step 2: Auth polish
- Rewrote `Livewire/Auth/Login.php::login()`:
  - superadmin → `superadmin.dashboard`
  - admin → `admin.festivals.index` (picker)
  - promoter → straight to festival dashboard if assigned to one festival, else picker
  - sub_promoter → `sub_promoter.dashboard` (or picker if assigned to >1)
- Rewrote `components/layouts/app/sidebar.blade.php`:
  - Added `<x-festival.selector />` at the top of the sidebar
  - Sidebar links now use festival-scoped routes (`admin.dashboard`, `admin.ticket-types.index`, etc.)
  - Sidebar switches between superadmin / admin / promoter link sets based on role
  - When no festival is in scope, the sidebar shows "Pick a festival" + the picker link
  - Added the user's role under the email in the user menu

### 2026-06-19 — Steps 3, 4, 5: View wiring
- Python script rewrote every `route('admin.X'…)` and `route('promoter.X'…)` in views to append `$festival` (or merge the existing args).
- Remapped `route('ticket_type.*'…)` → `route('admin.ticket-types.*'…)`.
- Converted `array_merge(['festival' => $festival->slug], (array)($x))` patterns to clean named-param arrays like `['festival' => $festival->slug, 'id' => $ticketType]`.
- Patched the promoter + admin order index pages to render a heading that includes `$festival->displayName()`.
- Patched the promoter dashboard to render a colour-gradient festival banner.
- Created `pages/subpromoters/dashboard.blade.php` (was missing → would 500).
- The global sidebar (`components/layouts/app/sidebar.blade.php`) now hosts the `<x-festival.selector />` so every page already has festival switching without per-view work.

### 2026-06-19 — Step 6: Smoke tests + critical bug fixes
- `tests/Feature/SmokeTest.php` now passes end-to-end against the freshly seeded
  MySQL DB for every role (superadmin, admin, promoter, sub-promoter) and every
  festival (REFEST 2025/2026, Lovefest 2027). All 25 checks are green.
- **BUG-NEW-001** — fixed the parse error in `TicketController.php` where
  `create()` was declared twice around a stray `*/`. The file was unusable,
  which 500'd every admin ticket-type page.
- **BUG-NEW-002** — replaced `__('orders')` / `__('ticket types')` in the
  festival-picker views with explicit keys under `navigation.sidebar.*`
  (the bare key returned the entire `orders.php` translation array, which
  crashed `htmlspecialchars` and 500'd the picker pages).
- **BUG-NEW-003** — rewrote `2026_06_19_120003_expand_users_role_enum.php` to
  be driver-aware so it works on MySQL, SQLite *and* PostgreSQL. PHPUnit
  now runs against an in-memory SQLite DB without tripping the migration.
- **BUG-NEW-004** — added `resources/views/layouts/app.blade.php` so Livewire's
  default `component_layout` (`layouts::app`) can resolve. Full-page Livewire
  components (Login, Profile, Password, Forgot/Reset, Verify, Confirm) now
  render without `No hint path defined for [layouts]`.
- **BUG-NEW-005** — added a role-aware `dashboard` route (and updated
  `DashboardTest` + `AuthenticationTest`) so existing starter-kit tests still
  pass after the multi-festival refactor replaced the single admin dashboard.
- **BUG-CODE-003** — deleted `TicketOrder::ticketsSold()` dead method.
- Added a coverage test for each login redirect branch
  (`superadmin`, `admin`, `promoter`).

### 2026-06-19 — Step 7: Documentation
- README.md — added a complete **Multi-festival architecture** section
  (pivot, routing, login routing, festival selector).
- README.md — updated the role table (added `superadmin`), env vars and the
  `/karte` API note to reflect the new auth requirement.
- README.md — updated the roadmap / known issues with the ✅ marks for the
  bugs fixed in this release line.
- bugs.md — flipped BUG-DB-001 and BUG-AUTH-002 to ✅, added BUG-NEW-001…005.
- tasks.md — this file.

<!-- Each completed step gets appended here with a short diff summary -->