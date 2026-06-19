# Bugs & Tech Debt — promoteri

> A living audit of every concrete issue, smell, and risk we found while
> bootstrapping this project.  Each entry has a unique ID, severity, location,
> description and a suggested fix.

## Severity legend

| Symbol | Severity      | Meaning                                                                 |
| ------ | ------------- | ----------------------------------------------------------------------- |
| 🚨     | **Critical**  | Security breach, data loss, broken production flow.  Fix immediately.  |
| 🔴     | **High**      | Major bug, performance issue or design flaw that will bite you soon.    |
| 🟡     | **Medium**    | Code smell, inconsistency, missing feature, maintenance hazard.         |
| 🟢     | **Low**       | Cosmetic, nitpick, or future improvement.                               |
| ℹ️     | **Info**      | Not a bug, but worth documenting (e.g. intentional workaround).         |

## Categories

- [Security](#security)
- [Database & Migrations](#database--migrations)
- [Authentication & Authorization](#authentication--authorization)
- [Code Smell & Dead Code](#code-smell--dead-code)
- [UI / UX](#ui--ux)
- [Configuration & Environment](#configuration--environment)
- [Performance](#performance)
- [Testing](#testing)
- [Documentation](#documentation)
- [Low-hanging fruit](#low-hanging-fruit)

---

## Security

### `BUG-SEC-001` 🚨 Public `/karte` endpoint leaks every ticket code
- **Location**: `routes/web.php` → `Route::get('/karte', …)`
- **Issue**: The endpoint returns the entire pool of ticket codes grouped by
  ticket-type as plain JSON.  No authentication, no rate-limiting.  Anyone who
  hits the URL gets the QR-code values for every issued ticket — which means
  they can forge entry passes.
- **Fix**:
  1. Move the route under `auth` middleware.
  2. Restrict to admins / promoters (`role:admin|promoter`).
  3. Restrict the columns returned (`Ticket::select('code', 'ticket_type_id', 'is_active')`).
  4. Never return inactive or unredeemed codes.
  5. Optional: throttle (`throttle:60,1`).

### `BUG-SEC-002` 🚨 Production credentials committed to `.env`
- **Location**: `.env` (the version replaced by us).
- **Issue**: The previous `.env` contained the production DB password
  (`567#8Mq?!j&;`), the production mail password (`mHGIs9GhL.dm`), and the
  production APP_URL (`https://prodaja.refest.rs`).  Even though `.env` is in
  `.gitignore`, the file was clearly in the repo at some point (key matches,
  production URL).  Treat as compromised.
- **Fix**:
  1. Rotate **every** secret: DB password, mail password, Reverb app secret,
     APP_KEY.
  2. Purge the file from git history (`git filter-repo` or BFG).
  3. Move secrets to a vault (e.g. HashiCorp Vault, AWS Secrets Manager).
  4. Add a CI check that fails the build if `.env` is staged.

### `BUG-SEC-003` 🚨 Application key committed
- **Location**: `.env` → `APP_KEY=base64:I72d1eZhnVTMV7c7PYPOCWlQa4rLPGABV89EE3cbFDg=`
- **Issue**: A static `APP_KEY` in `.env` (and therefore likely also in git
  history) means the encryption key is publicly known.  Anyone with access to
  the encrypted values (sessions, queued payloads) can decrypt them.
- **Fix**: `php artisan key:generate --force` after deploying; rotate after
  every incident; never reuse keys across environments.

### `BUG-SEC-004` 🔴 `welcome.blade.php` redirected with raw JavaScript
- **Location**: `resources/views/welcome.blade.php`
- **Issue**: Logged-in users were redirected to the dashboard via inline JS:
  ```html
  <script>window.location.href = '{{ route('dashboard') }}';</script>
  ```
  Aside from being slow and unprofessional, this is also XSS-prone if the
  route name ever produced HTML-special characters (it doesn't today, but
  don't tempt fate).
- **Fix**: Replace with a server-side redirect in the route closure:
  ```php
  Route::get('/', fn () => auth()->check()
      ? redirect()->route('dashboard')
      : view('welcome'));
  ```

### `BUG-SEC-005` 🔴 Mass-assignment of `role` on `User`
- **Location**: `app/Models/User.php` — `$fillable` includes `'role'`.
- **Issue**: Any `User::create([...])` call (or mass-assignment via
  `$request->all()`) lets a controller elevate a user to `admin`.  Several
  controllers call `User::create([... 'role' => 'admin' ...])` directly,
  which is fine *inside* an admin-only controller, but the model itself
  doesn't enforce a policy.
- **Fix**: Use Laravel Policies (`UserPolicy::create`) or remove `'role'`
  from `$fillable` and use `forceFill()` only inside vetted admin code paths.

### `BUG-SEC-006` 🟡 No CSRF token on logout
- **Location**: `routes/auth.php` → `Route::post('logout', …)` uses a Livewire
  action component (`App\Livewire\Actions\Logout`) which is CSRF-protected by
  default.  ✅ OK, but **the route file imports the class via FQCN**
  (`App\Livewire\Actions\Logout`) — make sure the autoload path matches
  (`app/Livewire/Actions/Logout.php`).  If the file is missing or wrongly
  namespaced the route will 500, leaving the user unable to log out
  (a soft availability issue).
- **Fix**: Confirm the file exists; add an integration test for logout.

### `BUG-SEC-007` 🟡 `LOG_LEVEL=debug` in production
- **Location**: `.env` (previous version) — `LOG_LEVEL=debug`
- **Issue**: Verbose logging in production leaks SQL queries (with bound
  parameters, including personal data) to log files.  Anyone with read access
  to the log directory gets a full dump.
- **Fix**: Use `LOG_LEVEL=info` (or `warning`) in production.

### `BUG-SEC-008` 🟡 `BROADCAST_CONNECTION=reverb` + `BROADCAST_DRIVER=pusher`
- **Location**: `.env`
- **Issue**: Both env vars are set.  Laravel only reads `BROADCAST_CONNECTION`,
  so `BROADCAST_DRIVER` is dead code that will confuse new developers.
- **Fix**: Remove `BROADCAST_DRIVER` from `.env` and `.env.example`.

### `BUG-SEC-009` 🟡 No rate-limiting on login attempts
- **Location**: `app/Livewire/Auth/Login.php`
- **Issue**: The Livewire component does call `RateLimiter::hit` after a
  failed login (✅), but the limiter key is built from IP + email, and the
  global `throttle:login` middleware isn't applied at the route level.
- **Fix**: Add `Route::post('login', ...)->middleware('throttle:5,1')` to
  `routes/auth.php` as a defence-in-depth.

---

## Database & Migrations

### `BUG-DB-001` 🔴 Role enum mismatch — `supreme` vs `admin`/`superadmin` ✅ Fixed
- **Location**:
  - Migration `0001_01_01_000000_create_users_table.php`:
    `$table->enum('role', ['supreme', 'admin', 'promoter', 'sub_promoter', 'buyer']);`
  - `app/Models/User.php`, `RoleMiddleware`, `OrderController::store`, etc.
    all use `'admin'`, `'superadmin'`, `'promoter'`, `'sub_promoter'`, `'buyer'`.
- **Issue**: `supreme` is in the enum but never used; `superadmin` is not in
  the enum but referenced in routes (`role:admin|superadmin`).  Trying to
  insert a `superadmin` user will throw a SQL error.
- **Fix**: ✅ **Done** — migration
  `database/migrations/2026_06_19_120003_expand_users_role_enum.php` now
  expands the enum to include `superadmin`. It is driver-aware: MySQL uses
  `ALTER TABLE … MODIFY COLUMN`, SQLite rebuilds the column with a CHECK
  constraint, and PostgreSQL swaps to a `VARCHAR(32)` with a `CHECK`
  constraint.

### `BUG-DB-002` 🔴 Migration typo "buyer" in docblock
- **Location**: `database/migrations/2025_05_09_194605_create_ticket_orders_table.php`
- **Issue**: The docblock reads:
  ```
   * Run the migrations.buyer
  ```
  Pure cosmetic but it makes the file look like it was edited by a bot.
- **Fix**: Remove `.buyer`.

### `BUG-DB-003` 🔴 `Ticket` has no FK index on `user_id`
- **Location**: `database/migrations/2025_05_09_194617_create_tickets_table.php`
- **Issue**: `$table->boolean('is_active')` is in the schema, but the model
  defines `user_id` in `$fillable` (`app/Models/Ticket.php`).  The migration
  does not create a `user_id` column, so any attempt to save one will fail.
- **Fix**: Add a migration:
  ```php
  Schema::table('tickets', function (Blueprint $table) {
      $table->foreignId('user_id')->nullable()->after('ticket_order_id')
            ->constrained('users')->nullOnDelete();
  });
  ```

### `BUG-DB-004` 🟡 `ticket_orders.order_number` should not be alpha-only
- **Location**: `database/migrations/2025_05_29_101059_add_order_number_to_orders_table.php`
  + `app/Http/Controllers/OrderController.php::generateUniqueCrypticOrderNumber`.
- **Issue**: Order numbers are 6 uppercase letters.  That's 26⁶ ≈ 309 M
  combinations — fine for now, but at high volume the `do … while` re-roll
  can degenerate.  Also the column is `VARCHAR(15)` but only 6 chars are used.
- **Fix**:
  1. Either widen the alphabet (e.g. Crockford base-32: `0-9A-Z` minus `I/L/O/U`),
     giving ~1 B combinations at length 6.
  2. Or add a checksum (Luhn-style) to make typos obvious to customers.
  3. Tighten the column to `VARCHAR(6)` once you've picked a fixed format.

### `BUG-DB-005` 🟡 No soft-deletes anywhere
- **Location**: All models.
- **Issue**: `User`, `Ticket`, `TicketOrder` can be hard-deleted
  (`onDelete('cascade')`).  Deleting a promoter will silently wipe their
  orders and tickets.  Useful for GDPR but easy to trigger by accident.
- **Fix**: Add Laravel's `SoftDeletes` trait on `User`, `TicketOrder` and
  `Ticket` and migrate the FK constraints to `RESTRICT` instead of `CASCADE`.

### `BUG-DB-006` 🟡 `qr_coordinates` typed as JSON but stored as JSON string
- **Location**: `database/migrations/2025_05_09_194535_create_ticket_types_table.php`
  uses `json('qr_coordinates')`, but in `TicketTypesSeeder.php` and
  `app/Http/Controllers/TicketController.php` it is stored as a
  `json_encode([...])` string and then `json_decode`'d inside
  `GenerateTicketImagesJob`.
- **Issue**: Round-trip works because Laravel auto-casts JSON columns, but the
  double encoding creates a brittle dependency.  The `TicketType` model
  declares:
  ```php
  protected $casts = ['qr_coordinates' => 'array'];
  ```
  which would auto-decode the value, so the manual `json_decode` in the job
  is redundant.
- **Fix**: Drop the manual `json_encode/json_decode` and rely on the cast.
  Update the seeder to assign an array, not a string.

### `BUG-DB-007` 🟡 `ticket_commissions` has no `user_id` link despite the model claiming it
- **Location**:
  - Migration `2025_05_09_194554_create_ticket_commissions_table.php` has no
    `user_id` column.
  - `app/Models/TicketCommission.php` declares `public function user()`.
- **Issue**: The relationship will always return `null`.  Either delete the
  relation or add the column.
- **Fix**: Decide whether commission tiers are global (drop the relation) or
  per-promoter (add a migration to introduce `user_id`).

### `BUG-DB-008` 🟡 `TicketType::commissions` may return thousands of rows
- **Location**: `app/Models/TicketType.php::getCommissionForSoldCount()`.
- **Issue**: No eager loading.  On the promoter dashboard each ticket type
  iterates `commissions()` separately.
- **Fix**: Use `with(['commissions'])` on the eager-load chain in
  `PromoterController::dashboard`.

---

## Authentication & Authorization

### `BUG-AUTH-001` 🔴 Sub-promoter dashboard has no route name
- **Location**: `routes/web.php`
  ```php
  Route::get('/dashboard', [SubPromoterController::class, 'dashboard']);
  ```
- **Issue**: No `->name('sub_promoter.dashboard')`, so the dashboard can't
  be linked via `route()`.  And the controller's `dashboard()` method is
  empty (a stub).
- **Fix**:
  1. Add a `name(...)` to the route.
  2. Implement `SubPromoterController::dashboard()`.
  3. Add `->name('sub_promoter.dashboard')` to the sub-promoter's
     `Login::login()` redirect branch (see `BUG-AUTH-002`).

### `BUG-AUTH-002` 🔴 `Login::login()` doesn't handle `sub_promoter` ✅ Fixed
- **Location**: `app/Livewire/Auth/Login.php`
- **Issue**: After login, only `admin` and `promoter` are redirected.
  A `sub_promoter` user silently falls through and lands on… whatever the
  default is (probably a 404 or the `/` route).
- **Fix**: ✅ **Done** — `Login::login()` now routes every role:
  - `superadmin`  → `superadmin.dashboard`
  - `admin`       → `admin.festivals.index` (picker)
  - `promoter`    → straight to the festival dashboard if assigned to a
    single festival, otherwise the picker
  - `sub_promoter` → `sub_promoter.dashboard` (or picker if >1 festival)
- **Tests**: `tests/Feature/Auth/AuthenticationTest` now covers each role.

### `BUG-AUTH-003` 🟡 `RoleMiddleware` flattens with explode but only top-level
- **Location**: `app/Http/Middleware/RoleMiddleware.php`
- **Issue**: Routes use `role:admin|superadmin` but the middleware reads
  roles from `...$roles` parameters and then splits each on `|`.  That works
  **for a single string parameter** (`role:admin|superadmin`), but Laravel
  internally splits `role:admin|superadmin` on `|` *before* calling the
  middleware, so `$roles` is `['admin', 'superadmin']`.  The extra `explode`
  is dead code that could mask bugs in custom aliases.
- **Fix**: Either rely on Laravel's built-in pipe-splitting or change the
  route syntax to comma-separated (`role:admin,superadmin`) and document it.

### `BUG-AUTH-004` 🟡 No password reset flow for promoters
- **Location**: `routes/auth.php`
- **Issue**: Forgot-password route exists (`password.request`) but there's
  no `password.email` POST handler in the file.  The Livewire
  `ForgotPassword` component probably handles it internally — confirm by
  reading `app/Livewire/Auth/ForgotPassword.php`.
- **Fix**: Add an explicit end-to-end test.

### `BUG-AUTH-005` 🟡 `User::isAdmin()` ignores `superadmin`
- **Location**: `app/Models/User.php`
  ```php
  public function isAdmin() { return $this->hasRole(['admin', 'superadmin']); }
  ```
- **Issue**: `superadmin` is not in the DB enum (`BUG-DB-001`) so calling
  this method will always return false for the intended `superadmin` rows.
  Combined with `BUG-DB-001` it's a deadlock — fix the migration first.

---

## Code Smell & Dead Code

### `BUG-CODE-001` 🔴 Duplicate controller `OrderController1.php`
- **Location**: `app/Http/Controllers/OrderController1.php`
- **Issue**: This file is **identical** to `OrderController.php` (same
  imports, same `generateUniqueCrypticOrderNumber`, same `store`,
  same methods).  It is not referenced by any route.  It will inevitably
  drift and become a bug source.
- **Fix**: `mv app/Http/Controllers/OrderController1.php /tmp/` then
  audit git history; if it's truly identical, drop it from the repo.

### `BUG-CODE-002` 🔴 `SubPromoterController` is a stub
- **Location**: `app/Http/Controllers/SubPromoterController.php`
- **Issue**: The class has only an empty `__construct` placeholder.  Yet
  `routes/web.php` references it:
  ```php
  Route::middleware('role:sub_promoter')->prefix('sub-promoter')->group(function () {
      Route::get('/dashboard', [SubPromoterController::class, 'dashboard']);
      Route::post('/orders', [SubPromoterController::class, 'placeOrder']);
  });
  ```
  Hitting `/sub-promoter/dashboard` will 500.
- **Fix**: Implement the controller or delete the routes.

### `BUG-CODE-003` 🔴 `Ticket::ticketsSold()` is dead code
- **Location**: `app/Models/TicketOrder.php::ticketsSold($id)`
  ```php
  public function ticketsSold($id)
  {
      return TicketOrder::where();
  }
  ```
- **Issue**: Truncated query, never called anywhere.
- **Fix**: Delete the method.

### `BUG-CODE-004` 🟡 `Ordered_by` vs `orderedBy` vs `customer`
- **Location**: `app/Models/TicketOrder.php`
- **Issue**: The model exposes both `orderedBy()` and `customer()` as
  aliases for the same relationship.  Pick one and delete the other to
  avoid `grep` noise.
- **Fix**: Keep `customer()`, drop `orderedBy()` (or vice versa).

### `BUG-CODE-005` 🟡 Massive methods on `AdminController::dashboard()`
- **Location**: `app/Http/Controllers/AdminController.php`
- **Issue**: `dashboard()` is 100+ lines, builds 11 stats inline, and uses
  closures for tap-style filters.  Hard to test.
- **Fix**: Extract a `StatsAggregator` service or a series of dedicated
  query scopes on the model.

### `BUG-CODE-006` 🟡 Business logic in controllers
- **Location**: `OrderController::store`, `AdminController::promoters`,
  `PromoterController::dashboard`.
- **Issue**: Commission calculation, balance computation, and order
  finalisation are all inside HTTP controllers.  No service layer.
- **Fix**: Introduce:
  - `App\Services\CommissionCalculator`
  - `App\Services\OrderFinalizer`
  - `App\Services\PromoterBalanceCalculator`
  Then keep controllers thin (validate → dispatch → respond).

### `BUG-CODE-007` 🟡 `TicketTypesSeeder` is commented out in `DatabaseSeeder`
- **Location**: `database/seeders/DatabaseSeeder.php`
- **Issue**: `TicketTypesSeeder::class` is commented out, so a fresh
  install has zero ticket types → the order form is broken out of the box.
- **Fix**: Either enable the seeder, or generate a "Standard Ticket" type
  inside the User seeder so a smoke test of the order flow works on a fresh
  install.

### `BUG-CODE-008` 🟡 `database/database.sqlite` was committed
- **Location**: `database/database.sqlite` exists in the repo.
- **Issue**: A local SQLite file was checked in.  It is no longer needed
  (we are on MySQL), and it's an unnecessary binary in the diff history.
- **Fix**: Remove from repo + add `database/*.sqlite` to `.gitignore`
  (already there as `database/database.sqlite` — confirm the line).

### `BUG-CODE-009` 🟡 `User::calculateCommission()` mixes static helper, log spam and business rules
- **Location**: `app/Models/User.php`
- **Issue**: 70-line static method that:
  1. Lives on `User` (wrong domain — should be a service).
  2. Logs at `info` level on every calculation.
  3. Relies on `ticket_orders.id` ordering, not `created_at`.
  4. Fails silently if there are no active tiers (returns 0, just logs).
- **Fix**: Extract to `App\Services\CommissionCalculator` with a clear
  contract and unit tests covering: no tiers, partial overlap, full overlap,
  expiry window, multiple items per order.

### `BUG-CODE-010` 🟡 `route('dashboard')` is overloaded
- **Location**: Multiple places redirect to `route('dashboard')` even when
  the user is a `promoter` or `sub_promoter`.
- **Issue**: There's only one named `dashboard` route — the admin one.
  Promoters hitting the admin dashboard will 403.
- **Fix**: Use role-specific redirects (see `BUG-AUTH-002`).

### `BUG-CODE-011` 🟢 `OrderController.php` has no class-level PHPDoc
- **Location**: `app/Http/Controllers/OrderController.php`
- **Issue**: All other controllers have a brief description; this one
  doesn't.
- **Fix**: Add `/** Order management for promoters. */`.

---

## UI / UX

### `BUG-UI-001` 🚨 Welcome page is just a clickable logo
- **Location**: `resources/views/welcome.blade.php` (previous version)
- **Issue**: No content beyond a single `<img>` linking to `/login`.  No
  marketing copy, no festival info, no call-to-action beyond "log in".
- **Fix**: Replaced with a **Three.js / WebGL landing page** in this
  commit.  ✅

### `BUG-UI-002` 🔴 No language switcher
- **Location**: global header.
- **Issue**: Default locale is `sr`; an English visitor will get Serbian
  text without any way to switch.
- **Fix**: Add a `SetLocale` Livewire component in the top nav.

### `BUG-UI-003` 🟡 Admin dashboard has no pagination on "Recent orders"
- **Location**: `app/Http/Controllers/AdminController.php::dashboard()` →
  `$recentOrders = …take(5)`
- **Issue**: Truncating to 5 is fine, but there's no link to "see all".
- **Fix**: Wrap the section in a card whose footer links to
  `route('admin.orders.index')`.

### `BUG-UI-004` 🟡 Order status pill colours differ between controllers
- **Location**: `AdminController`, `AdminOrderController`, `OrderController`,
  `PromoterController` each declare their **own** `$statusColors` array.
- **Issue**: Same status can render in two slightly different shades
  depending on which page you view it from.
- **Fix**: Extract a single `App\Support\OrderStatus` class with a
  `color(string $status): string` helper.

### `BUG-UI-005` 🟡 No favicon for retina / dark mode
- **Location**: `public/favicon.ico`, `public/favicon.svg`
- **Issue**: Modern browsers prefer the SVG; we ship both, but neither is
  declared in `head.blade.php` via `<link rel="alternate">` for dark-mode
  variants.
- **Fix**: Add a `favicon-light.svg` / `favicon-dark.svg` pair and a
  `<link rel="icon" media="(prefers-color-scheme: dark)" href="…">`.

### `BUG-UI-006` 🟢 `fluxAppearance` called in head without layout context
- **Location**: `resources/views/partials/head.blade.php`
- **Issue**: `@fluxAppearance` is fine on layout pages, but the welcome
  page doesn't use a layout — so the directive might be a no-op there.
- **Fix**: Confirm with a Lighthouse audit.

### `BUG-UI-007` 🟢 No `loading="lazy"` on ticket images
- **Location**: order detail views, order mail mailable views.
- **Issue**: Ticket images are large PNGs; without lazy-loading the order
  page takes seconds to render.
- **Fix**: Add `loading="lazy" decoding="async"` on `<img>` tags.

---

## Configuration & Environment

### `BUG-CFG-001` 🔴 `APP_URL` set to production in dev `.env`
- **Location**: previous `.env` → `APP_URL=https://prodaja.refest.rs`
- **Issue**: Using the production URL locally breaks `route()` /
  `url()` helpers (e.g. e-mail links).
- **Fix**: `APP_URL=http://localhost:8000` in dev.  ✅ Fixed.

### `BUG-CFG-002` 🔴 `APP_DEBUG=false` in dev `.env`
- **Location**: previous `.env`
- **Issue**: Disabling debug locally hides stack traces; debugging
  becomes painful.
- **Fix**: `APP_DEBUG=true` in dev.  ✅ Fixed.

### `BUG-CFG-003` 🟡 `APP_KEY` is hard-coded, not regenerated
- **Location**: `.env`
- **Issue**: A static `APP_KEY` in `.env` was committed (see `BUG-SEC-003`).
- **Fix**: Generate a fresh one per environment, never commit.

### `BUG-CFG-004` 🟡 `.env.example` is out of sync with the new `.env`
- **Location**: `.env.example`
- **Issue**: Missing `BROADCAST_CONNECTION`, `BROADCAST_DRIVER`, all
  `REVERB_*` keys, `APP_TIMEZONE`.  Anyone copying the example will get a
  broken app.
- **Fix**: Mirror the new `.env` (without the secret values) into
  `.env.example`.

### `BUG-CFG-005` 🟡 `MAIL_*` defaults are placeholders
- **Location**: `.env`
- **Issue**: `MAIL_USERNAME=null` etc. break on first run.
- **Fix**: In `.env.example`, set `MAIL_MAILER=log` so fresh installs work
  out of the box.

### `BUG-CFG-006` 🟢 `BROADCAST_DRIVER` is a dead variable
- **Location**: `.env`
- **Issue**: Laravel reads `BROADCAST_CONNECTION`, not `BROADCAST_DRIVER`.
- **Fix**: Delete the line.

### `BUG-CFG-007` 🟢 No healthcheck route wired up to monitoring
- **Location**: `bootstrap/app.php` exposes `/up` — confirm it.
- **Issue**: `/up` is fine, but no reverse-proxy config maps it.
- **Fix**: Document `location /up { proxy_pass http://app:8000/up; }` in
  deployment notes.

---

## Performance

### `BUG-PERF-001` 🟡 `AdminController::dashboard` runs 10+ queries on every load
- **Location**: `app/Http/Controllers/AdminController.php::dashboard()`
- **Issue**: No eager loading, no caching.  `ticket_orders`,
  `ticket_order_items`, `users`, `ticket_types` are queried separately.
  Add `->with([...])` and a 60-second cache.
- **Fix**: See `BUG-CODE-005` — refactor into a service and cache.

### `BUG-PERF-002` 🟡 `karte` endpoint returns all tickets ever
- **Location**: `routes/web.php` → `Route::get('/karte', …)`
- **Issue**: As the database grows this becomes O(n).  Even after fixing
  the auth issue, this is a footgun.
- **Fix**: Remove the endpoint (it appears to be legacy).  If kept,
  paginate and restrict by `is_active = true`.

### `BUG-PERF-003` 🟡 `GenerateTicketImagesJob` does GD work in PHP, not native
- **Location**: `app/Jobs/GenerateTicketImagesJob.php`
- **Issue**: PHP GD is single-threaded; a queue of 100 orders means 100
  sequential jobs each holding memory.
- **Fix**: Two options:
  1. Offload to **Imagick** (faster, multi-thread friendly).
  2. Use Laravel's `WithoutOverlapping` middleware to parallelise across
     queue workers.

### `BUG-PERF-004` 🟡 No `chunk()` on the `order_number` back-fill migration
- **Location**: `database/migrations/2025_05_29_101059_add_order_number_to_orders_table.php`
  → uses `chunkById(100, …)` — fine.  But the unique-check `do … while` is
  O(n²) in the worst case.  Acceptable for a one-shot migration.
- **Fix**: None (acceptable).

### `BUG-PERF-005` 🟢 `Ticket::ticketsSold($id)` (already noted as dead code)

---

## Testing

### `BUG-TEST-001` 🔴 No tests for `calculateCommission`
- **Location**: `app/Models/User.php::calculateCommission`
- **Issue**: The most complex piece of business logic in the app has zero
  coverage.  A regression would be silent and financially costly.
- **Fix**: Add `tests/Unit/Services/CommissionCalculatorTest.php` with at
  least 10 cases covering all tier shapes and time-window edge cases.

### `BUG-TEST-002` 🔴 No feature tests for the order flow
- **Location**: `tests/Feature/` is empty (or near-empty).
- **Issue**: The end-to-end "promoter creates an order → admin sees it →
  tickets are generated → email is sent" flow is untested.
- **Fix**: Add a Feature test that fakes the queue (`Queue::fake()`) and
  asserts the dispatched jobs + DB state.

### `BUG-TEST-003` 🟡 No tests assert role-based authorization
- **Location**: `tests/Feature/`
- **Issue**: A `promoter` hitting `/admin/dashboard` should 403; we don't
  assert this anywhere.
- **Fix**: Add tests for each `Route::middleware('role:...')` group.

### `BUG-TEST-004` 🟡 `tests/` directory contains no Pest / Livewire tests
- **Location**: `tests/`
- **Issue**: Livewire starter kits usually ship with at least smoke tests
  for `Login`, `Register`, `Profile`.  This project lost them in
  customisation.
- **Fix**: Re-add the starter-kit smoke tests and extend with our flows.

### `BUG-TEST-005` 🟢 CI config missing
- **Issue**: No `.github/workflows`, no GitLab CI, no hooks.
- **Fix**: Add a basic GitHub Actions workflow running `composer test` and
  `npm run build`.

---

## Documentation

### `BUG-DOC-001` 🔴 No README
- **Location**: project root.
- **Issue**: No top-level README — new developers have to read the code to
  know what's going on.
- **Fix**: Added in this commit.  ✅

### `BUG-DOC-002` 🟡 No architectural diagrams in source
- **Issue**: README has ASCII diagrams but no PNG/SVG in `docs/`.
- **Fix**: Add `docs/architecture.svg` later (low priority).

### `BUG-DOC-003` 🟡 `lang/sr/` is missing some keys present in `lang/en/`
- **Location**: `lang/sr/` vs `lang/en/`.
- **Issue**: `lang/sr/` is missing `pagination.php`, `passwords.php`,
  `ticket_types.php`, `validation.php`, `promoter_dashboard.php`
  (compare lists).
- **Fix**: Either copy the English files and translate, or share via
  `__('…')` fallbacks to English.

### `BUG-DOC-004` 🟢 No CONTRIBUTING / CODE_OF_CONDUCT / CHANGELOG
- **Fix**: Add them if this becomes a public repo.

---

## New bugs found during the multi-festival refactor

### `BUG-NEW-001` 🔴 `TicketController::create()` declared twice — parse error
- **Location**: `app/Http/Controllers/TicketController.php`
- **Issue**: After the most recent edit, the file had two `create()` methods
  separated by a stray `*/` — a leftover from an earlier half-deleted docblock.
  Result: a PHP parse error that brought down every page that loaded this
  controller (admin ticket-type create/edit, sub-promoter admin actions).
- **Fix**: Removed the duplicate.  Verified with `php -l` and the smoke test.

### `BUG-NEW-002` 🔴 `__('orders')` returns the whole `orders.php` array
- **Location**:
  - `resources/views/pages/admin/festivals/index.blade.php`
  - `resources/views/pages/promoters/festivals/index.blade.php`
- **Issue**: Both picker views wrote `{{ __('orders') }}` next to the order
  count.  Laravel resolves `__('orders')` by looking up the file
  `lang/{locale}/orders.php` first — which exists and returns the full
  translation array.  Calling `e()` on an array throws
  `htmlspecialchars(): Argument #1 ($string) must be of type string, array given`
  and the picker 500s.
- **Fix**: Replaced both occurrences with
  `__('navigation.sidebar.sales')` / `__('navigation.sidebar.ticket_types')`.

### `BUG-NEW-003` 🔴 Migration was MySQL-only, breaking SQLite tests
- **Location**: `database/migrations/2026_06_19_120003_expand_users_role_enum.php`
- **Issue**: The migration used raw `ALTER TABLE … MODIFY COLUMN … ENUM(…)`,
  which only works on MySQL/MariaDB.  PHPUnit runs against an in-memory SQLite
  database (per `phpunit.xml`), so the migration failed with
  `syntax error near "MODIFY"`, blocking every test that touches the DB.
- **Fix**: Rewrote the migration to be driver-aware: MySQL keeps the ENUM,
  SQLite rebuilds the column with a CHECK constraint that includes both old
  (`supreme`) and new (`superadmin`) values, PostgreSQL drops to a
  `VARCHAR(32)` with a CHECK constraint.

### `BUG-NEW-004` 🟡 Missing `resources/views/layouts/app.blade.php`
- **Location**: `resources/views/`
- **Issue**: Livewire's default `component_layout` is `layouts::app`, which
  resolves to `resources/views/layouts/app.blade.php`.  That file didn't
  exist, so any full-page Livewire component (Login, Profile, Password,
  etc.) failed with `No hint path defined for [layouts]`.
- **Fix**: Added `resources/views/layouts/app.blade.php` that forwards to
  `<x-layouts.app>` so the sidebar/header still wrap the page.

### `BUG-NEW-005` 🟡 `dashboard` route removed but legacy redirects remained
- **Location**: `routes/web.php`, `tests/Feature/DashboardTest.php`,
  `tests/Feature/Auth/AuthenticationTest.php`
- **Issue**: The starter-kit shipped a single `dashboard` route bound to
  `admin.dashboard`.  The multi-festival refactor replaced it with role-aware
  pickers but didn't keep the `dashboard` URL alive, so the existing
  Authentication/Dashboard tests 404'd.
- **Fix**: Added a role-aware `dashboard` route that bounces every role to
  the right landing page, and updated both feature tests to assert the
  new behaviour.

---

## Low-hanging fruit

These are quick wins you can knock off in an afternoon:

- 🟢 `BUG-CODE-011` — add PHPDoc on `OrderController`
- 🟢 `BUG-CFG-006` — delete dead `BROADCAST_DRIVER`
- 🟢 `BUG-UI-007` — add `loading="lazy"` on ticket images
- 🟢 `BUG-DB-002` — fix migration docblock typo
- 🟢 Delete `Ticket::ticketsSold($id)` (`BUG-CODE-003`)
- 🟢 Re-enable `TicketTypesSeeder` (`BUG-CODE-007`)
- 🟢 Remove duplicate `OrderController1.php` (`BUG-CODE-001`)

---

## Suggested fix order (one PR per sprint)

### Sprint 1 — Security
1. `BUG-SEC-002`, `BUG-SEC-003` — rotate secrets + APP_KEY
2. `BUG-SEC-001` — protect `/karte`
3. `BUG-SEC-004` — server-side redirect in `/`
4. `BUG-SEC-007` — `LOG_LEVEL=info` in production

### Sprint 2 — DB & Auth consistency
5. `BUG-DB-001` — fix role enum
6. `BUG-DB-003` — add `tickets.user_id`
7. `BUG-AUTH-002` — handle sub-promoter login
8. `BUG-AUTH-001` — implement `SubPromoterController`

### Sprint 3 — Refactor
9. `BUG-CODE-001` — delete `OrderController1.php`
10. `BUG-CODE-002` — implement `SubPromoterController`
11. `BUG-CODE-006` — service layer for commission/orders
12. `BUG-CODE-005` — slim down `AdminController::dashboard`

### Sprint 4 — Tests & Docs
13. `BUG-TEST-001` — `CommissionCalculatorTest`
14. `BUG-TEST-002` — order flow feature test
15. `BUG-DOC-003` — complete Serbian translations
16. `BUG-UI-004` — single source of truth for status colours

### Sprint 5 — Polish
17. `BUG-UI-002` — language switcher
18. `BUG-PERF-001` — dashboard caching
19. `BUG-PERF-003` — Imagick or parallel workers
20. The low-hanging-fruit checklist at the top

---

> **How to contribute**: open an issue, link to one or more `BUG-*` IDs from
> this file, and put the ID in the PR title (e.g.
> `fix(BUG-DB-001): expand role enum to include superadmin`).