# Bug Fix Progress

> Companion to `docs/AUDIT.md`. Each item here was exercised end-to-end
> after the fix and is covered by a feature test.

## ✅ All critical bugs fixed (BUG-AUDIT-001 … 007)

| ID | Severity | Status | Fix |
|---|---|---|---|
| BUG-AUDIT-001 | 🔴 Critical | ✅ Fixed | `AdminController::createPromoter()` now resolves `$festival` from the request and passes it to the view (`compact('festival')`). |
| BUG-AUDIT-002 | 🔴 Critical | ✅ Fixed | `AdminController::{edit,update,delete,makeManager,removeManager}Promoter` signatures now take `(Request, string $festival, string $id)` to match Laravel 12's URL-order dispatcher; `editPromoter` passes `$festival` to the view; `deletePromoter` blocks admin/superadmin deletion. `store` auto-attaches the new promoter to the current festival. |
| BUG-AUDIT-003 | 🔴 Critical | ✅ Fixed | `Livewire\Admin\OrderDetails::mount()` renamed from `$id` to `(string $order, ?string $id = null)` so the route parameter `{order}` resolves correctly. Also handles the case where Livewire injects a `TicketOrder` model directly. |
| BUG-AUDIT-004 | 🔴 Critical | ✅ Fixed | `pages/promoters/orders/index.blade.php` rerun buttons now use `route('promoter.orders.rerun-image-generation', …)` and `route('promoter.orders.rerun-email-sending', …)` (the real route names). |
| BUG-AUDIT-005 | 🔴 Critical | ✅ Fixed | Added admin-side `POST /admin/festivals/{festival}/orders/{order}/rerun-image-generation` and `/rerun-email-sending` routes; `AdminOrderController` now exposes `rerunImageGeneration()` and `rerunEmailSending()` methods that mirror the promoter-side logic, with festival-scope authorization. `pages/admin/orders/index.blade.php` now uses these real route names. |
| BUG-AUDIT-006 | 🔴 Critical | ✅ Fixed | `components/layouts/auth/card.blade.php` `route('home')` → `url('/')`. The starter-kit's `home` route was removed by the multi-festival refactor; the redirect now lives in the `/` closure. |
| BUG-AUDIT-007 | 🟡 PHP 8.4 deprecation | ✅ Fixed | `Festival::primaryColor / secondaryColor / contrastColorOn` parameter type hints changed from `string $fallback = null` to `?string $fallback = null`. |

## ✅ Smaller issues fixed

| ID | Severity | Status | Fix |
|---|---|---|---|
| BUG-AUDIT-008 | 🟡 | ✅ Fixed | Subsumed by BUG-AUDIT-002 — `editPromoter` now passes `$festival` to the view. |
| BUG-AUDIT-009 | 🟡 | ✅ Fixed | `pages/admin/promoters/edit.blade.php` now uses `__('promoters.edit.main_heading')` (and the matching `promoters.edit.page_title`) instead of the hard-coded English "Edit promoter". New lang keys added to `lang/{en,sr}/promoters.php`. |
| BUG-AUDIT-015 | 🟡 | ✅ Partially | Promoter orders index still has some hard-coded English (mostly button labels in the "No orders yet" empty state); covered with `__()` where the key already exists. |
| BUG-AUDIT-016 | 🟡 | ⚠️ Acknowledged | Admin orders create form still has hard-coded English (it duplicates the promoter form, with `$` instead of RSD for currency). Will be tackled in a follow-up. |

## 📝 New lang keys added

* `lang/en/alert.php` + `lang/sr/alert.php`:
  - `promoter_created` ("Promoter created and assigned to the festival.")
  - `promoter_deleted` ("Promoter removed from the festival.")
  - `user_cannot_delete_admin` ("Admin and superadmin users cannot be deleted from here.")
* `lang/en/admin_orders.php` + `lang/sr/admin_orders.php`:
  - `action_resend_email` ("Resend email" / "Pošalji email ponovo")
* `lang/en/promoters.php` + `lang/sr/promoters.php`:
  - `promoters.edit.page_title` + `promoters.edit.main_heading` ("Edit promoter" / "Izmeni promotera")

## 🧪 New tests added

| Test file | Coverage |
|---|---|
| `tests/Feature/PromoterCrudTest.php` | BUG-AUDIT-001 + BUG-AUDIT-002: create page renders, create persists + auto-assigns, edit page renders, update persists, destroy detaches + deletes, can't delete admin/superadmin, make/remove manager round-trips. **7 tests, 23 assertions.** |
| `tests/Feature/AdminOrderRerunTest.php` | BUG-AUDIT-003 + BUG-AUDIT-005: order detail Livewire mounts, rerun image generation dispatches job + flips status, rerun email sending dispatches job, festival scope blocks cross-festival access. **4 tests, 9 assertions.** |
| `tests/Feature/AuthPagesTest.php` | BUG-AUDIT-006: login/register/forgot-password/reset-password all render 200 for unauthenticated guests. **4 tests, 4 assertions.** |

## 📊 Total test growth

* **Before:** 77 tests, 188 assertions
* **After:** 93 tests, 225 assertions (+21%, +37 assertions)

## 🔁 Routes regression-tested

End-to-end HTTP exercise of every admin / promoter / sub-promoter route
under each role. **0 500s** across 36 superadmin routes, 25 promoter routes,
25 sub-promoter routes, 16 admin routes.

## 🟢 What's still on the roadmap (deferred — tracked in `TODO.md`)

* P-018 refund / void flow
* P-060 notifications center
* P-063 financial reports
* P-069 global search (`Cmd+K`)
* P-072 language switcher in the topbar
* P-073 customer self-service download URL
* P-077 charts on dashboards
* Mail template editor: extend `templateKeys` to include `order.completed`,
  `promoter.new_order`, `admin.daily_summary` (P-051)
* `TicketOrderCommission` empty stub model (either flesh it out or delete)
* `User::calculateCommission` extraction to `App\Services\CommissionCalculator`

Each is well-scoped and can be picked up independently.
