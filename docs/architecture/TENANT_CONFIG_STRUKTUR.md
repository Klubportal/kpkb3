# ⚙️ Tenant-spezifische Konfiguration

## 📋 Übersicht

Laravel's Config-System kann pro Tenant überschrieben werden, um tenant-spezifische Einstellungen zu ermöglichen.

**Automatisch konfiguriert durch:** `ConfigureTenantEnvironment` Listener

---

## 🎯 Automatische Konfiguration

### Event Listener

**Datei:** `app/Listeners/ConfigureTenantEnvironment.php`

Wird automatisch bei `TenancyInitialized` Event ausgelöst und überschreibt:

| Config Key | Quelle | Beschreibung |
|-----------|--------|--------------|
| `mail.from.address` | `$tenant->email` | E-Mail Absender |
| `mail.from.name` | `$tenant->name` | Absender-Name |
| `app.name` | `$tenant->name` | App-Name |
| `app.url` | `$tenant->domains->first()` | Base URL |
| `cache.prefix` | `tenant_{id}_cache` | Cache-Prefix |
| `filesystems.disks.public` | `tenants/{id}/` | Storage-Pfad |

### Registrierung

**Datei:** `app/Providers/TenancyServiceProvider.php`

```php
Events\TenancyInitialized::class => [
    Listeners\BootstrapTenancy::class,
    \App\Listeners\ConfigureTenantEnvironment::class, // ✅ Hier registriert
],
```

---

## 🔧 Manuelle Konfiguration

### Im Controller

```php
use Illuminate\Support\Facades\Config;

class TenantController extends Controller
{
    public function configure()
    {
        $tenant = tenant(); // Aktueller Tenant
        
        // Mail Config
        Config::set('mail.from.address', $tenant->email);
        Config::set('mail.from.name', $tenant->name);
        
        // App Config
        Config::set('app.name', $tenant->name);
        Config::set('app.timezone', $tenant->timezone ?? 'UTC');
        Config::set('app.locale', $tenant->locale ?? 'de');
        
        // Custom Config
        Config::set('services.stripe.key', $tenant->stripe_key);
        Config::set('services.google.maps_key', $tenant->google_maps_key);
    }
}
```

### In Tenant-Event Listener

```php
namespace App\Listeners;

use Stancl\Tenancy\Events\TenancyInitialized;

class CustomTenantConfig
{
    public function handle(TenancyInitialized $event): void
    {
        $tenant = $event->tenancy->tenant;
        
        // Lade Settings aus Tenant-DB
        $settings = \DB::table('settings')
            ->pluck('payload', 'name')
            ->toArray();
        
        // Überschreibe Config
        foreach ($settings as $key => $value) {
            config([$key => json_decode($value, true)]);
        }
    }
}
```

---

## 💾 Settings aus Tenant-Datenbank

### Settings Model

```php
namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['group', 'name', 'payload', 'locked'];
    
    protected $casts = [
        'payload' => 'json',
        'locked' => 'boolean',
    ];
}
```

### Settings laden

```php
use App\Models\Tenant\Setting;

// Einzelnes Setting
$value = Setting::where('name', 'primary_color')->value('payload');

// Alle Settings als Array
$settings = Setting::pluck('payload', 'name')->toArray();

// Settings in Config schreiben
foreach ($settings as $key => $value) {
    config(['tenant.' . $key => $value]);
}
```

### Im ConfigureTenantEnvironment Listener

```php
protected function getTenantSettings(): ?array
{
    try {
        if (class_exists(\App\Models\Tenant\Setting::class)) {
            $settings = \App\Models\Tenant\Setting::pluck('payload', 'name')->toArray();
            
            // JSON decode values
            return array_map(function ($value) {
                $decoded = json_decode($value, true);
                return $decoded ?? $value;
            }, $settings);
        }

        return null;
    } catch (\Exception $e) {
        \Log::debug('Could not load tenant settings: ' . $e->getMessage());
        return null;
    }
}
```

---

## 🎨 Beispiel: Theme-Settings

### In ConfigureTenantEnvironment

```php
public function handle(TenancyInitialized $event): void
{
    $tenant = $event->tenancy->tenant;
    $settings = $this->getTenantSettings();
    
    if ($settings) {
        // Theme Colors
        if (isset($settings['primary_color'])) {
            Config::set('filament.theme.primary_color', $settings['primary_color']);
        }
        
        // Logo
        if (isset($settings['logo'])) {
            Config::set('filament.brand.logo', $settings['logo']);
        }
        
        // Timezone
        if (isset($settings['timezone'])) {
            Config::set('app.timezone', $settings['timezone']);
        }
        
        // Locale
        if (isset($settings['locale'])) {
            Config::set('app.locale', $settings['locale']);
        }
    }
}
```

### Verwendung in Blade

```blade
<!-- Automatisch tenant-spezifisch -->
<div style="background-color: {{ config('filament.theme.primary_color') }}">
    {{ config('app.name') }}
</div>

<img src="{{ config('filament.brand.logo') }}" alt="Logo">
```

---

## 📧 Beispiel: Mail-Konfiguration

### Pro Tenant unterschiedliche Mail-Settings

```php
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

// In ConfigureTenantEnvironment oder Controller
$tenant = tenant();

Config::set([
    'mail.from.address' => $tenant->email,
    'mail.from.name' => $tenant->name,
    'mail.reply_to.address' => $tenant->support_email,
    'mail.reply_to.name' => $tenant->name . ' Support',
]);

// SMTP Settings (falls tenant eigenen SMTP-Server hat)
if ($tenant->smtp_host) {
    Config::set([
        'mail.mailers.smtp.host' => $tenant->smtp_host,
        'mail.mailers.smtp.port' => $tenant->smtp_port,
        'mail.mailers.smtp.username' => $tenant->smtp_username,
        'mail.mailers.smtp.password' => decrypt($tenant->smtp_password),
    ]);
}

// Jetzt Mail senden mit Tenant-Config
Mail::to('user@example.com')->send(new WelcomeMail());
```

---

## 🔐 Beispiel: API-Keys pro Tenant

### Stripe, PayPal, Google Maps, etc.

```php
public function handle(TenancyInitialized $event): void
{
    $tenant = $event->tenancy->tenant;
    
    // Stripe
    if ($tenant->stripe_key && $tenant->stripe_secret) {
        Config::set([
            'services.stripe.key' => $tenant->stripe_key,
            'services.stripe.secret' => decrypt($tenant->stripe_secret),
        ]);
    }
    
    // PayPal
    if ($tenant->paypal_client_id && $tenant->paypal_secret) {
        Config::set([
            'services.paypal.client_id' => $tenant->paypal_client_id,
            'services.paypal.secret' => decrypt($tenant->paypal_secret),
            'services.paypal.mode' => $tenant->paypal_sandbox ? 'sandbox' : 'live',
        ]);
    }
    
    // Google Maps
    if ($tenant->google_maps_key) {
        Config::set('services.google.maps_key', $tenant->google_maps_key);
    }
}
```

---

## 🌐 Beispiel: Locale & Timezone

### Pro Tenant unterschiedliche Sprache und Zeitzone

```php
public function handle(TenancyInitialized $event): void
{
    $tenant = $event->tenancy->tenant;
    
    // Timezone
    $timezone = $tenant->timezone ?? 'Europe/Berlin';
    Config::set('app.timezone', $timezone);
    date_default_timezone_set($timezone);
    
    // Locale
    $locale = $tenant->locale ?? 'de';
    Config::set('app.locale', $locale);
    app()->setLocale($locale);
    
    // Fallback Locale
    Config::set('app.fallback_locale', $tenant->fallback_locale ?? 'en');
}
```

### Verwendung

```php
// Carbon Dates - automatisch in Tenant-Timezone
$match_date = now(); // Verwendet app.timezone

// Übersetzungen - automatisch in Tenant-Locale
echo __('messages.welcome'); // Verwendet app.locale

// Formatierung
$price = 29.99;
echo Number::currency($price, in: config('app.locale')); // 29,99 € für 'de'
```

---

## 💾 Cache-Prefix pro Tenant

### Automatisch gesetzt

```php
Config::set('cache.prefix', 'tenant_' . $tenant->id . '_cache');
```

### Verwendung

```php
// Cache ist automatisch tenant-isoliert
Cache::put('key', 'value', 3600);
// Speichert unter: tenant_testclub_cache:key

// Jeder Tenant hat eigenen Cache
$value = Cache::get('key'); // Nur für aktuellen Tenant
```

---

## 📁 Storage-Pfad pro Tenant

### Automatisch gesetzt

```php
Config::set('filesystems.disks.public.root', storage_path('app/public/tenants/' . $tenant->id));
Config::set('filesystems.disks.public.url', env('APP_URL') . '/storage/tenants/' . $tenant->id);
```

### Verwendung

```php
// Storage ist automatisch tenant-isoliert
Storage::disk('public')->put('avatar.jpg', $file);
// Speichert in: storage/app/public/tenants/testclub/avatar.jpg

// URL
$url = Storage::disk('public')->url('avatar.jpg');
// http://localhost:8000/storage/tenants/testclub/avatar.jpg
```

---

## 🧪 Testing

### Demo ausführen

```bash
php demo-tenant-config.php
```

**Output:**

```
📋 CONFIG VOR TENANT-INITIALISIERUNG:
  app.name:           Klubportal
  app.url:            http://localhost:8000
  cache.prefix:       klubportal-cache-

🔧 INITIALISIERE TENANT...
✅ Tenant initialisiert!

📋 CONFIG NACH TENANT-INITIALISIERUNG:
  app.name:           Klubportal
  app.url:            http://testclub.localhost
  cache.prefix:       tenant_testclub_cache
```

### Unit Test

```php
namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Central\Tenant;

class TenantConfigTest extends TestCase
{
    public function test_tenant_config_is_loaded()
    {
        $tenant = Tenant::factory()->create([
            'id' => 'testclub',
            'name' => 'Test Club',
            'email' => 'admin@testclub.com',
        ]);
        
        tenancy()->initialize($tenant);
        
        $this->assertEquals('Test Club', config('app.name'));
        $this->assertEquals('admin@testclub.com', config('mail.from.address'));
        $this->assertEquals('tenant_testclub_cache', config('cache.prefix'));
    }
    
    public function test_config_can_be_overridden()
    {
        $tenant = Tenant::factory()->create();
        tenancy()->initialize($tenant);
        
        config(['app.name' => 'Custom Name']);
        
        $this->assertEquals('Custom Name', config('app.name'));
    }
}
```

---

## 🎯 Best Practices

### ✅ DO's

1. **Nutze Event Listener** für automatische Konfiguration
2. **Settings aus DB laden** statt hardcoded
3. **Verschlüssele sensible Daten** (API Keys, Passwörter)
4. **Cache-Prefix setzen** für Tenant-Isolation
5. **Storage-Pfad anpassen** für Datei-Isolation
6. **Timezone & Locale** pro Tenant konfigurieren

### ❌ DON'Ts

1. **Nicht** Config in `.env` für Tenants speichern
2. **Nicht** Config ohne Verschlüsselung für API-Keys
3. **Nicht** Config ändern ohne Event Listener
4. **Nicht** globale Config für tenant-spezifische Werte
5. **Nicht** Settings hardcoded in Code

---

## 🐛 Troubleshooting

### Problem 1: Config wird nicht geladen

```bash
# Fehler: Config bleibt unverändert nach Tenant-Init
```

**Lösung:**

```php
// Prüfe ob Listener registriert ist
// app/Providers/TenancyServiceProvider.php

Events\TenancyInitialized::class => [
    Listeners\BootstrapTenancy::class,
    \App\Listeners\ConfigureTenantEnvironment::class, // ✅ Muss vorhanden sein
],
```

---

### Problem 2: Settings nicht verfügbar

```bash
# Fehler: Table 'settings' not found
```

**Lösung:**

```php
// Im Listener Exception-Handling:
protected function getTenantSettings(): ?array
{
    try {
        if (class_exists(\App\Models\Tenant\Setting::class)) {
            $settings = \App\Models\Tenant\Setting::pluck('payload', 'name')->toArray();
            return $settings;
        }
        return null;
    } catch (\Exception $e) {
        \Log::debug('Could not load tenant settings: ' . $e->getMessage());
        return null; // ✅ Fallback wenn Tabelle fehlt
    }
}
```

---

### Problem 3: API-Keys nicht verschlüsselt

```bash
# Fehler: Stripe key visible in logs
```

**Lösung:**

```php
// In Tenant Model
protected $casts = [
    'stripe_secret' => 'encrypted',
    'paypal_secret' => 'encrypted',
    'smtp_password' => 'encrypted',
];

// Verwendung
$tenant->stripe_secret = 'sk_live_xxx'; // Automatisch verschlüsselt
$key = $tenant->stripe_secret; // Automatisch entschlüsselt
```

---

## 📊 Zusammenfassung

### ✅ Implementiert

- ✅ **ConfigureTenantEnvironment** Listener erstellt
- ✅ **Automatische Config-Überschreibung** bei Tenant-Init
- ✅ **Settings aus Tenant-DB** laden
- ✅ **Cache-Prefix** pro Tenant
- ✅ **Storage-Pfad** pro Tenant
- ✅ **Mail-Config** pro Tenant
- ✅ **Demo-Script** erstellt

### 🔑 Wichtigste Regel

```php
// ✅ RICHTIG - Automatisch via Event Listener
Events\TenancyInitialized::class => [
    \App\Listeners\ConfigureTenantEnvironment::class,
],

// ✅ ODER - Manuell im Code
tenancy()->initialize($tenant);
config(['mail.from.address' => $tenant->email]);

// ❌ FALSCH - Ohne Tenant-Init
config(['mail.from.address' => 'hardcoded@example.com']);
```

---

## 📚 Weitere Dokumentation

- [SESSION_TENANCY_STRUKTUR.md](./SESSION_TENANCY_STRUKTUR.md) - Session Isolation
- [MIDDLEWARE_STRUKTUR.md](./MIDDLEWARE_STRUKTUR.md) - Middleware Konfiguration
- [SEEDING_STRUKTUR.md](./SEEDING_STRUKTUR.md) - Central vs Tenant Seeding
- [MULTI_TENANCY_VERIFIKATION.md](./MULTI_TENANCY_VERIFIKATION.md) - Gesamtübersicht

---

**Status:** ✅ Vollständig implementiert und getestet
**Letztes Update:** 2025-10-26
