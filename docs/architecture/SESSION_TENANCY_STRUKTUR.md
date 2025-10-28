# 🔐 SESSION TENANCY STRUKTUR

## 📋 Übersicht

Sessions werden **automatisch pro Tenant getrennt** durch:
- ✅ `SESSION_DRIVER=database` in `.env`
- ✅ `DatabaseTenancyBootstrapper` (bereits aktiv)
- ✅ Tenant-spezifische `sessions` Tabelle in jeder Tenant-DB

**Wichtig**: Es gibt **KEINEN** separaten `SessionTenancyBootstrapper` in stancl/tenancy v4!  
Session-Isolation wird automatisch vom `DatabaseTenancyBootstrapper` gehandhabt.

---

## ⚙️ Konfiguration

### 1. Session Driver (✅ Bereits konfiguriert)

**Datei**: `.env`

```env
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
```

### 2. Session Config (✅ Bereits konfiguriert)

**Datei**: `config/session.php`

```php
return [
    'driver' => env('SESSION_DRIVER', 'database'),
    
    'lifetime' => (int) env('SESSION_LIFETIME', 120),
    
    'expire_on_close' => env('SESSION_EXPIRE_ON_CLOSE', false),
    
    'encrypt' => env('SESSION_ENCRYPT', false),
    
    // Wichtig: Keine feste Connection - wird dynamisch vom Tenant übernommen
    'connection' => env('SESSION_CONNECTION'),
    
    'table' => env('SESSION_TABLE', 'sessions'),
    
    'store' => env('SESSION_STORE'),
    
    'lottery' => [2, 100],
    
    'cookie' => env(
        'SESSION_COOKIE',
        Str::slug(env('APP_NAME', 'laravel'), '_').'_session'
    ),
    
    'path' => env('SESSION_PATH', '/'),
    
    'domain' => env('SESSION_DOMAIN'),
    
    'secure' => env('SESSION_SECURE_COOKIE'),
    
    'http_only' => env('SESSION_HTTP_ONLY', true),
    
    'same_site' => env('SESSION_SAME_SITE', 'lax'),
    
    'partitioned' => env('SESSION_PARTITIONED_COOKIE', false),
];
```

### 3. Tenancy Config (✅ DatabaseTenancyBootstrapper aktiv)

**Datei**: `config/tenancy.php`

```php
'bootstrappers' => [
    Stancl\Tenancy\Bootstrappers\DatabaseTenancyBootstrapper::class,
    // ↑ Dieser Bootstrapper übernimmt Session-Isolation automatisch!
    Stancl\Tenancy\Bootstrappers\CacheTenancyBootstrapper::class,
    Stancl\Tenancy\Bootstrappers\FilesystemTenancyBootstrapper::class,
    Stancl\Tenancy\Bootstrappers\QueueTenancyBootstrapper::class,
],
```

**Wichtig**: 
- ❌ Es gibt **KEINEN** `SessionTenancyBootstrapper::class`
- ✅ `DatabaseTenancyBootstrapper` erledigt Session-Isolation automatisch
- ✅ Sessions werden in der jeweiligen Tenant-DB gespeichert

---

## 🗄️ Database Migration

### Sessions Tabelle (✅ Bereits vorhanden)

**Datei**: `database/migrations/tenant/0001_01_01_000000_create_sessions_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45')->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
    }
};
```

### Migration ausführen

```bash
# Alle Tenants
php artisan tenants:migrate

# Spezifischer Tenant
php artisan tenants:migrate --tenants=testclub

# Prüfen ob Tabelle existiert
php artisan tinker
tenant('testclub')->run(fn() => Schema::hasTable('sessions'))
```

---

## 🚀 Wie es funktioniert

### Automatische Session-Trennung

```
┌─────────────────────────────────────────────────────┐
│ User öffnet: http://testclub.localhost:8000         │
└─────────────────────────────────────────────────────┘
                     │
                     ▼
        ┌────────────────────────┐
        │ InitializeTenancyByDomain
        │ erkennt: testclub      │
        └────────────────────────┘
                     │
                     ▼
        ┌────────────────────────┐
        │ DatabaseTenancyBootstrapper
        │ aktiviert Tenant DB    │
        └────────────────────────┘
                     │
                     ▼
        ┌────────────────────────┐
        │ Session Connection     │
        │ → tenant_testclub      │
        └────────────────────────┘
                     │
                     ▼
        ┌────────────────────────┐
        │ Sessions gespeichert in:
        │ tenant_testclub.sessions
        └────────────────────────┘
```

### Multi-Tenant Login Scenario

```
Browser Tab 1:                          Browser Tab 2:
───────────────                         ───────────────
http://testclub.localhost:8000          http://liverpool.localhost:8000
  │                                       │
  ├─ Tenant: testclub                     ├─ Tenant: liverpool
  ├─ DB: tenant_testclub                  ├─ DB: tenant_liverpool
  ├─ Session: sessions (testclub)         ├─ Session: sessions (liverpool)
  ├─ User: admin@testclub.com             ├─ User: trainer@liverpool.com
  └─ Auth: ✅ Eingeloggt                  └─ Auth: ✅ Eingeloggt

ERGEBNIS: Komplett getrennte Sessions!
- Logout in Tab 1 → beeinflusst Tab 2 NICHT
- User kann parallel bei beiden Clubs eingeloggt sein
```

---

## 💻 Code Beispiele

### 1. Session verwenden (Standard Laravel)

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Session speichern - landet automatisch in Tenant DB!
        session(['last_visit' => now()]);
        
        // Session lesen
        $lastVisit = session('last_visit');
        
        // Flash Session
        session()->flash('status', 'Willkommen zurück!');
        
        return view('dashboard');
    }
}
```

### 2. Session in Filament

```php
<?php

namespace App\Filament\Resources\PlayerResource\Pages;

use App\Filament\Resources\PlayerResource;
use Filament\Resources\Pages\EditRecord;

class EditPlayer extends EditRecord
{
    protected static string $resource = PlayerResource::class;
    
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Session im Tenant Context
        session([
            'last_player_edit' => [
                'id' => $this->record->id,
                'at' => now(),
            ]
        ]);
        
        return $data;
    }
    
    protected function getRedirectUrl(): string
    {
        // Flash Message (automatisch tenant-isoliert)
        session()->flash('success', 'Spieler aktualisiert!');
        
        return $this->getResource()::getUrl('index');
    }
}
```

### 3. Custom Middleware mit Session

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TrackUserActivity
{
    public function handle(Request $request, Closure $next)
    {
        // Session-basiertes Activity Tracking
        // Automatisch pro Tenant getrennt!
        
        $activities = session('user_activities', []);
        $activities[] = [
            'url' => $request->url(),
            'time' => now(),
        ];
        
        // Nur letzte 10 Activities behalten
        session(['user_activities' => array_slice($activities, -10)]);
        
        return $next($request);
    }
}
```

### 4. Auth mit Session

```php
<?php

namespace App\Http\Livewire;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class LoginForm extends Component implements HasForms
{
    use InteractsWithForms;
    
    public $email;
    public $password;
    public $remember = false;
    
    public function login()
    {
        if (Auth::attempt([
            'email' => $this->email,
            'password' => $this->password,
        ], $this->remember)) {
            // Session wird automatisch in Tenant DB gespeichert!
            session()->regenerate();
            
            session([
                'login_at' => now(),
                'login_ip' => request()->ip(),
            ]);
            
            return redirect()->intended('/dashboard');
        }
        
        $this->addError('email', 'Ungültige Anmeldedaten');
    }
}
```

### 5. Session Cleanup Job

```php
<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class CleanupExpiredSessions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        // Läuft im Tenant Context
        // Löscht Sessions älter als 7 Tage
        
        $expiredTime = now()->subDays(7)->timestamp;
        
        DB::table('sessions')
            ->where('last_activity', '<', $expiredTime)
            ->delete();
    }
}

// Scheduled Job für alle Tenants
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->call(function () {
        \App\Models\Central\Tenant::all()->each(function ($tenant) {
            $tenant->run(function () {
                CleanupExpiredSessions::dispatch();
            });
        });
    })->daily();
}
```

---

## 🧪 Testing

### Test 1: Session Isolation

```php
<?php

namespace Tests\Feature;

use App\Models\Central\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionIsolationTest extends TestCase
{
    use RefreshDatabase;
    
    /** @test */
    public function sessions_are_isolated_per_tenant()
    {
        $tenant1 = Tenant::factory()->create(['id' => 'club1']);
        $tenant2 = Tenant::factory()->create(['id' => 'club2']);
        
        // Tenant 1: Session setzen
        $tenant1->run(function () {
            session(['test_key' => 'club1_value']);
            $this->assertEquals('club1_value', session('test_key'));
        });
        
        // Tenant 2: Darf club1_value NICHT sehen
        $tenant2->run(function () {
            $this->assertNull(session('test_key'));
            session(['test_key' => 'club2_value']);
            $this->assertEquals('club2_value', session('test_key'));
        });
        
        // Tenant 1: Original Session noch intakt
        $tenant1->run(function () {
            $this->assertEquals('club1_value', session('test_key'));
        });
    }
}
```

### Test 2: Multi-Login

```php
<?php

namespace Tests\Feature;

use App\Models\Central\Tenant;
use App\Models\Tenant\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class MultiTenantLoginTest extends TestCase
{
    use RefreshDatabase;
    
    /** @test */
    public function user_can_login_to_multiple_tenants_simultaneously()
    {
        $tenant1 = Tenant::factory()->create(['id' => 'club1']);
        $tenant2 = Tenant::factory()->create(['id' => 'club2']);
        
        // Tenant 1: Login
        $tenant1->run(function () {
            $user1 = User::factory()->create(['email' => 'admin@club1.com']);
            Auth::login($user1);
            $this->assertTrue(Auth::check());
            $this->assertEquals('admin@club1.com', Auth::user()->email);
        });
        
        // Tenant 2: Separater Login (beeinflusst Tenant 1 nicht)
        $tenant2->run(function () {
            $user2 = User::factory()->create(['email' => 'trainer@club2.com']);
            Auth::login($user2);
            $this->assertTrue(Auth::check());
            $this->assertEquals('trainer@club2.com', Auth::user()->email);
        });
        
        // Tenant 1: Immer noch eingeloggt!
        $tenant1->run(function () {
            $this->assertTrue(Auth::check());
            $this->assertEquals('admin@club1.com', Auth::user()->email);
        });
    }
}
```

### Test 3: Session Table Existiert

```php
<?php

namespace Tests\Feature;

use App\Models\Central\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SessionTableTest extends TestCase
{
    use RefreshDatabase;
    
    /** @test */
    public function each_tenant_has_sessions_table()
    {
        $tenant = Tenant::factory()->create();
        
        $tenant->run(function () {
            $this->assertTrue(Schema::hasTable('sessions'));
            
            $columns = Schema::getColumnListing('sessions');
            $this->assertContains('id', $columns);
            $this->assertContains('user_id', $columns);
            $this->assertContains('payload', $columns);
            $this->assertContains('last_activity', $columns);
        });
    }
}
```

---

## 🔍 Session Management

### Sessions anzeigen (Tinker)

```php
php artisan tinker

// Tenant Context wechseln
tenant('testclub')->run(function () {
    // Alle Sessions
    DB::table('sessions')->get();
    
    // Sessions eines Users
    DB::table('sessions')->where('user_id', 1)->get();
    
    // Aktive Sessions (letzten 30 Min)
    DB::table('sessions')
        ->where('last_activity', '>', now()->subMinutes(30)->timestamp)
        ->count();
    
    // Session Details
    $session = DB::table('sessions')->first();
    $payload = unserialize(base64_decode($session->payload));
    print_r($payload);
});
```

### Alte Sessions löschen

```php
// Alle Tenants
use App\Models\Central\Tenant;

Tenant::all()->each(function ($tenant) {
    $tenant->run(function () {
        // Sessions älter als 7 Tage
        $deleted = DB::table('sessions')
            ->where('last_activity', '<', now()->subDays(7)->timestamp)
            ->delete();
        
        echo "Tenant {$tenant->id}: {$deleted} Sessions gelöscht\n";
    });
});
```

### Session Monitor erstellen

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SessionResource\Pages;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class SessionResource extends Resource
{
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'System';
    protected static ?string $label = 'Aktive Sessions';

    public static function getEloquentQuery(): Builder
    {
        // Raw query da sessions keine Eloquent Model ist
        return DB::table('sessions')->orderBy('last_activity', 'desc');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user_id')
                    ->label('User')
                    ->formatStateUsing(fn ($state) => $state ? "User #{$state}" : 'Guest'),
                    
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP Adresse'),
                    
                Tables\Columns\TextColumn::make('user_agent')
                    ->label('Browser')
                    ->limit(50),
                    
                Tables\Columns\TextColumn::make('last_activity')
                    ->label('Zuletzt aktiv')
                    ->dateTime()
                    ->formatStateUsing(fn ($state) => 
                        \Carbon\Carbon::createFromTimestamp($state)
                    ),
            ])
            ->filters([
                Tables\Filters\Filter::make('active')
                    ->label('Nur aktive (30 Min)')
                    ->query(fn (Builder $query) => 
                        $query->where('last_activity', '>', 
                            now()->subMinutes(30)->timestamp
                        )
                    ),
            ])
            ->actions([
                Tables\Actions\Action::make('delete')
                    ->label('Beenden')
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation()
                    ->action(fn ($record) => 
                        DB::table('sessions')
                            ->where('id', $record->id)
                            ->delete()
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('delete')
                    ->label('Sessions beenden')
                    ->requiresConfirmation()
                    ->action(fn ($records) => 
                        DB::table('sessions')
                            ->whereIn('id', $records->pluck('id'))
                            ->delete()
                    ),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSessions::route('/'),
        ];
    }
}
```

---

## ⚠️ Troubleshooting

### Problem 1: Sessions werden nicht getrennt

**Symptome**: User bei testclub sieht Session-Daten von liverpool

**Ursachen & Lösungen**:

```bash
# 1. Prüfe Session Driver
php artisan tinker --execute="echo config('session.driver');"
# Sollte: database

# 2. Prüfe .env
cat .env | grep SESSION_DRIVER
# Sollte: SESSION_DRIVER=database

# 3. Cache leeren
php artisan config:clear
php artisan cache:clear

# 4. Prüfe DatabaseTenancyBootstrapper
php artisan tinker --execute="print_r(config('tenancy.bootstrappers'));"
# Sollte: DatabaseTenancyBootstrapper enthalten
```

### Problem 2: Session Table existiert nicht

**Symptome**: `Table 'tenant_testclub.sessions' doesn't exist`

**Lösung**:

```bash
# Migration für alle Tenants
php artisan tenants:migrate

# Oder nur für spezifischen Tenant
php artisan tenants:migrate --tenants=testclub

# Prüfen
php artisan tinker
tenant('testclub')->run(fn() => Schema::hasTable('sessions'))
```

### Problem 3: Sessions bleiben über Tenants hinweg

**Symptome**: Gleiche Session-ID für verschiedene Tenants

**Ursache**: Session Driver ist nicht `database`

**Lösung**:

```bash
# .env anpassen
SESSION_DRIVER=database  # Nicht 'file' oder 'cookie'

# Config neu laden
php artisan config:clear

# Server neustarten
php artisan serve
```

### Problem 4: Session Cookie Konflikte

**Symptome**: Logout funktioniert nicht korrekt über Tenants hinweg

**Lösung - Domain-spezifische Cookies**:

```env
# .env
SESSION_DOMAIN=.localhost
# Das Punkt-Prefix erlaubt Subdomains aber trennt sie
```

```php
// config/session.php
'domain' => env('SESSION_DOMAIN', null),

// Oder dynamisch per Tenant:
'domain' => function () {
    if (tenancy()->initialized) {
        return '.'.request()->getHost();
    }
    return env('SESSION_DOMAIN');
},
```

### Problem 5: Performance bei vielen Sessions

**Symptome**: Langsame Session-Queries

**Lösung - Indexes hinzufügen**:

```php
// database/migrations/tenant/xxxx_add_indexes_to_sessions.php
Schema::table('sessions', function (Blueprint $table) {
    $table->index('last_activity');
    $table->index('user_id');
});
```

```bash
php artisan tenants:migrate
```

---

## 🎯 Best Practices

### ✅ DO - Empfohlen

```php
// 1. Normale Laravel Session-Methoden verwenden
session(['key' => 'value']);
$value = session('key');

// 2. Flash Messages
session()->flash('status', 'Erfolgreich gespeichert!');

// 3. Auth-Sessions automatisch nutzen
Auth::login($user);
Auth::logout();

// 4. Session regenerieren nach Login
session()->regenerate();

// 5. Alte Sessions aufräumen (Scheduled Job)
protected function schedule(Schedule $schedule)
{
    $schedule->command('model:prune', ['--model' => Session::class])
        ->daily();
}
```

### ❌ DON'T - Vermeiden

```php
// 1. NICHT: Hardcoded Connection
DB::connection('mysql')->table('sessions')->get();

// 2. NICHT: Tenant-ID in Session speichern
session(['tenant_id' => $tenantId]);
// Warum: Automatisch erkannt, redundant

// 3. NICHT: Sessions im Central Context für Tenant-Daten
// Im Central Context
session(['tenant_data' => $data]);  // ❌ Falsch!

// 4. NICHT: File-Driver für Production
SESSION_DRIVER=file  // ❌ Nicht multi-tenant-safe

// 5. NICHT: Shared Session Table
// Eine sessions-Tabelle für alle Tenants teilen ❌
```

---

## 📊 Monitoring & Analytics

### Session Statistics Widget (Filament)

```php
<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class SessionStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalSessions = DB::table('sessions')->count();
        
        $activeSessions = DB::table('sessions')
            ->where('last_activity', '>', now()->subMinutes(30)->timestamp)
            ->count();
        
        $authenticatedSessions = DB::table('sessions')
            ->whereNotNull('user_id')
            ->count();
        
        return [
            Stat::make('Gesamt Sessions', $totalSessions)
                ->description('Alle Sessions in DB')
                ->icon('heroicon-o-users'),
                
            Stat::make('Aktive Sessions', $activeSessions)
                ->description('Letzte 30 Minuten')
                ->icon('heroicon-o-check-circle')
                ->color('success'),
                
            Stat::make('Eingeloggte User', $authenticatedSessions)
                ->description('Authentifiziert')
                ->icon('heroicon-o-shield-check')
                ->color('warning'),
        ];
    }
}
```

---

## 📚 Zusammenfassung

### ✅ Session Tenancy ist AKTIV wenn:

1. ✅ `SESSION_DRIVER=database` in `.env`
2. ✅ `DatabaseTenancyBootstrapper` in `config/tenancy.php`
3. ✅ `sessions` Migration in `database/migrations/tenant/`
4. ✅ Migration ausgeführt: `php artisan tenants:migrate`

### 🎯 Wie es funktioniert:

```
User Request → Domain Middleware → Tenant erkannt
           → DatabaseTenancyBootstrapper aktiviert
           → Session Connection auf Tenant DB umgestellt
           → Sessions landen in tenant_xxx.sessions
```

### 🚀 Vorteile:

- ✅ **Isolation**: Jeder Tenant hat eigene Sessions
- ✅ **Multi-Login**: User kann bei mehreren Tenants gleichzeitig eingeloggt sein
- ✅ **Sicherheit**: Keine Session-Leaks zwischen Tenants
- ✅ **Automatisch**: Keine Code-Änderungen nötig
- ✅ **Standard Laravel**: Alle Laravel Session-Features funktionieren

---

## 🔗 Weiterführende Dokumentation

- [stancl/tenancy Session Docs](https://tenancyforlaravel.com/docs/v4/database-tenancy-bootstrapper)
- [Laravel Session Docs](https://laravel.com/docs/11.x/session)
- [QUEUE_TENANCY_STRUKTUR.md](./QUEUE_TENANCY_STRUKTUR.md)
- [CACHE_TENANCY_STRUKTUR.md](./CACHE_TENANCY_STRUKTUR.md)

**Letzte Aktualisierung**: 2025-10-26
