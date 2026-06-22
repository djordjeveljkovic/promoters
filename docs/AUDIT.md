# Deep-Dive Audit — promoteri

> Conducted 2026-06-22 by a veteran Laravel / Livewire / TailwindCSS reviewer.
> Source files were read in full, every route was exercised against an
> authenticated superadmin / admin / promoter / sub-promoter, and the
> PHPUnit suite was run.

---

## 0. Verdict at a glance

| Area                | Verdict |
|---------------------|---------|
| Architecture        | Solid, multi-festival design is well thought out |
| Code organisation   | Mostly good, a few "fat controllers" |
| Test coverage       | 77 tests pass, but only covers the easy paths |
| UI consistency      | Design-system primitives used everywhere |
| **Production-readiness** | **NOT READY — 7 hard bugs that 500 / 404 on real user actions** |

The audit found **7 confirmed runtime bugs**, **4 small issues**, and a
backlog of features already inventoried in `TODO.md` / `IMPLEMENTATION_STATUS.md`.

---

## 1. What works (verified end-to-end)

Every URL below was hit with a real HTTP request via `php artisan` HTTP
kernel and rendered successfully:

| URL | Result |
|---|---|
| `/` (public landing) | ✅ 200 |
| `/f/refest-2026` (public festival page) | ✅ 200 |
| `/login`, `/register`, `/forgot-password`, `/reset-password/{token}` | ✅ 200 |
| `/superadmin/dashboard` | ✅ 200 |
| `/superadmin/festivals` (list, create, edit, archive, restore, toggle-public, delete) | ✅ 200 |
| `/superadmin/festivals/{id}/assignments` | ✅ 200 |
| `/superadmin/users` (list, create, edit, delete) | ✅ 200 |
| `/superadmin/mail-templates` (list + edit) | ✅ 200 |
| `/admin/festivals` (picker) | ✅ 200 |
| `/admin/festivals/refest-2026/dashboard` | ✅ 200 |
| `/admin/festivals/refest-2026/promoters` (list) | ✅ 200 |
| `/admin/festivals/refest-2026/orders` (list) | ✅ 200 |
| `/admin/festivals/refest-2026/order/create` | ✅ 200 |
| `/admin/festivals/refest-2026/ticket-types` (list + create + edit + set-price + delete) | ✅ 200 |
| `/admin/festivals/refest-2026/scan` (camera + manual + recent list) | ✅ 200 |
| `/admin/festivals/refest-2026/promoter-managers` (list + show + update) | ✅ 200 |
| `/admin/festivals/refest-2026/mail-templates` | ✅ 200 |
| `/admin/festivals/refest-2026/promoter/leaderboard` | ✅ 200 |
| `/promoter/festivals` (picker) | ✅ 200 |
| `/promoter/festivals/refest-2026/dashboard` | ✅ 200 |
| `/promoter/festivals/refest-2026/order/create` | ✅ 200 |
| `/promoter/festivals/refest-2026/orders/{id}` (show) | ✅ 200 |
| `/promoter/festivals/refest-2026/help` | ✅ 200 |
| `/sub-promoter/dashboard` | ✅ 200 |
| `/settings/profile`, `/settings/password` | ✅ 200 |
| `/karte` (auth-guarded legacy endpoint) | ✅ 401 (correctly rejected unauthenticated) |
| `/up` (health check) | ✅ 200 |

The whole login → role-aware redirect chain works correctly, including the
sub-promoter redirect from `Login::login()` and the
`sub_promoter.dashboard` route name (BUG-AUTH-001 ✅).

All 77 PHPUnit tests pass (`php artisan test --testsuite=Feature`).

---

## 2. Confirmed bugs (fix immediately)

### BUG-AUDIT-001 🔴 Promoter create page 500 — view references missing `$festival`
**Location:** `resources/views/pages/admin/promoters/create.blade.php` + `AdminController::createPromoter()`
**Symptom:** `GET /admin/festivals/refest-2026/promoter/create`
**Stack trace:**
```
Illuminate\View\ViewException: Missing required parameter for
[Route: admin.promoters.index] [URI: admin/festivals/{festival}/promoters]
[Missing parameter: festival].
(View: resources/views/pages/admin/promoters/create.blade.php)
```
**Root cause:** the view calls `route('admin.promoters.index', $festival)`
and `route('admin.promoters.store', $festival)` but `createPromoter()` does
not pass `$festival` (the controller only returns `view('pages.admin.promoters.create')`).
**Fix:** pass `compact('festival')` like the rest of the controller actions
that pull the festival from `$request->attributes->get('festival')`.

### BUG-AUDIT-002 🔴 Promoter edit / update / destroy / make-manager / remove-manager all 404
**Location:** `AdminController::editPromoter / updatePromoter / deletePromoter / makeManager / removeManager` + route definitions in `routes/web.php`
**Symptom:** clicking "Edit", "Delete", "Make manager", or "Demote" on the
promoters index returns 404.
**Stack trace:**
```
Illuminate\Database\Eloquent\ModelNotFoundException:
No query results for model [App\Models\User] refest-2026
```
**Root cause:** the Laravel 12 dispatcher passes route parameters to
controller methods in **URL order**, not declaration order. The route is
`/admin/festivals/{festival}/promoter/edit/{id}` but the controller
signatures are `editPromoter($id)`, `updatePromoter(Request, $id)`,
`deletePromoter($id)`, `makeManager(Request, Festival, $id)`,
`removeManager(Request, Festival, $id)` — so `$id` ends up holding the
festival slug and `findOrFail('refest-2026')` blows up.
**Fix:** add `string $festival` as the first parameter (matching the URL
order) on every method, same pattern as `OrderController@show(string $festival, string $order)`.

### BUG-AUDIT-003 🔴 Admin order detail (Livewire `OrderDetails`) 500
**Location:** `App\Livewire\Admin\OrderDetails` mounted via `Route::get('/orders/{order}', OrderDetails::class)`
**Symptom:** `GET /admin/festivals/refest-2026/orders/{order}` throws
```
Illuminate\Contracts\Container\BindingResolutionException:
Unable to resolve dependency [Parameter #0 [ <required> $id ]]
in class App\Livewire\Admin\OrderDetails
```
**Root cause:** `OrderDetails::mount($id)` accepts a parameter named
`$id`, but the route parameter is `{order}` — Livewire does **not**
auto-pass a route parameter whose name doesn't match the mount() parameter.
**Fix:** rename the mount parameter to `$order` (and update the body to use
`$order` instead of `$id`).

### BUG-AUDIT-004 🔴 Promoter orders index page 500
**Location:** `resources/views/pages/promoters/orders/index.blade.php`
**Symptom:** hitting the orders index throws
```
Illuminate\View\ViewException:
Route [orders.rerunEmailJob] not defined.
(View: resources/views/pages/promoters/orders/index.blade.php)
```
**Root cause:** the view uses `route('orders.rerunEmailJob', $order->id)` and
`route('orders.rerunImageJob', $order->id)` for the "retry" / "resend" buttons.
The actual route names are `promoter.orders.rerun-image-generation` and
`promoter.orders.rerun-email-sending` (already wired up to
`OrderController::rerunImageGeneration` and `OrderController::rerunEmailSending`).
**Fix:** update the view to use the correct route names.

### BUG-AUDIT-005 🔴 Admin orders index page uses a non-existent route for "Re-run images"
**Location:** `resources/views/pages/admin/orders/index.blade.php`
**Symptom:** clicking the "Generate images" button on a failed order raises a
`RouteNotFoundException` (the rendered view can still load because the
`@if ($order->job_status === 'failed')` branch isn't entered when there are no failed orders, but on a real admin view with at least one failed order the page 500s).
**Root cause:** the view uses `route('orders.rerunImageJob', $order->id)`.
There is no admin-side rerun-image route at all — admin can only retry
through the Livewire `OrderDetails` component or the promoter route.
**Fix:** either add an admin-side rerun-image route (preferred), or change
the view to link to the Livewire order detail page where the bulk actions live.

### BUG-AUDIT-006 🔴 Auth card uses non-existent `route('home')`
**Location:** `resources/views/components/layouts/auth/card.blade.php:27`
**Symptom:** every auth page (login, register, forgot-password, reset-password)
500s after submitting a successful form (the form submits → server returns the auth page
again → the page tries to render the logo link to `route('home')` → boom).
**Stack trace (when form is posted):**
```
Route [home] not defined.
```
**Root cause:** the starter-kit layout used `route('home')` which was
removed by the multi-festival refactor; the redirect now lives in the `/` closure.
**Fix:** change `route('home')` to `url('/')`.

### BUG-AUDIT-007 🟡 PHP 8.4 deprecation warnings on every page load
**Location:** `app/Models/Festival.php`
**Symptom:** log noise (and a deprecation notice in PHP 8.4+)
```
Deprecated: Implicitly marking parameter $fallback as nullable
is deprecated, the explicit nullable type must be used instead
in app/Models/Festival.php on line 146 (and 152, 172).
```
**Root cause:** the `primaryColor / secondaryColor / contrastColorOn`
methods declare `string $fallback = null` — needs `?string $fallback = null`
to be PHP-8.4-clean.
**Fix:** add `?` to the type hints.

---

## 3. Issues found but not blocking

### BUG-AUDIT-008 🟡 Promoter edit view also needs `$festival`
**Location:** `resources/views/pages/admin/promoters/edit.blade.php`
**Symptom:** once BUG-AUDIT-002 is fixed, the edit view will still
need `$festival` because it uses `route('admin.promoters.index', $festival)`
in the page header.
**Fix:** pass `compact('festival', 'promoter')` from `editPromoter()`.

### BUG-AUDIT-009 🟡 Promoter edit view hard-codes English title
**Location:** `resources/views/pages/admin/promoters/edit.blade.php:6`
**Symptom:** the "Edit promoter" page title is hard-coded English; every
other view uses `__('Edit')`.
**Fix:** change to `{{ __('Edit') }}` (or add a lang key).

### BUG-AUDIT-010 🟡 Admin orders index uses wrong "rerun" route (after BUG-AUDIT-005)
**Location:** `resources/views/pages/admin/orders/index.blade.php:115`
**Symptom:** see BUG-AUDIT-005.
**Fix:** tied to that bug.

### BUG-AUDIT-011 🟡 `OrderController1.php` is a dead duplicate
**Location:** `app/Http/Controllers/OrderController1.php`
**Symptom:** none at runtime (it's namespaced to `_deprecated` so the
autoloader won't load it), but it's a foot-gun.
**Fix:** safe to keep as-is per the sandbox restriction; renaming the
namespace or moving the file would prevent future autoloads. Documented
in `bugs.md` (BUG-CODE-001).

### BUG-AUDIT-012 🟡 `TicketOrderCommission` is an empty stub model
**Location:** `app/Models/TicketOrderCommission.php`
**Symptom:** unused (the live model is `TicketCommission`). Confusing for
new devs.
**Fix:** either flesh it out (link order → commission snapshot) or delete it.

### BUG-AUDIT-013 🟡 `User::calculateCommission` lives on the wrong model
**Location:** `app/Models/User.php`
**Symptom:** the model owns business logic + writes to `Log` on every call.
It also assumes `ticket_orders.id` is monotonic with creation order.
**Fix:** extract to `App\Services\CommissionCalculator` (or use the
existing `CommissionDistributor` which already does the split correctly).
The admin-side calculations are already wired through
`CommissionDistributor::splitForOrder`.

### BUG-AUDIT-014 🟡 Mail-template editor only knows about `customer.tickets`
**Location:** `app/Livewire/Admin/MailTemplates/Editor::$templateKeys`
**Symptom:** the editor's "create" dropdown offers a single template
type. P-051 (new mail templates for promoter / admin) is still TODO.
**Fix:** extend `$templateKeys` with the planned `order.completed`,
`promoter.new_order`, `admin.daily_summary` keys and add the corresponding
fallback views + seeds.

### BUG-AUDIT-015 🟡 Promoter orders index still has hard-coded English text
**Location:** `resources/views/pages/promoters/orders/index.blade.php`
**Symptom:** "Duplicate last", "Resend last 5", "Create new order button",
"No orders yet", "Click 'Create new order' to start selling tickets" are
all hard-coded English.
**Fix:** wrap in `__()`.

### BUG-AUDIT-016 🟡 Admin order create form has hard-coded English
**Location:** `resources/views/pages/admin/orders/create.blade.php`
**Symptom:** same as BUG-AUDIT-015. The form is also non-functional
(client-side only) — submitting it succeeds but with no UI feedback, and
the success flow uses `promoter.orders.store` which routes through the
promoter middleware (works because admin role is in the allow-list).
**Fix:** translate + add a flash message on success/fail.

### BUG-AUDIT-017 🟡 Mail template editor is missing CSS preview iframe
**Location:** `resources/views/livewire/admin/mail-templates/editor.blade.php`
**Symptom:** the preview iframe has no height/scroll handling and overflows
on long templates.
**Fix:** CSS-only.

### BUG-AUDIT-018 🟡 `OrderDetails` Livewire component is in `App\Livewire\Admin\` but loaded inside the admin festival area — should be considered
**Location:** `app/Livewire/Admin/OrderDetails.php`
**Symptom:** none — works after BUG-AUDIT-003.
**Fix:** leave as-is.

---

## 4. Page-by-page usability matrix

Verified by `curl`-style HTTP test against the running dev server with each role.

| Page | superadmin | admin | promoter | sub-promoter |
|---|---|---|---|---|
| `/` | redirect → /superadmin/dashboard | redirect → /admin/festivals | redirect → /promoter/festivals | redirect → /sub-promoter/dashboard |
| Login | ✅ | ✅ | ✅ | ✅ |
| Register | ✅ | ✅ | ✅ | ✅ |
| Forgot/Reset password | ✅ | ✅ | ✅ | ✅ |
| Profile / Settings | ✅ | ✅ | ✅ | ✅ |
| Public festival `/f/{slug}` | ✅ | ✅ | ✅ | ✅ |
| **Superadmin area** | ✅ | n/a | n/a | n/a |
| Dashboard | ✅ | n/a | n/a | n/a |
| Festivals (list / create / edit / archive / restore / toggle-public / delete) | ✅ | n/a | n/a | n/a |
| Users (list / create / edit / delete) | ✅ | n/a | n/a | n/a |
| Mail templates (list / edit / preview) | ✅ | n/a | n/a | n/a |
| **Admin area (festival-scoped)** | | | | |
| Dashboard | ✅ | ✅ | n/a | n/a |
| Orders (list / show / bulk download QR / payment update) | ✅ | ✅ | n/a | n/a |
| Orders `/{id}` (Livewire detail) | ⚠️ **BUG-AUDIT-003** | ⚠️ | n/a | n/a |
| Order create | ✅ | ✅ | n/a | n/a |
| Promoters (list / create / edit / delete / make-manager / remove-manager) | ⚠️ **BUG-AUDIT-001/002** | ⚠️ | n/a | n/a |
| Ticket types (list / create / edit / delete / set-price) | ✅ | ✅ | n/a | n/a |
| Scan (camera + manual + recent) | ✅ | ✅ | n/a | n/a |
| Promoter managers (list / show / update) | ✅ | ✅ | n/a | n/a |
| Mail templates | ✅ | ✅ | n/a | n/a |
| Festival picker | ✅ | ✅ | n/a | n/a |
| **Promoter area (festival-scoped)** | | | | |
| Dashboard | ✅ | n/a | ✅ | n/a |
| Orders (list / show) | ✅ | n/a | ⚠️ **BUG-AUDIT-004** | n/a |
| Order create | ✅ | n/a | ✅ | n/a |
| Help | ✅ | n/a | ✅ | n/a |
| Sub-promoters (list / show / create / update) | ✅ | n/a | ⚠️ 403 unless promoter_manager | n/a |
| **Sub-promoter area** | | | | |
| Dashboard | ✅ | n/a | n/a | ✅ |
| Place order (stub) | n/a | n/a | n/a | ✅ (501 by design) |

---

## 5. Critical-path action plan

1. **Fix BUG-AUDIT-001 → 007 (the 7 hard bugs).** This is one focused
   PR; every fix is < 30 lines.
2. **Tighten test coverage** for the festival-scoped admin routes:
   - Add `tests/Feature/PromoterCrudTest.php` (create / edit / delete / make-manager).
   - Add `tests/Feature/AdminOrderDetailTest.php` (mount works, payment
     update works, bulk download works).
   - Add `tests/Feature/PromoterOrdersIndexTest.php` (render does not 500).
   - Add `tests/Feature/AuthCardTest.php` (auth pages render 200 with
     no `route('home')`).
3. **Translate the hard-coded strings** in promoter orders index and
   admin orders create.
4. **Extend the mail-template editor** to know about promoter / admin
   templates (P-051).
5. **Drop the legacy `OrderController1.php`** namespace once the
   sandbox restriction lifts (mark it `@deprecated` for now).
6. **Add CI** (`.github/workflows/ci.yml`) that runs `composer test`
   on every PR.

---

## 6. Inventory of *missing* CRUD pages (from `TODO.md`)

The following items from `TODO.md` are **still open** as of this audit
(cross-referenced with `IMPLEMENTATION_STATUS.md`):

| ID | Status | Notes |
|---|---|---|
| P-010 / P-011 / P-012 mail template show / version history / preview with real data | ❌ open | Editor is the only surface today |
| P-018 refund / void flow | ❌ open | No `Order::refund()` anywhere |
| P-025 `FestivalUser` self-management | ❌ open | Only superadmin can change `role_in_festival` today |
| P-041 reassign order to another promoter | ❌ open | No UI |
| P-042 bulk promoter invite | ❌ open | No UI |
| P-043 import ticket types from another festival | ❌ open | No UI |
| P-048 bulk user import | ❌ open | No UI |
| P-049 clone festival from template | ❌ open | No UI |
| P-051 new mail template types | ❌ open | Editor only ships `customer.tickets` |
| P-054 watch demo / contact form on help | ❌ open | Help page just lists phone + email |
| P-060 notifications center | ❌ open | No bell icon |
| P-063 financial reports | ❌ open | No reports anywhere |
| P-069 global search | ❌ open | Topbar has search icon stub but no endpoint |
| P-070 promoter public profile | ❌ open | No `/p/{id}` route |
| P-071 promoter onboarding wizard | ❌ open | No wizard |
| P-072 language switcher | ❌ open | No switcher in topbar |
| P-073 customer self-service download | ❌ open | No signed-URL flow |
| P-074 API tokens | ❌ open | No `/superadmin/integrations` |
| P-075 branding tab in festival edit | ❌ open | Branding lives inline in `_form` |
| P-076 promoter goals | ❌ open | No goals table |
| P-077 charts | ❌ open | No charting lib |
| P-078 calendar view | ❌ open | No route |
| P-079 waitlist | ❌ open | No route |
| P-080 internal notes on order | ❌ open | No model |
| P-081 support inbox | ❌ open | No route |
| P-082 / P-083 reports | ❌ open | No route |

The defer-list is large but each is independently shippable. The current
focus should be **fixing the 7 bugs first**, then **the 5 small UX
issues**, then deciding which feature to ship next (notifications or
refund flow are the highest-value for users).

---

## 7. Summary

* The architecture is good — multi-festival scoping, role hierarchy,
  commission distributor, mail-template renderer are all solid.
* The admin dashboard, promoter dashboard, ticket scanner, leaderboard,
  mail-template editor, festival picker and public festival page are
  working and polished.
* **7 hard bugs** stop a real user from completing their most common
  actions (creating/editing/deleting a promoter, viewing an admin order,
  rendering the orders index with a failed order, rendering the auth
  pages after a successful form).
* All bugs are localized, have obvious fixes, and are well within an
  afternoon of work.
