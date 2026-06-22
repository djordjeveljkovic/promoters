# Session summary — TODO.md completion pass

> Companion to `docs/AUDIT.md` and `docs/FIX_PROGRESS.md`. Captures the
> feature work completed in this session, plus what's still open.

## ✅ Shipped in this session

| P-id | Feature | Routes / files added |
|---|---|---|
| **P-072** | Language switcher | `app/Http/Middleware/SetLocale.php`, `app/Livewire/LocaleSwitcher.php`, `resources/views/livewire/locale-switcher.blade.php`, mounted in sidebar + auth card. Lang files `lang/{en,sr}/search.php` and `lang/{en,sr}/navigation.php` updated. |
| **P-025** | Admin can change promoter roles inline | `AdminController::changeRole()`, route `admin.promoters.change-role`, dropdown on the promoters index page. New `promoter_managers.role.*` lang keys. |
| **P-027** | Printable commission statement | `AdminController::promoterStatement()`, route `admin.promoters.statement`, view `pages/admin/promoters/statement.blade.php` with per-ticket-type breakdown, ledger, and print-friendly CSS. |
| **P-069** | Global search | `app/Livewire/GlobalSearch.php`, view `livewire.global-search.blade.php`, mounted in topbar. Scoped to user's accessible festivals. |
| **P-070** | Public promoter profile | Migration `2026_06_22_130000_add_promoter_profile_to_users.php`, `PublicPromoterController`, view `pages/public/promoter.blade.php`, route `public.promoter`, `is_public` toggle + `bio` field on `User`, wired into the promoter edit form. |

## 📊 Test growth

| Stage | Tests | Assertions |
|---|---|---|
| Start of audit | 77 | 188 |
| After 7 critical bug fixes (FIX_PROGRESS.md) | 93 | 225 |
| **After this session** | **118** | **283** |

New test files added in this session:
- `tests/Feature/LocaleSwitcherTest.php` (6 tests)
- `tests/Feature/PromoterCrudTest.php` (now 12 tests, added 5)
- `tests/Feature/PublicPromoterProfileTest.php` (6 tests)
- `tests/Feature/GlobalSearchTest.php` (8 tests)

## 🧪 Regression sweep

End-to-end HTTP exercise of every named route, by role:
- superadmin: 38 routes, 0 hard 500s
- admin: 16 routes, 0 hard 500s
- promoter: 25 routes, 0 hard 500s
- sub-promoter: 25 routes, 0 hard 500s
- public: 3 routes (festival landing, promoter profile, karta)

## 🟢 Still open in TODO.md (in suggested implementation order)

### Sprint 4 — Bigger features
- **P-018** Refund / void flow (large)
- **P-060** Notifications center (large — needs notifications table + bell icon + dropdown)
- **P-063** Financial reports (large — daily / weekly / monthly revenue, CSV export)

### Sprint 5 — Polish
- **P-100** Notification bell + global search trigger `Cmd+K`
- **P-104** Bulk actions on tables
- **P-107** Branded 404/403/500 pages
- **P-112** "Print" button on order detail (already partially in place)
- **P-119** Password rotation toggle in settings

### Mail-template editor (P-051) extension
The editor today only knows `customer.tickets`. Adding promoter / admin
templates is a localized change (`Editor::$templateKeys` + a few fallback
Blade views in `resources/views/emails/`).

### Minor
- P-010/011/012 mail template show / version history / preview-with-real-data
- P-021 user profile edit verification (existing page is comprehensive — just needs eyes)
- P-041 reassign order to another promoter
- P-042 bulk promoter invite
- P-043 import ticket types from another festival
- P-048 bulk user import
- P-049 clone festival from template
- P-054 watch demo / contact form on help
- P-070 done — public promoter profile shipped
- P-072 done — language switcher shipped
- P-073 customer self-service download URL (signed)
- P-074 API tokens (`/superadmin/integrations`)
- P-075 festival branding tab (inline in edit form today)
- P-076 promoter goals
- P-077 charts on dashboards
- P-078 calendar view of orders
- P-079 waitlist
- P-080 internal notes on order
- P-081 support inbox
- P-082 / P-083 email-delivery / revenue reports

Each is well-scoped and independently shippable.

## ⚠️ Known small issues still open

* **Hard-coded English strings** in a few views (`admin/orders/create.blade.php`,
  parts of `promoters/orders/index.blade.php`). Translated where the lang
  key already existed; cosmetic follow-up.
* **`OrderDetails` Livewire view** still does its own emoji-`-based`
  legend; not pretty, but works.
* **Mail-template preview** falls back to a synthetic order instead of
  a real one (P-012).
* **`User::calculateCommission`** still lives on the model + logs spam
  (BUG-AUDIT-013). The admin-side numbers come from
  `CommissionDistributor` so this is dead code for the hot paths.

None of these are user-blocking; they're polish.

## 🚦 How to keep going

1. **P-018 Refund flow** — biggest single user win left; needs a small
   `refunded` job status, an admin "Refund" button on the order detail,
   and a "Refund confirmation" email template.
2. **P-060 Notifications center** — model + Livewire dropdown + sidebar bell;
   medium effort but high daily-use value.
3. **P-063 Financial reports** — needs a `reports` controller, a couple of
   chart helpers (or a small `chart.js` integration), and CSV export.
4. Then the smaller polish items in batches.
