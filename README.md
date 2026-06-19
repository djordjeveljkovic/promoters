# Promoteri — REFEST Festival Ticket Platform

> **Promoteri** is a Laravel 12 / Livewire 3 ticket-sales back-office built for the
> **REFEST Festival**. It powers a tiered sales channel in which administrators
> manage ticket types and commission rules, promoters sell tickets through their
> own panel, and a background pipeline generates QR-coded ticket images and
> delivers them by e-mail — all in real time over Laravel Reverb WebSockets.

[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel)](https://laravel.com)
[![Livewire](https://img.shields.io/badge/Livewire-3-FB70A9?logo=livewire)](https://livewire.laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php)](https://www.php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8-4479A1?logo=mysql)](https://www.mysql.com)
[![Tailwind](https://img.shields.io/badge/TailwindCSS-4-06B6D4?logo=tailwindcss)](https://tailwindcss.com)
[![Three.js](https://img.shields.io/badge/Three.js-WebGL-black?logo=three.js)](https://threejs.org)

---

## Table of Contents

1. [Overview](#overview)
2. [Feature Highlights](#feature-highlights)
3. [Architecture](#architecture)
4. [Tech Stack](#tech-stack)
5. [Domain Model](#domain-model)
6. [Roles & Permissions](#roles--permissions)
7. [Order Lifecycle](#order-lifecycle)
8. [Commission Engine](#commission-engine)
9. [Real-time Updates (Reverb)](#real-time-updates-reverb)
10. [Project Structure](#project-structure)
11. [Installation](#installation)
12. [Configuration](#configuration)
13. [Database Schema](#database-schema)
14. [Running the App](#running-the-app)
15. [Background Workers & Queues](#background-workers--queues)
16. [Internationalisation](#internationalisation)
17. [API Endpoints](#api-endpoints)
18. [Landing Page (Three.js / WebGL)](#landing-page-threejs--webgl)
19. [Testing](#testing)
20. [Deployment Notes](#deployment-notes)
21. [Roadmap](#roadmap)
22. [Known Issues](#known-issues)
23. [License](#license)

---

## Overview

Promoteri is the back-office of a festival ticket-resale operation.  Three groups
of people interact with the system:

| Group            | What they do                                                              |
| ---------------- | ------------------------------------------------------------------------- |
| **Admin**        | Manage ticket types, prices, QR templates, commission tiers, promoters, see every order, reconcile payments. |
| **Promoter**     | Create orders on behalf of customers, see their own sales, earnings and balances. |
| **Sub-promoter** | A promoter can spawn sub-promoters; sub-promoters can place orders against the parent promoter. |

The platform is designed to be deployed at the edge of a busy festival sales
season: it is queue-driven, asynchronous, and event-broadcasted so the UI stays
responsive while hundreds of ticket images are being rendered.

---

## Feature Highlights

- **Tiered commission engine** — Commission per ticket depends on the *cumulative*
  number of tickets sold by a promoter for a given ticket type.  Tiers are
  time-versioned so historical orders always use the rules that were active when
  the order was placed.
- **QR-coded ticket image generation** — Every sold ticket is rendered server-side
  by compositing the ticket-type background with a unique QR code at configurable
  coordinates, all powered by GD + `simplesoftwareio/simple-qrcode`.
- **Bulk download** — Admins can package all of an order's generated images and
  QR codes into a single ZIP.
- **Real-time order feed** — As soon as a promoter creates an order, the admin
  dashboard receives a `JobCompleted` event over Laravel Reverb.
- **Multi-role auth** — `admin`, `promoter`, `sub_promoter` with a custom
  `RoleMiddleware` that supports `|`-separated role lists.
- **Bilingual UI** — English (`en`) + Serbian Cyrillic (`sr`).
- **Livewire Volt / Flux** — Modern stack on top of Laravel's full-stack
  ecosystem.
- **Landing page** — Interactive Three.js / WebGL scene replacing the old
  single-image splash.

---

## Architecture

```
┌─────────────────────────────────────────────────────────────────────┐
│                          Browser (Livewire / Alpine)                 │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────────────────┐  │
│  │ Landing page │  │  Admin panel │  │  Promoter / Sub-promoter │  │
│  │  (Three.js)  │  │  (Flux UI)   │  │       panels             │  │
│  └──────────────┘  └──────────────┘  └──────────────────────────┘  │
└────────┬──────────────────┬──────────────────────┬──────────────────┘
         │                  │                      │
         ▼                  ▼                      ▼
┌─────────────────────────────────────────────────────────────────────┐
│                Laravel 12 (HTTP / WebSocket via Reverb)               │
└──────┬─────────────────────────┬─────────────────────┬───────────────┘
       │                         │                     │
       ▼                         ▼                     ▼
   ┌────────┐             ┌────────────┐         ┌────────────┐
   │ MySQL  │             │ Queue      │         │ Mail (SMTP │
   │ 8.0    │             │ (database) │         │ or log)    │
   └────────┘             └────┬───────┘         └────────────┘
                               │
              ┌────────────────┼────────────────┐
              ▼                ▼                ▼
   GenerateTicketImagesJob  OrderCompleted  SendCustomerTicketsEmailJob
   (GD + QR code)          (broadcasts)    (Mailable)
```

---

## Tech Stack

| Layer       | Tool                                                                |
| ----------- | ------------------------------------------------------------------- |
| Framework   | **Laravel 12** (PHP 8.2+)                                           |
| Front-end   | **Livewire 3**, **Volt**, **Flux UI**, **Alpine.js**, **Tailwind 4** |
| 3D / WebGL  | **Three.js** (landing page scene)                                   |
| Bundler     | **Vite 6**                                                          |
| Auth        | Laravel Breeze (Livewire starter kit, customised)                   |
| Database    | **MySQL 8** (also supports SQLite for tests)                        |
| Queue       | `database` driver (default), `sync` for tests                       |
| Real-time   | **Laravel Reverb** (WebSocket)                                      |
| Mail        | SMTP / log                                                          |
| QR          | `simplesoftwareio/simple-qrcode` + `chillerlan/php-qrcode`          |
| Image work  | PHP **GD**                                                          |
| i18n        | PHP arrays in `lang/en/` and `lang/sr/`                             |

---

## Domain Model

```
User (admin | promoter | sub_promoter)
 │── parent_id ───────────────► User (promoter)
 │
 ├── owns ──► TicketOrder
 │              ├── job_status   (pending|processing|failed|blocked|completed|sent)
 │              ├── order_number (6-char unique alpha code)
 │              ├── ordered_by   ► User  (customer — created on the fly)
 │              ├── requested_by ► User  (promoter who entered the sale)
 │              ├── total, paid, total_commission_earned
 │              │
 │              ├── items ─► TicketOrderItem
 │              │              ├── ticket_type_id ─► TicketType
 │              │              └── quantity, commission_earned
 │              │
 │              └── tickets ─► Ticket
 │                              ├── code (unique)
 │                              ├── image_path, qr_code_path
 │                              └── ticket_type_id ─► TicketType

TicketType
 ├── photo_path     (template background)
 ├── qr_coordinates (x, y, size — where the QR is stamped)
 └── commissions ──► TicketCommission   (min_sold, max_sold, commission_amount)
                                              validity window  (valid_from, valid_to)
```

### Why `ordered_by` *and* `requested_by`?

- `requested_by` is the **promoter** who entered the order.
- `ordered_by` is the **end customer** the ticket was sold to.
  For sales made through the promoter flow, the customer may be a fresh `User`
  record created on the fly (role `buyer`).

---

## Multi-Festival Architecture

Every edition of a festival (REFEST 2025, REFEST 2026, Lovefest 2027, …) is
its own row in the `festivals` table.  Ticket types, ticket orders, order
items and individual tickets all carry a `festival_id` foreign key, so they
cannot leak across festivals.

### Pivot: `festival_user`

The pivot table `festival_user` grants a user access to a festival in a
specific role:

```php
festival_user
 ├── festival_id
 ├── user_id
 ├── role_in_festival   (admin | promoter | sub_promoter)
 ├── assigned_by
 └── assigned_at
```

A user can be admin on one festival and promoter on another at the same time
— the pivot makes that trivial.

### Global roles vs. festival-scoped roles

Two layers of role checks exist:

1. **Global role middleware** (`role:admin|superadmin`) — does the user have
   access to this area at all?
2. **Festival access middleware** (`festival.access`) — does this user have
   access to *this specific* festival?  Superadmins always pass.

The festival parameter is resolved by `EnsureFestivalAccess` from:
the route-model binding `festival` (object / numeric id / slug), or a
`festival_id` query string.  The resolved `Festival` model is stored on the
request as `$request->attributes->get('festival')` so controllers can access
it without re-querying.

### Routing

```
/admin/festivals                        ← picker — list of festivals I admin
/admin/festivals/{festival}/dashboard   ← festival-scoped admin area
/admin/festivals/{festival}/orders      ← only this festival's orders
/admin/festivals/{festival}/ticket-types
/admin/festivals/{festival}/promoters

/promoter/festivals                     ← picker for the promoter
/promoter/festivals/{festival}/dashboard
/promoter/festivals/{festival}/orders
/promoter/festivals/{festival}/order/create

/sub-promoter/dashboard
```

The festival slug is the URL identifier (so the URLs stay stable across
renames).  Resolving is handled by `EnsureFestivalAccess`.

### Login routing

`Login::login()` now routes every role to the right landing page:

| Role           | Destination                                                    |
| -------------- | -------------------------------------------------------------- |
| `superadmin`   | `/superadmin/dashboard` (global overview)                      |
| `admin`        | `/admin/festivals` (picker)                                    |
| `promoter`     | `/promoter/festivals/refest-2026/dashboard` if only one, else picker |
| `sub_promoter` | `/sub-promoter/dashboard` (or picker if assigned to >1 festival) |
| anything else  | `/admin/festivals` (safe default)                              |

The legacy `/dashboard` URL is preserved as a role-aware redirect, so old
bookmarks and external integrations keep working.

### Festival selector

A small dropdown `<x-festival.selector />` lives in the global sidebar; it
appears for anyone with access to more than one festival (or always, for
superadmins) and lets you swap the active festival without losing your place
in the app.

---

## Roles & Permissions

The `users.role` enum is migrated in
`database/migrations/2026_06_19_120003_expand_users_role_enum.php` to:

```php
$table->enum('role', ['superadmin', 'admin', 'promoter', 'sub_promoter', 'buyer']);
```

> The original migration used the typo `supreme`.  That has been superseded
> by the expansion migration above; the typo lives only in the historical
> initial migration for reproducibility.

| Role           | Effective powers                                                                   |
| -------------- | ---------------------------------------------------------------------------------- |
| `superadmin`   | **Global** — sees and manages every festival, every user, every order.  Bypasses all festival-scope middleware. |
| `admin`        | All of `promoter` powers + manage ticket types, promoters, commissions, see every order **within the festivals they're assigned to**. |
| `promoter`     | Create orders, see own dashboard, create sub-promoters — scoped to their assigned festivals. |
| `sub_promoter` | Create orders under the parent promoter; sees a read-only slice of the parent's orders. |
| `buyer`        | Internal record used to attach a customer to an order (no UI).                     |

`RoleMiddleware` understands `|` separators, e.g.
`Route::middleware('role:admin|superadmin')`.  `EnsureFestivalAccess`
*additionally* checks the `festival_user` pivot (except for superadmins, who
are global).

---

## Order Lifecycle

```
                 ┌──────────┐
 promoter  ───►  │ pending  │   ──► (OrderController::store creates the order)
                 └────┬─────┘
                      ▼
                 ┌─────────────┐  OrderCompleted dispatched
                 │ processing  │  GenerateTicketImagesJob runs
                 └────┬────────┘
        ┌─────────────┼──────────────┐
        ▼             ▼              ▼
   ┌─────────┐  ┌──────────┐   ┌──────────┐
   │ failed  │  │ blocked  │   │ completed│ ──► SendCustomerTicketsEmailJob
   └─────────┘  └──────────┘   └────┬─────┘
                                   ▼
                                ┌──────┐
                                │ sent │
                                └──────┘
```

`GenerateTicketImagesJob` uses GD to:

1. Generate a QR PNG containing the ticket's unique `code`.
2. Load the ticket-type background.
3. Copy the QR onto the background at the configured `(x, y, size)` coordinates.
4. Save the composited PNG into `storage/app/public/tickets/<order>/`.
5. Update the `Ticket` model with `image_path` and `qr_code_path`.
6. On any failure the order is moved to `failed` and
   `NotifyUserOfFailedImageGeneration` listener sends an admin notification.

---

## Commission Engine

Commissions are *not* a flat percentage.  They are tiered by **cumulative tickets
sold by a single promoter for a single ticket type** at the time of the order.

```php
TicketCommission
  ├─ ticket_type_id
  ├─ min_sold          // inclusive lower bound
  ├─ max_sold          // inclusive upper bound (null = open ended)
  ├─ commission_amount // per ticket in this tier
  ├─ valid_from        // when the tier became active
  └─ valid_to          // when it expired (null = currently active)
```

Algorithm (see `User::calculateCommission()`):

1. Count how many tickets of this type the promoter had already sold in
   *completed* orders **before** this order (using `ticket_orders.id` as a
   proxy for creation order).
2. Load all commission tiers for the ticket type whose `valid_from` /
   `valid_to` window covers the order's `created_at`.
3. For every tier, compute the overlap between the tier's `[min, max]` range and
   the new order's `[previousCount + 1, previousCount + qty]` range, and
   multiply by `commission_amount`.

This guarantees historical fairness: changing a commission tier today does not
retroactively rewrite yesterday's commission numbers.

---

## Real-time Updates (Reverb)

`OrderCompleted` (dispatched by `OrderController::store` after the order is
written) is broadcast over Reverb.  The admin dashboard subscribes to:

```php
App\Events\JobCompleted
```

`config/broadcasting.php` exposes the `reverb` connection, which reads
`REVERB_APP_ID`, `REVERB_APP_KEY`, `REVERB_APP_SECRET`, `REVERB_HOST`,
`REVERB_PORT` from `.env`.

Run the broadcaster locally with:

```bash
php artisan reverb:start
```

---

## Project Structure

```
promoteri/
├── app/
│   ├── Events/JobCompleted.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AdminController.php            # Admin CRUD: promoters, commissions, dashboard
│   │   │   ├── AdminOrderController.php       # Admin: orders, ZIP, payment update
│   │   │   ├── PromoterController.php         # Promoter: dashboard, sub-promoters, help
│   │   │   ├── OrderController.php            # Order create / store / re-run jobs
│   │   │   ├── OrderController1.php           # ⚠ legacy duplicate
│   │   │   ├── SubPromoterController.php      # ⚠ stub — see bugs.md
│   │   │   └── TicketController.php           # Admin: ticket types
│   │   └── Middleware/RoleMiddleware.php
│   ├── Jobs/
│   │   ├── GenerateTicketImagesJob.php        # GD + QR composition
│   │   ├── OrderCompleted.php                 # Broadcast event wrapper
│   │   └── SendCustomerTicketsEmailJob.php
│   ├── Livewire/
│   │   ├── Actions/Logout.php
│   │   ├── Admin/OrderDetails.php
│   │   ├── Auth/  (Login, Register, ForgotPassword, ResetPassword, …)
│   │   └── Settings/  (Profile, Password, Appearance, DeleteUserForm)
│   ├── Listeners/NotifyUserOfFailedImageGeneration.php
│   ├── Mail/CustomerTicketsMail.php
│   ├── Models/
│   │   ├── Ticket.php
│   │   ├── TicketCommission.php
│   │   ├── TicketOrder.php
│   │   ├── TicketOrderItem.php
│   │   └── TicketType.php
│   └── Notifications/OrderImageGenerationFailed.php
├── bootstrap/app.php            # Route & middleware registration
├── config/                       # app, auth, broadcasting, cache, database, mail, queue, …
├── database/
│   ├── factories/UserFactory.php
│   ├── migrations/
│   └── seeders/                  # UserSeeder, TicketTypesSeeder (disabled)
├── lang/
│   ├── en/   (admin_orders, alert, dashboard, login, …)
│   └── sr/   (same set, Serbian Cyrillic)
├── public/
│   ├── build/   # Vite output
│   ├── img/     # ticket photos
│   ├── logo.png, logo.svg, logo_white.webp, favicon.*
│   └── index.php
├── resources/
│   ├── css/app.css              # Tailwind 4 entry
│   ├── js/
│   │   ├── app.js               # Alpine bootstrap
│   │   ├── ticket_items.js      # Alpine component: ticket cart
│   │   └── landing/             # Three.js / WebGL landing page
│   └── views/
│       ├── components/          # Layouts (app, auth), partials (head, settings-heading)
│       ├── emails/              # Customer ticket mailable views
│       ├── flux/                # Flux overrides
│       ├── livewire/            # Livewire views
│       ├── pages/               # Admin & promoter pages
│       ├── partials/            # Shared partials
│       └── welcome.blade.php    # Landing page
├── routes/
│   ├── auth.php
│   ├── channels.php
│   ├── console.php
│   └── web.php
├── storage/                     # Logs, cache, generated tickets
├── tests/
│   ├── Feature/
│   └── Unit/
├── .env                         # Local config (MySQL: promoteri / root / root)
├── .env.example                 # Template
├── artisan
├── composer.json / composer.lock
├── package.json / package-lock.json
└── vite.config.js
```

---

## Installation

### 1. Prerequisites

- PHP **8.2+** with extensions: `gd`, `mbstring`, `pdo_mysql`, `bcmath`, `xml`, `curl`, `zip`
- Composer 2.x
- Node.js 20+ & npm 10+
- MySQL 8 / MariaDB 10.6+  *(or SQLite for tests)*
- Optional: a real SMTP server for outbound e-mail

### 2. Clone & install PHP deps

```bash
git clone <repo-url> promoteri
cd promoteri
composer install
cp .env.example .env       # already filled with local MySQL creds by us
php artisan key:generate
```

### 3. Install JS deps (Three.js is included)

```bash
npm install
```

### 4. Create the database

The shipped `.env` expects MySQL on `127.0.0.1:3306` with `root` / `root` and
database `promoteri`.  Create it once:

```bash
mysql -uroot -proot -e "CREATE DATABASE promoteri CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### 5. Migrate & seed

```bash
php artisan migrate --seed
```

This creates two admin accounts:

| E-mail                    | Password              | Role  |
| ------------------------- | --------------------- | ----- |
| `superadmin@blank.com`    | `supremeAdminXOXO`    | admin |
| `refestrs@gmail.com`      | `Refestcar123***`     | admin |

### 6. Storage symlink

```bash
php artisan storage:link
```

### 7. Build front-end assets

```bash
npm run build        # production
# or
npm run dev          # hot reload
```

---

## Configuration

### `.env` highlights

| Key                   | Dev value                   | Notes |
| --------------------- | --------------------------- | ----- |
| `APP_NAME`            | `REFEST Festival`           |       |
| `APP_ENV`             | `local`                     | `production` in real deployments |
| `APP_DEBUG`           | `true`                      | **Never** `true` in production |
| `APP_URL`             | `http://localhost:8000`     | Set to your domain in production |
| `APP_LOCALE`          | `sr`                        | Serbian Cyrillic default |
| `DB_CONNECTION`       | `mysql`                     |       |
| `DB_HOST` / `DB_PORT` | `127.0.0.1` / `3306`        |       |
| `DB_DATABASE`         | `promoteri`                 |       |
| `DB_USERNAME/PASSWORD`| `root` / `root`             | Change before deploying! |
| `SESSION_DRIVER`      | `database`                  |       |
| `CACHE_STORE`         | `database`                  |       |
| `QUEUE_CONNECTION`    | `database`                  |       |
| `BROADCAST_CONNECTION`| `reverb`                    | Real-time order feed |
| `MAIL_MAILER`         | `log`                       | Switch to `smtp` for real mail |
| `REVERB_*`            | `797837` / `…` / `…` / `localhost` / `8080` | |

> ⚠ The previous `.env` checked into the repo (now replaced) contained
> production SMTP credentials.  See `bugs.md` for the full audit.

### Generating a new application key

```bash
php artisan key:generate --force
```

---

## Database Schema

The schema is small but expressive:

| Table                  | Purpose                                                  |
| ---------------------- | -------------------------------------------------------- |
| `users`                | Login + role + parent_id (sub-promoter linkage) + `paid` balance |
| `password_reset_tokens`| Breeze                                                   |
| `sessions`             | DB-backed sessions                                       |
| `cache` / `cache_locks`| Application cache                                        |
| `jobs` / `job_batches` / `failed_jobs` | Queue |
| `ticket_types`         | Catalog of tickets (name, price, photo, QR coords)       |
| `ticket_commissions`   | Commission tiers (versioned)                             |
| `ticket_orders`        | Order header                                             |
| `ticket_order_items`   | Order lines                                              |
| `tickets`              | One row per individual ticket (unique code)              |

Total migrations: **9**.

---

## Running the App

In four terminals (or use `composer dev`):

```bash
# Terminal 1 — web server
php artisan serve --host=127.0.0.1 --port=8000

# Terminal 2 — Vite dev server (optional)
npm run dev

# Terminal 3 — Queue worker (needed for ticket image + email jobs)
php artisan queue:work --tries=1

# Terminal 4 — Reverb WebSocket server (needed for real-time admin feed)
php artisan reverb:start
```

Or all-in-one (defined in `composer.json`):

```bash
composer dev
```

---

## Background Workers & Queues

The system relies on the `database` queue.  The worker processes three kinds of
job:

1. **`GenerateTicketImagesJob`** (per order) — generates the QR PNG and the
   composited ticket image.  Timeout `300` seconds.  On success → status
   `completed`.  On exception → status `failed` and a `failed_jobs` row is
   inserted.
2. **`SendCustomerTicketsEmailJob`** (per order) — sends the customer e-mail
   with the ZIP of generated images.  Triggered by `OrderCompleted`.
3. **`OrderCompleted`** — actually an event, broadcast over Reverb.

Failed jobs are surfaced in `failed_jobs` and can be re-driven from
`php artisan queue:retry all` or via the admin UI buttons
(`/orders/{order}/rerun-image-job`, `/orders/{order}/rerun-email-job`).

---

## Internationalisation

Two locales ship: **`en`** and **`sr`** (Serbian Cyrillic).  The default is `sr`.
Strings live in `lang/<locale>/<group>.php` (e.g. `lang/sr/login.php`,
`lang/en/admin_orders.php`).

To add a locale:

1. Create `lang/<locale>/` mirroring the file structure of `lang/en/`.
2. Add the locale to `config/app.php` → `available_locales` (if you customise it).
3. Set `APP_LOCALE=<locale>` in `.env` or expose a language switcher.

---

## API Endpoints

There is no public REST API.  Every page is rendered server-side via Livewire
or Blade.

The legacy `/karte` listing remains for backward-compatibility but is now
**protected**:

| Method | URL      | Purpose                                              | Auth                                                |
| ------ | -------- | ---------------------------------------------------- | --------------------------------------------------- |
| GET    | `/karte` | Returns active ticket codes grouped by ticket-type.  | `admin` or `promoter` only (401/403 for everyone else). |

For a full HTTP route map, run `php artisan route:list` — the routes are
generated entirely from `routes/web.php` + `routes/auth.php`.

---

## Landing Page (Three.js / WebGL)

`resources/views/welcome.blade.php` now hosts an interactive **Three.js** scene:

- A full-screen `<canvas>` rendered by a WebGL fragment shader / 3-D mesh.
- Mouse-parallax camera, idle rotation, dynamic glow.
- Subtle particle field representing floating festival lights.
- A central CTA button that links to `/login`.
- Falls back gracefully to the static logo when WebGL is unavailable.

The scene lives in `resources/js/landing/scene.js` and is loaded lazily via
`resources/js/app.js`.  Build the bundle with `npm run build`.

---

## Testing

```bash
php artisan test
```

The PHPUnit config (`phpunit.xml`) automatically uses an **in-memory SQLite**
database and a `sync` queue, so test runs are fully isolated and need no
MySQL.

Tests live under `tests/Unit/` and `tests/Feature/`.

---

## Deployment Notes

1. **Set `APP_ENV=production`** and **`APP_DEBUG=false`** in `.env`.
2. Rotate **all** secrets (database, mail, Reverb) and put them behind your
   secret manager — never commit them.  The repo's `.gitignore` already
   excludes `.env`, but the previous version had been committed by mistake
   (see `bugs.md`).
3. Run `php artisan config:cache route:cache view:cache`.
4. Set up a persistent queue worker (e.g. **systemd** unit running
   `php artisan queue:work --tries=1`).
5. Set up Reverb behind nginx on port 443 (`wss://`) — see the official
   [Reverb docs](https://laravel.com/docs/12.x/reverb).
6. Make sure `storage/`, `bootstrap/cache/`, and `public/build/` are writable.
7. Configure cron for `php artisan schedule:run` (Laravel scheduler).

---

## Roadmap

- [x] **Multi-festival support** — `festivals`, `festival_user`, festival-scoped
      routes & middleware, festival selector in the sidebar.
- [x] **Role enum corrected** — expanded to include `superadmin` (BUG-DB-001).
- [x] **`/karte` endpoint protected** — requires `admin` or `promoter`.
- [x] **`/dashboard` redirect** — routes every role to the right landing page
      (BUG-AUTH-002 fixed).
- [x] **`SubPromoterController::dashboard()`** — now renders with festival context.
- [x] **Driver-aware migrations** — `expand_users_role_enum` works on MySQL,
      SQLite and PostgreSQL.
- [ ] Real public REST/JSON API (`/api/v1/orders`, `/api/v1/tickets`) with token auth
- [ ] Self-service promoter registration flow with e-mail verification
- [ ] Stripe / card payment integration
- [ ] Customer-facing ticket download portal (signed URL)
- [ ] i18n switcher (live)
- [ ] Mobile-app-friendly API tokens
- [ ] Remove the legacy duplicate `OrderController1.php`
- [ ] Tests for `User::calculateCommission` (tiering edge cases)

---

## Known Issues

See **[bugs.md](./bugs.md)** for the complete, prioritised backlog.

Critical items (✅ = fixed in this release line):

- ✅ ~~Public `/karte` endpoint exposes every ticket code without auth.~~
- ✅ ~~Inconsistent role enum~~ (BUG-DB-001) — fixed via
  `2026_06_19_120003_expand_users_role_enum.php`.
- ✅ ~~`Login::login()` ignored `sub_promoter`~~ (BUG-AUTH-002).
- ✅ ~~`SubPromoterController` was a stub~~ — `dashboard()` now renders.
- ⚠ ~~Dead `Ticket::ticketsSold()`~~ — removed.
- 🚨 **Production SMTP and DB credentials were committed in `.env`.** Rotate
  immediately in any environment that used the historical `.env`.
- ⚠ Duplicate controller (`OrderController1.php`) — safe to delete manually
  (no autoloader / route references it).
- ⚠ Welcome page used to be just a clickable image (now replaced).

---

## License

This project is private to **REFEST Festival**.  All rights reserved.