<?php

use App\Http\Controllers\Central\TenantRegistrationController;
use App\Http\Controllers\CentralHomeController;
use App\Http\Controllers\DomainVerificationController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// ========================================
// 🏠 CENTRAL FRONTEND HOME
// ========================================
Route::get('/', [CentralHomeController::class, 'index'])->name('home');
Route::get('/register', [CentralHomeController::class, 'register'])->name('clubs.register');

// ========================================
// 🧪 TEST LOGIN ROUTE (Debug only!)
// ========================================
Route::get('/test-login', function () {
    return view('test-login');
})->name('test.login.form');

Route::post('/test-login', function (Request $request) {
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    // Versuch mit 'web' guard (Central)
    if (Auth::guard('web')->attempt($credentials)) {
        return redirect('/test-login')->with('success', '✅ LOGIN ERFOLGREICH mit web guard! User: ' . Auth::guard('web')->user()->email);
    }

    // Versuch mit 'central' guard
    if (Auth::guard('central')->attempt($credentials)) {
        return redirect('/test-login')->with('success', '✅ LOGIN ERFOLGREICH mit central guard! User: ' . Auth::guard('central')->user()->email);
    }

    return back()->withErrors(['email' => '❌ Login fehlgeschlagen! Credentials stimmen nicht.']);
})->name('test.login');

// ========================================
// 🌐 Central Domain Routes (klubportal.com)
// ========================================

// Tenant Registration Routes
Route::get('/register', [TenantRegistrationController::class, 'show'])
    ->name('tenant.register');

Route::post('/register', [TenantRegistrationController::class, 'store'])
    ->name('tenant.register.store');

// TEST: Simple Response ohne Middleware
Route::get('/test', function () {
    return 'Test funktioniert!';
});

Route::get('/landing', App\Livewire\Central\LandingPage::class)->name('landing');

// Language Switcher
Route::get('/language/{locale}', [App\Http\Controllers\LanguageController::class, 'switch'])->name('language.switch');

// Debug Translations
Route::get('/debug-translations', function () {
    return view('debug-translations');
});

// Neue Marketing Landing Page
Route::get('/landing', App\Livewire\Central\LandingPage::class)->name('landing');

// News Routes
Route::get('/news', function () {
    return 'News Liste - coming soon';
})->name('central.news.index');

Route::get('/news/{slug}', function ($slug) {
    return "News Detail: {$slug} - coming soon";
})->name('central.news.show');

// Domain Verification Routes
Route::get('/verify-domain/{token}', [DomainVerificationController::class, 'verify'])
    ->name('domain.verify');

Route::post('/admin/domains/{tenant}/manual-verify', [DomainVerificationController::class, 'manualVerify'])
    ->name('domain.manual-verify')
    ->middleware('auth');

// Fabricator Pages - muss am Ende stehen (Catch-all)
Route::get('/{filament_fabricator_page_slug}', \Z3d0X\FilamentFabricator\Http\Controllers\PageController::class)
    ->where('filament_fabricator_page_slug', '.*')
    ->name('fabricator');

// ========================================
// 🔐 Central Admin Routes
// ========================================
Route::middleware(['auth:central'])->group(function () {
    // Custom Dashboard außerhalb von Filament
    // Route::get('/custom-dashboard', [DashboardController::class, 'index']);
});

// ========================================
// 🏢 Admin Tenant Management Routes
// ========================================
Route::prefix('admin/tenants')->middleware(['auth'])->group(function () {
    Route::get('/create', [App\Http\Controllers\Admin\TenantCometController::class, 'create'])
        ->name('admin.tenants.create');

    Route::post('/store', [App\Http\Controllers\Admin\TenantCometController::class, 'store'])
        ->name('admin.tenants.store');

    Route::get('/', [App\Http\Controllers\Admin\TenantCometController::class, 'index'])
        ->name('admin.tenants.index');
});

// ========================================
// 🔄 Admin Sync Routes
// ========================================
Route::prefix('admin/sync')->middleware(['auth'])->group(function () {
    Route::get('/', [App\Http\Controllers\Admin\CometSyncController::class, 'index'])
        ->name('admin.sync.index');

    Route::get('/history', [App\Http\Controllers\Admin\CometSyncController::class, 'history'])
        ->name('admin.sync.history');

    Route::get('/status', [App\Http\Controllers\Admin\CometSyncController::class, 'status'])
        ->name('admin.sync.status');

    Route::get('/{id}', [App\Http\Controllers\Admin\CometSyncController::class, 'show'])
        ->name('admin.sync.show');

    // Manual sync triggers
    Route::post('/matches', [App\Http\Controllers\Admin\CometSyncController::class, 'syncMatches'])
        ->name('admin.sync.matches');

    Route::post('/rankings', [App\Http\Controllers\Admin\CometSyncController::class, 'syncRankings'])
        ->name('admin.sync.rankings');

    Route::post('/topscorers', [App\Http\Controllers\Admin\CometSyncController::class, 'syncTopScorers'])
        ->name('admin.sync.topscorers');

    Route::post('/all', [App\Http\Controllers\Admin\CometSyncController::class, 'syncAll'])
        ->name('admin.sync.all');

    Route::post('/tenants', [App\Http\Controllers\Admin\CometSyncController::class, 'syncTenants'])
        ->name('admin.sync.tenants');
});

// 🧪 DEBUG ROUTE: Teste Tenancy Initialisierung
Route::get('/debug-tenancy', function () {
    return response()->json([
        'tenancy_initialized' => tenancy()->initialized,
        'tenant_id' => tenant('id'),
        'domain' => tenant('domain'),
        'user' => auth('tenant')->user()?->only(['id', 'name', 'email']),
        'session_connection' => config('session.connection'),
    ]);
});

// 🧪 DEBUG ROUTE: Teste CSRF Token auf Tenant-Domain
Route::get('/debug-csrf', function () {
    return response()->json([
        'tenancy_initialized' => tenancy()->initialized,
        'tenant_id' => tenant('id'),
        'csrf_token' => csrf_token(),
        'session_id' => session()->getId(),
        'session_driver' => config('session.driver'),
        'session_data' => session()->all(),
    ]);
});

// ℹ️ Hinweis:
// - Tenant (Club) Routes sind in routes/tenant.php
// - Filament Panels nutzen ihre eigene Middleware-Konfiguration
// - Siehe: app/Providers/Filament/*PanelProvider.php

