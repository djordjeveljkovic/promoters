<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminOrderController;
use App\Http\Controllers\Admin\MailTemplatePreviewController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PromoterController;
use App\Http\Controllers\SubPromoterController;
use App\Livewire\Admin\MailTemplates\Editor as MailTemplateEditor;
use App\Http\Controllers\Superadmin\DashboardController as SuperadminDashboard;
use App\Http\Controllers\Superadmin\FestivalAssignmentController;
use App\Http\Controllers\Superadmin\FestivalController as SuperadminFestivalController;
use App\Http\Controllers\Superadmin\UserController as SuperadminUserController;
use App\Http\Controllers\TicketController;
use App\Livewire\Admin\OrderDetails;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/* ============================================================
 *  Public
 * ============================================================ */

Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();
        if ($user->isSuperAdmin()) {
            return redirect()->route('superadmin.dashboard');
        }
        return match ($user->role) {
            'admin', 'superadmin' => redirect()->route('admin.festivals.index'),
            'promoter'            => redirect()->route('promoter.festivals.index'),
            default               => redirect()->route('admin.festivals.index'),
        };
    }
    return view('welcome');
});

Route::get('/karte', function () {
    // ⚠ SECURITY: this endpoint used to be public (BUG-SEC-001).
    // It now requires admin or promoter authentication.
    if (!Auth::check()) {
        abort(401);
    }
    if (!Auth::user()->isAdmin() && !Auth::user()->isPromoter()) {
        abort(403);
    }
    $tickets = \App\Models\Ticket::with('ticketType')
        ->where('is_active', true)
        ->get();

    $grouped = $tickets->groupBy(function ($ticket) {
        return strtolower($ticket->ticketType->name ?? 'unknown');
    })->map(function ($group) {
        return $group->pluck('code')->all();
    });

    return response()->json($grouped);
});

/* ============================================================
 *  Superadmin area
 *  Accessible only to users.role = 'superadmin'
 * ============================================================ */

Route::middleware(['auth', 'role:superadmin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/dashboard', [SuperadminDashboard::class, 'index'])->name('dashboard');

    // Festival CRUD
    Route::get('/festivals', [SuperadminFestivalController::class, 'index'])->name('festivals.index');
    Route::get('/festivals/create', [SuperadminFestivalController::class, 'create'])->name('festivals.create');
    Route::post('/festivals', [SuperadminFestivalController::class, 'store'])->name('festivals.store');
    Route::get('/festivals/{festival}/edit', [SuperadminFestivalController::class, 'edit'])->name('festivals.edit');
    Route::put('/festivals/{festival}', [SuperadminFestivalController::class, 'update'])->name('festivals.update');
    Route::delete('/festivals/{festival}', [SuperadminFestivalController::class, 'destroy'])->name('festivals.destroy');

    // Festival assignments (users ↔ festivals)
    Route::get('/festivals/{festival}/assignments', [FestivalAssignmentController::class, 'show'])->name('festivals.assignments');
    Route::post('/festivals/{festival}/assignments', [FestivalAssignmentController::class, 'store'])->name('festivals.assignments.store');
    Route::delete('/festivals/{festival}/assignments/{user}', [FestivalAssignmentController::class, 'destroy'])->name('festivals.assignments.destroy');

    // User management
    Route::get('/users', [SuperadminUserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [SuperadminUserController::class, 'create'])->name('users.create');
    Route::post('/users', [SuperadminUserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [SuperadminUserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [SuperadminUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [SuperadminUserController::class, 'destroy'])->name('users.destroy');

    // Mail templates (superadmin sees every festival's overrides + the globals)
    Route::get('/mail-templates', MailTemplateEditor::class)->name('mail-templates.index');
    Route::get('/mail-templates/preview', MailTemplatePreviewController::class)->name('mail-templates.preview');
});

/* ============================================================
 *  Authenticated area (any role)
 * ============================================================ */

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');
    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');

    /* ----------  Role-aware dashboard redirect ---------- */
    Route::get('dashboard', function () {
        $u = auth()->user();
        return match (true) {
            $u?->isSuperAdmin() => redirect()->route('superadmin.dashboard'),
            $u?->isAdmin()      => redirect()->route('admin.festivals.index'),
            $u?->isPromoter()   => redirect()->route('promoter.festivals.index'),
            $u?->isSubPromoter() => redirect()->route('sub_promoter.dashboard'),
            default             => redirect('/'),
        };
    })->name('dashboard');

    /* ----------  Admin: pick a festival ---------- */
    Route::middleware('role:admin|superadmin')->prefix('admin')->name('admin.')->group(function () {
        // Festival picker — shows the list of festivals the admin can access.
        Route::get('/festivals', [AdminController::class, 'festivalsIndex'])->name('festivals.index');
    });

    /* ----------  Promoter: pick a festival ---------- */
    Route::middleware('role:promoter|sub_promoter|superadmin|admin')->prefix('promoter')->name('promoter.')->group(function () {
        Route::get('/festivals', [PromoterController::class, 'festivalsIndex'])->name('festivals.index');
    });

    /* ----------  Festival-scoped admin area ---------- */
    Route::middleware(['role:admin|superadmin', 'festival.access:admin'])->prefix('admin/festivals/{festival}')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

        // Promoters inside a festival
        Route::get('/promoters', [AdminController::class, 'promoters'])->name('promoters.index');
        Route::get('/promoter/create', [AdminController::class, 'createPromoter'])->name('promoters.create');
        Route::get('/promoter/edit/{id}', [AdminController::class, 'editPromoter'])->name('promoters.edit');
        Route::post('/promoters', [AdminController::class, 'store'])->name('promoters.store');
        Route::put('/promoter/edit/{id}', [AdminController::class, 'updatePromoter'])->name('promoters.update');
        Route::delete('/promoter/{id}', [AdminController::class, 'deletePromoter'])->name('promoters.destroy');

        // Orders inside a festival
        Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', OrderDetails::class)->name('orders.show');
        Route::post('/orders/{order}/download-qrcodes', [AdminOrderController::class, 'downloadQRCodes'])->name('orders.downloadQRCodes');
        Route::put('/orders/{order}/update-payment', [AdminOrderController::class, 'updatePayment'])->name('orders.updatePayment');
        Route::get('/order/create', [AdminOrderController::class, 'create'])->name('orders.create');
        Route::post('/orders', [AdminOrderController::class, 'store'])->name('orders.store');

        // Ticket types inside a festival
        Route::get('/ticket-types', [TicketController::class, 'index'])->name('ticket-types.index');
        Route::get('/ticket-types/create', [TicketController::class, 'create'])->name('ticket-types.create');
        Route::get('/ticket-types/{id}/edit', [TicketController::class, 'edit'])->name('ticket-types.edit');
        Route::delete('/ticket-types/{id}', [TicketController::class, 'destroy'])->name('ticket-types.destroy');
        Route::post('/ticket-types', [TicketController::class, 'store'])->name('ticket-types.store');
        Route::put('/ticket-types/{id}', [TicketController::class, 'update'])->name('ticket-types.update');
        Route::put('/ticket-types/{id}/photo', [TicketController::class, 'uploadPhoto']);
        Route::put('/ticket-types/{id}/qr', [TicketController::class, 'setQrCoordinates']);
        Route::put('/ticket-types/{id}/price', [TicketController::class, 'setPrice']);
        Route::put('/commissions', [AdminController::class, 'setCommission']);

        // Mail templates scoped to this festival (admin can override the
        // global defaults for their event without touching the platform-wide copy)
        Route::get('/mail-templates', MailTemplateEditor::class)
            ->name('mail-templates.index');
    });

    /* ----------  Festival-scoped promoter area ---------- */
    Route::middleware(['role:promoter|sub_promoter|superadmin|admin', 'festival.access'])->prefix('promoter/festivals/{festival}')->name('promoter.')->group(function () {
        Route::get('/dashboard', [PromoterController::class, 'dashboard'])->name('dashboard');
        Route::get('/help', [PromoterController::class, 'help'])->name('help');
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::get('/order/create', [OrderController::class, 'create'])->name('orders.create');
        Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');

        // Sub-promoter creation (only the lead promoter or admin can do this)
        Route::post('/sub-promoters', [PromoterController::class, 'createSubPromoter']);
    });

    /* ----------  Sub-promoter area (legacy routes preserved) ---------- */
    Route::middleware('role:sub_promoter')->prefix('sub-promoter')->group(function () {
        Route::get('/dashboard', [SubPromoterController::class, 'dashboard'])->name('sub_promoter.dashboard');
        Route::post('/orders', [SubPromoterController::class, 'placeOrder'])->name('sub_promoter.orders.store');
    });
});

require __DIR__ . '/auth.php';