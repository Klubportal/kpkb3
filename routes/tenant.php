<?php

declare(strict_types=1);

use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\NewsController;
use App\Http\Controllers\Frontend\PageController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenancyServiceProvider with:
| - Domain: {tenant}.localhost
| - Middleware: web, InitializeTenancyByDomain, PreventAccessFromCentralDomains
|
| NOTE: Public routes (like /storage) are in routes/tenant-public.php
|
*/

// ========================================
// 🧪 DEBUG ROUTES (Tenant)
// ========================================
Route::get('/debug-tenancy', function () {
    return response()->json([
        'tenancy_initialized' => tenancy()->initialized,
        'tenant_id' => tenant('id'),
        'domain' => tenant('domain'),
        'user' => auth('tenant')->user()?->only(['id', 'name', 'email']),
        'session_connection' => config('session.connection'),
    ]);
});

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

Route::get('/debug-auth', function () {
    $guards = ['web', 'tenant'];
    $guardStatus = [];

    foreach ($guards as $guard) {
        $guardStatus[$guard] = [
            'check' => auth($guard)->check(),
            'user' => auth($guard)->user()?->only(['id', 'name', 'email']),
        ];
    }

    // Prüfe ob User in DB existiert
    $testUser = \App\Models\Tenant\User::where('email', 'admin@testclub.com')->first();

    return response()->json([
        'tenancy_initialized' => tenancy()->initialized,
        'tenant_id' => tenant('id'),
        'guards' => $guardStatus,
        'default_guard' => config('auth.defaults.guard'),
        'session_data' => session()->all(),
        'test_user_exists' => $testUser !== null,
        'test_user' => $testUser?->only(['id', 'name', 'email']),
    ]);
});

Route::get('/debug-panel-access', function () {
    $user = auth('tenant')->user();

    if (!$user) {
        return response()->json(['error' => 'Not authenticated']);
    }

    try {
        $panel = \Filament\Facades\Filament::getPanel('club');

        return response()->json([
            'user' => $user->only(['id', 'name', 'email']),
            'canAccessPanel' => $user->canAccessPanel($panel),
            'panel_id' => $panel->getId(),
            'panel_path' => $panel->getPath(),
            'auth_check' => auth('tenant')->check(),
            'gate_allows_viewAny' => \Illuminate\Support\Facades\Gate::allows('viewAny', \App\Models\Tenant\Player::class),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
    }
});

// TEST: Direkte Dashboard-Route ohne Filament Middleware
Route::get('/test-dashboard', function () {
    return response()->json([
        'message' => 'Dashboard Test erfolgreich!',
        'user' => auth('tenant')->user()?->only(['id', 'name', 'email']),
        'tenancy' => tenancy()->initialized,
    ]);
})->middleware(['web', 'auth:tenant']);

// TEST: Filament Dashboard manuell aufrufen
Route::get('/test-filament-dashboard', function () {
    $panel = \Filament\Facades\Filament::getPanel('club');
    $user = auth('tenant')->user();

    if (!$user) {
        return response()->json(['error' => 'Not authenticated']);
    }

    if (!$user->canAccessPanel($panel)) {
        return response()->json(['error' => 'Cannot access panel']);
    }

    // Versuche Filament's Dashboard zu laden
    return redirect($panel->getUrl());
})->middleware(['web', 'auth:tenant']);

// ========================================
// 🏠 Tenant Public Frontend Routes (Controller-based Alternative)
// ========================================
use App\Http\Controllers\Tenant\FrontendController;

// Einfaches Controller-basiertes Frontend (Alternative zu Livewire)
Route::controller(FrontendController::class)->group(function () {
    Route::get('/simple', 'home')->name('home.simple');
    Route::get('/simple/about', 'about')->name('about');
    Route::get('/simple/news', 'news')->name('news');
    Route::get('/simple/news/{slug}', 'newsShow')->name('news.show.simple');
    Route::get('/simple/teams', 'teams')->name('teams');
    Route::get('/simple/teams/{id}', 'teamShow')->name('teams.show');
    Route::get('/simple/matches', 'matches')->name('matches');
    Route::get('/simple/contact', 'contact')->name('contact');
});

// ========================================
// 🏠 Tenant Public Frontend Routes
// ========================================

// Homepage
Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('tenant.home');

// News Routes
Route::get('/news', [App\Http\Controllers\NewsController::class, 'index'])->name('tenant.news.index');
Route::get('/news/{slug}', [App\Http\Controllers\NewsController::class, 'show'])->name('tenant.news.show');

// Template Pages
Route::get('/raspored', function () {
    $settings = \App\Models\Tenant\TemplateSetting::current();
    return view('templates.kp.raspored', compact('settings'));
})->name('raspored');

Route::get('/tablice', function () {
    $settings = \App\Models\Tenant\TemplateSetting::current();
    return view('templates.kp.tablice', compact('settings'));
})->name('tablice');

Route::get('/seniori', function () {
    $settings = \App\Models\Tenant\TemplateSetting::current();
    return view('templates.kp.seniori', compact('settings'));
})->name('seniori');

Route::get('/skola-nogometa', function () {
    $settings = \App\Models\Tenant\TemplateSetting::current();
    return view('templates.kp.skola-nogometa', compact('settings'));
})->name('skola-nogometa');

Route::get('/kontakt', function () {
    $settings = \App\Models\Tenant\TemplateSetting::current();
    return view('templates.kp.kontakt', compact('settings'));
})->name('kontakt');

// News Routes (Vijesti)
Route::prefix('vijesti')->name('vijesti.')->group(function () {
    Route::get('/', function () {
        $settings = \App\Models\Tenant\TemplateSetting::current();
        return view('templates.kp.vijesti.index', compact('settings'));
    })->name('index');
    Route::get('/{slug}', \App\Livewire\Tenant\NewsDetail::class)->name('show');
});

// Legacy News Routes removed: '/news' is handled above by controller routes.

// Events Routes
Route::prefix('events')->name('events.')->group(function () {
    Route::get('/', \App\Livewire\Tenant\EventsList::class)->name('index');
    Route::get('/{id}', \App\Livewire\Tenant\EventDetail::class)->name('show');
});

// Dynamic Pages (must be last to avoid conflicts)
Route::get('/{slug}', \App\Livewire\Tenant\PageShow::class)->name('page.show');

// ========================================
// 📝 Old Routes (commented out - can be removed later)
// ========================================

// Public News Routes (uncomment when controllers are ready)
// Route::get('/news', [App\Http\Controllers\Tenant\NewsController::class, 'index'])->name('tenant.news.index');
// Route::get('/news/{news}', [App\Http\Controllers\Tenant\NewsController::class, 'show'])->name('tenant.news.show');

// Public Match/Game Routes
// Route::get('/matches', [App\Http\Controllers\Tenant\MatchController::class, 'index'])->name('tenant.matches.index');
// Route::get('/matches/{match}', [App\Http\Controllers\Tenant\MatchController::class, 'show'])->name('tenant.matches.show');

// Public Team Routes
// Route::get('/teams', [App\Http\Controllers\Tenant\TeamController::class, 'index'])->name('tenant.teams.index');
// Route::get('/teams/{team}', [App\Http\Controllers\Tenant\TeamController::class, 'show'])->name('tenant.teams.show');

// Public Player Routes
// Route::get('/players', [App\Http\Controllers\Tenant\PlayerController::class, 'index'])->name('tenant.players.index');
// Route::get('/players/{player}', [App\Http\Controllers\Tenant\PlayerController::class, 'show'])->name('tenant.players.show');

// Public Event Calendar
// Route::get('/events', [App\Http\Controllers\Tenant\EventController::class, 'index'])->name('tenant.events.index');
// Route::get('/events/{event}', [App\Http\Controllers\Tenant\EventController::class, 'show'])->name('tenant.events.show');
