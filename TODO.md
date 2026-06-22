# TODO — Missing pages & Create buttons

> Living backlog. Each item is small enough to pick up as a single PR.
> Convention: `[ ]` todo · `[~]` in progress · `[x]` done.

## How to read this file

- **Section 1** = entire views that don't exist yet (route / controller
  exists, but `resources/views/...` is missing or empty). Without these
  the app returns 500.
- **Section 2** = full CRUD for models that currently only have a
  list/create but no edit, show, or delete confirmation UI.
- **Section 3** = "Create" / "+ Add" buttons missing on existing
  index pages.
- **Section 4** = feature pages the product needs but that have no
  controller / route yet.
- **Section 5** = UX polish that doesn't need a new page but should
  exist as a button / link / inline panel inside an existing page.

Each item has a short ID so it's easy to reference in commits
(`feat(P-001): ...`) or in PR titles.

---

## 1. Missing views (route exists → 500)

These are the immediate, app-breaking gaps. If the route fires, the
user sees the "Internal Server Error" page.

| ID      | View (expected)                                          | Route                                        | Controller                                              | Notes |
| ------- | -------------------------------------------------------- | -------------------------------------------- | ------------------------------------------------------ | ----- |
| P-001   | `pages/promoters/orders/show.blade.php`                  | `promoter.orders.show` (GET)                 | `OrderController@show`                                 | Promoter needs to see one order's details: items, tickets (with QR images), commission earned, status timeline, "resend email" + "re-run image generation" actions. Use the new design system (`<x-ds.card>`, `<x-ds.table>`, status badges). Currently a 500. |
| P-002   | `pages/promoters/orders/edit.blade.php` (or refactor to show + dedicated actions) | _n/a — file is empty_                       | —                                                      | File exists at 0 bytes. Decide: either delete the route entirely (orders shouldn't be edited by promoter post-creation) **or** turn it into a dedicated "Order detail" view that supersedes the missing show view. |

---

## 2. Full CRUD for existing models

| ID      | Model            | Missing action          | Where it should live                                      |
| ------- | ---------------- | ----------------------- | --------------------------------------------------------- |
| P-010   | `MailTemplate`   | Show (preview-only)     | `/superadmin/mail-templates/{template}` and `/admin/festivals/{festival}/mail-templates/{template}`. Today the editor is the only way to "show" — but a separate read-only "preview as customer receives it" page, with a list of all historical versions, is needed. |
| P-011   | `MailTemplate`   | Version history         | The `version` column exists; surface a timeline / diff view so admins can see what changed and roll back. |
| P-012   | `MailTemplate`   | Preview with a real `TicketOrder` | The preview today uses synthetic stub data. Add a "preview with this order" dropdown that lets an admin pick an actual order and render the email as that customer would see it. |
| P-013   | `TicketType`     | Photo upload UI          | `PUT admin/.../ticket-types/{id}/photo` exists but has no form. Add an upload tile inside the ticket-type edit page. |
| P-014   | `TicketType`     | QR coordinates editor   | `PUT admin/.../ticket-types/{id}/qr` exists. The create form has inputs but the edit page doesn't expose them in a "live preview on the template image" way. Add a drag-to-place overlay. |
| P-015   | `TicketType`     | Quick price change      | `PUT admin/.../ticket-types/{id}/price` exists. Add an inline price editor on the index row. |
| P-016   | `TicketType`     | Commission tier editor  | `PUT admin/.../commissions` exists. Currently the only way to edit tiers is via the create form. Build a dedicated "Commissions" page per festival with a tier-by-tier list. |
| P-017   | `TicketType`     | Duplicate               | Right-click → "Duplicate" on the index. Useful when a new festival reuses an old price list. |
| P-018   | `TicketOrder`    | Refund / void flow      | No way to refund or void an order from the UI. Add a "Refund" button on the order detail page (admin/promoter show) that triggers a status change + email. |
| P-019   | `TicketOrder`    | Re-send email button    | The promoter orders index already has a "resend" action via Livewire, but the admin order detail page does not — only bulk actions. Add a "Send tickets email again" button. |
| P-020   | `Ticket`         | Scan / verify           | There's no way to mark a ticket as scanned at the gate. Add a "Scanner" page (`/admin/festivals/{festival}/scan`) that accepts a QR code and marks the matching `Ticket` as used. |
| P-021   | `User`           | Profile edit (admin)    | Superadmin can create users; the edit page exists but doesn't expose the password, role, or festival assignments in a clean way. Re-verify the existing edit page covers all fields. |
| P-022   | `Festival`       | Archive / restore       | `status` column exists with `archived` value but no UI to set it. Add an "Archive" button on the festival row. |
| P-023   | `Festival`       | Duplicate from existing | When creating a new festival, offer "Start from last year's settings" — copy ticket types, commission tiers, colors. |
| P-024   | `Festival`       | Public landing toggle   | `is_public` flag exists but is only set in the create form. Add a per-row toggle on the index. |
| P-025   | `FestivalUser`   | Self-management         | The pivot table has `role_in_festival` but currently only superadmin can change it. Let festival admins change the role of their own promoters/sub-promoters. |
| P-026   | `Festival`       | Statistics tab          | On the festival show / edit page, add a "Stats" tab: ticket-type breakdown, promoter leaderboard, daily sales chart. |
| P-027   | `Promoter`       | Commission statement   | A printable PDF / HTML view of a promoter's earnings per festival. |
| P-028   | `Promoter`       | Payout request         | Promoter requests a payout; admin marks it paid. Track in a new `payouts` table or use the existing `paid` column on `users` with an audit trail. |

---

## 3. Missing "Create" buttons on existing pages

These pages already render but the user can't trigger the create flow
from them.

| ID      | Page                                              | Add this button                                                  |
| ------- | ------------------------------------------------- | ---------------------------------------------------------------- |
| P-040   | `/admin/festivals` (festival picker)             | No create here on purpose — only superadmin creates festivals. **But add a link** to the superadmin create form when the user has access to it (`/superadmin/festivals/create`). |
| P-041   | `/admin/festivals/{festival}/orders` (admin)     | ✓ already has "New order" button. **Add a "Reassign to another promoter" action** so the admin can move an order if it was mis-attributed. |
| P-042   | `/admin/festivals/{festival}/promoters` (admin)  | ✓ has "Add promoter" button. **Add "Bulk invite"** — paste a list of emails, send invite links. |
| P-043   | `/admin/festivals/{festival}/ticket-types` (admin) | ✓ has "Create ticket type" button. **Add "Import from last year"** action. |
| P-044   | `/admin/festivals/{festival}/dashboard` (admin)   | **Add a quick-action panel**: "New order", "New ticket type", "Invite promoter". |
| P-045   | `/promoter/festivals` (festival picker)           | No create here on purpose. **Add a banner** at the top: "You don't have access to any festival yet — contact your admin." |
| P-046   | `/promoter/festivals/{festival}/orders`           | ✓ has "New order" button. **Add "Duplicate previous order"** — pre-fill the new order form from the promoter's last order. |
| P-047   | `/promoter/festivals/{festival}/dashboard`       | **Add a "Resend last 5 emails" bulk action** for promoters whose emails bounced. |
| P-048   | `/superadmin/users`                              | ✓ has "New user" button. **Add "Bulk import"** — CSV upload. |
| P-049   | `/superadmin/festivals`                          | ✓ has "New festival" button. **Add "Clone from template"** — pick an existing festival and copy everything. |
| P-050   | `/sub-promoter/dashboard`                        | **Add "Place order" link** to the parent promoter's order page. Today the sub-promoter has to ask the parent for the link. |
| P-051   | `/admin/festivals/{festival}/mail-templates`      | ✓ has "+ Customer — Tickets delivery" create button. **Add "+ Promoter — New order notification"** and "+ Admin — Daily summary" so admins can customise every email their platform sends. |
| P-052   | Global topbar                                    | **Add a "+ New order" global shortcut** (visible when a festival is in scope) so promoters can create an order from any page, not just the orders index. |
| P-053   | `/admin/festivals/{festival}/orders/{order}`     | **Add "Print"** button — opens a printable view of the order with all ticket QR codes for the gate staff. |
| P-054   | `/promoter/festivals/{festival}/help`            | **Add a "Watch demo" CTA** (video) and a "Contact support" inline form. |

---

## 4. Brand-new feature pages (no controller / route yet)

These are larger features the product clearly needs. None of them exist
yet — they need a route, controller, view, and (usually) tests.

| ID      | Feature                                                    | Where it should live                          |
| ------- | ---------------------------------------------------------- | --------------------------------------------- |
| P-060   | **Notifications center** — a real bell icon in the topbar with a list of unread items (new orders, failed emails, payment received). Mark as read, archive, jump to entity. | Bell in the global header → `/notifications` |
| P-061   | **Activity log** — every meaningful action (order created, status changed, payment received, user invited) is logged with actor + timestamp + IP. Surface per-festival and per-user timelines. | `/superadmin/activity` + `/admin/festivals/{festival}/activity` |
| P-062   | **Ticket scanner** — camera-based QR scanner for the entrance. Marks `Ticket.is_active = false` (or adds a `scanned_at` column) and shows the buyer info on screen. | `/admin/festivals/{festival}/scan` + `/promoter/festivals/{festival}/scan` (for lead promoter) |
| P-063   | **Financial reports** — daily / weekly / monthly revenue per festival, per ticket type, per promoter. Exportable as CSV. | `/admin/festivals/{festival}/reports` |
| P-064   | **Public festival landing** — a public, no-login page that shows one festival with its ticket types, "Buy now" button. Today the only public page is the marketing welcome. | `/f/{slug}` |
| P-065   | **Promoter leaderboard** — top-N leaderboard widget on the admin dashboard, with badges (gold / silver / bronze) and "view all" link. | `/admin/festivals/{festival}/leaderboard` |
| P-066   | **Email template preview with custom data** — pick a sample order, render the template with that data so admins can sanity-check before saving. | Inline on the mail-template editor (P-012). |
| P-067   | **Bulk order import** — admins upload a CSV of orders, system processes them through the same pipeline (generate images, send emails). | `/admin/festivals/{festival}/orders/import` |
| P-068   | **Refund flow** — admin can fully or partially refund an order, the system cancels the tickets, sends refund confirmation email, and updates `paid` / `total` accordingly. | Inline on the order detail page (P-018). |
| P-069   | **Search** — global app search (orders, promoters, festivals) with keyboard shortcut (`Cmd/Ctrl + K`). | Topbar → `/search?q=...` |
| P-070   | **Promoter public profile** — each promoter gets a `/p/{id}` page with their public name, bio, photo, list of festivals they sell for. | `/p/{promoter}` |
| P-071   | **Promoter onboarding wizard** — multi-step wizard for new promoters: pick festival → set commission tier → upload selfie → get first order link. | Modal or `/promoter/onboarding` |
| P-072   | **Language switcher** in the topbar — currently the locale is set in the URL/session only. Add a dropdown that switches between `en` / `sr`. | Topbar (next to the theme toggle) |
| P-073   | **Customer-facing "view my tickets" page** — token-protected URL in the email so customers can re-download their tickets without logging in. | `/tickets/lookup?email=...&order=...` |
| P-074   | **Webhook / API tokens** for promoter integrations (so external systems can create orders). | `/superadmin/integrations` |
| P-075   | **Festival "branding" page** — upload logo, set colors, choose typography, see a live preview of the picker card. | Inside `/superadmin/festivals/{festival}/edit` (tab) |
| P-076   | **Promoter goals** — set a sales target per festival, show a progress bar on the promoter dashboard. | `/admin/festivals/{festival}/promoters/{id}/goal` + dashboard widget |
| P-077   | **Charts** — line chart of daily sales, bar chart of top ticket types, pie chart of order status distribution. | Various dashboards |
| P-078   | **Calendar view** — orders shown on a calendar (by event date or by order date). | `/admin/festivals/{festival}/orders/calendar` |
| P-079   | **Waitlist** — if a ticket type is sold out, customers can join a waitlist and get notified when tickets are released. | `/admin/festivals/{festival}/waitlist` |
| P-080   | **Promoter chat / comments** — internal note thread per order or per promoter for the admin team. | Inside the order detail page |
| P-081   | **Customer support inbox** — emails sent to `support@refest.rs` land in a unified inbox the admins can reply to. | `/admin/support` |
| P-082   | **Reports → Email delivery** — view bounce / open / click stats from the mailer. | `/superadmin/reports/email` |
| P-083   | **Reports → Revenue** — financial summary across all festivals. | `/superadmin/reports/revenue` |

---

## 5. UX polish — buttons, links, inline panels inside existing pages

These are smaller, but each one makes the app feel complete.

| ID      | Page / Context                                          | What to add                                                   |
| ------- | ------------------------------------------------------- | ------------------------------------------------------------- |
| P-100   | Topbar (every authenticated page)                       | **Notification bell** (P-060) + **search trigger** (`Cmd+K`) + current festival breadcrumb is clickable to open the festival picker. |
| P-101   | Sidebar user menu                                       | **"Switch language"** menu item (P-072).                       |
| P-102   | All tables                                              | **"Export CSV"** button in the toolbar.                        |
| P-103   | All tables                                              | **Bulk actions** when rows are selected (delete, archive, export). |
| P-104   | All forms                                               | **"Save and add another"** button (super useful for ticket types, commission tiers). |
| P-105   | All forms                                               | **"Discard changes"** confirmation when navigating away with unsaved edits (`@dirty` Livewire). |
| P-106   | Login screen                                            | **"Stay signed in"** by default (longer session), "Sign in with Google" stub. |
| P-107   | 404 / 403 / 500 error pages                             | Friendly branded pages with a "back to dashboard" CTA, not the default Laravel error. |
| P-108   | Order detail page                                       | **"Timeline"** view — every status change with timestamp + actor. |
| P-109   | Order detail page                                       | **"Send WhatsApp reminder"** button (optional channel). |
| P-110   | Ticket type index                                       | **"Clone"** icon on each row.                                  |
| P-111   | Ticket type index                                       | **"Archive"** icon (sets `is_active = false` instead of delete). |
| P-112   | Promoter dashboard                                      | **"This month vs last month"** sparkline.                     |
| P-113   | Admin dashboard                                         | **"Festival comparison"** widget — pick 2 festivals, see their stats side by side. |
| P-114   | Mail-template editor                                    | **"Send test email to me"** button.                            |
| P-115   | Mail-template editor                                    | **"Lint"** button — check for missing variables, broken Blade. |
| P-116   | Festival create / edit                                  | **"Preview picker card"** — shows how the festival looks in the admin/promoter picker. |
| P-117   | User profile settings                                   | **"Active sessions"** list — see where you're signed in, sign out others. |
| P-118   | Sub-promoter dashboard                                  | **"My parent promoter"** card with name + contact.             |
| P-119   | Settings → password                                     | **"Require password change every 90 days"** toggle.            |
| P-120   | Topbar                                                  | **"Live now"** indicator when a festival is actively selling (recent order in last 5 min). |

---

## Suggested implementation order (one PR per sprint)

### Sprint 1 — Make the app work
- **P-001** Promoter order show page (fixes 500 on a core flow)
- **P-002** Decide + delete the empty `pages/promoters/orders/edit.blade.php`

### Sprint 2 — Finish the data model CRUDs
- P-013 ticket-type photo upload
- P-014 ticket-type QR editor
- P-015 quick price change
- P-016 commission tier editor
- P-010 mail template show
- P-011 mail template version history
- P-012 mail template preview with real data

### Sprint 3 — Actionability
- P-040 → P-054 (all "missing button" items, biggest UX wins first)
- P-100 notification bell (P-060)
- P-069 global search

### Sprint 4 — Bigger features
- P-060 notifications center
- P-062 ticket scanner
- P-020 ticket scan
- P-019 resend email button
- P-018 refund flow
- P-063 financial reports

### Sprint 5 — Polish + scale
- P-070 promoter public profile
- P-073 customer self-service
- P-074 API tokens
- P-075 branding page
- P-077 charts
- P-078 calendar view
- P-079 waitlist
- P-100 → P-120 (polish items)

---

## Conventions

- All new pages **must** use the design system components
  (`<x-ds.card>`, `<x-ds.page-header>`, `<x-ds.table>`, etc.).
  No raw Tailwind utility soup in views.
- All new routes need a **smoke test** in `tests/Feature/SmokeTest.php`
  (at minimum: returns 200, contains the design system marker).
- All new pages need a **sidebar entry** if they belong to a role
  (superadmin / admin / promoter) — check `components/layouts/app/sidebar.blade.php`.
- All new user-facing copy needs an entry in `lang/en/...` and
  `lang/sr/...` — never hardcode English text.
- All new pages need a **`{{ __('page_title') }}`** in `<x-layouts.app>` /
  `<x-layouts.auth>` so the browser tab is labelled correctly.
