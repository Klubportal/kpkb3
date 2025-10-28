# 💾 STORAGE/FILESYSTEM STRUKTUR - Central vs Tenant

## ✅ Status: VOLLSTÄNDIG KONFIGURIERT

Die Filesystem-Trennung ist bereits korrekt implementiert und aktiviert.

---

## 📋 Konfiguration

### ✅ 1. Bootstrapper aktiviert (`config/tenancy.php`)

```php
'bootstrappers' => [
    Stancl\Tenancy\Bootstrappers\DatabaseTenancyBootstrapper::class,
    Stancl\Tenancy\Bootstrappers\CacheTenancyBootstrapper::class,
    Stancl\Tenancy\Bootstrappers\FilesystemTenancyBootstrapper::class,  // ✅ Aktiviert
    Stancl\Tenancy\Bootstrappers\QueueTenancyBootstrapper::class,
    // Stancl\Tenancy\Bootstrappers\RedisTenancyBootstrapper::class,
],
```

### ✅ 2. Filesystem Konfiguration (`config/tenancy.php`)

```php
'filesystem' => [
    /**
     * Each disk listed in the 'disks' array will be suffixed by the suffix_base, followed by the tenant_id.
     */
    'suffix_base' => 'tenant',  // ✅ Suffix für Tenant-Disks
    
    'disks' => [
        'local',   // ✅ Aktiviert
        'public',  // ✅ Aktiviert
        // 's3',   // Optional für AWS S3
    ],

    /**
     * Use this for local disks.
     */
    'root_override' => [
        // Disk roots werden nach storage_path() Suffix überschrieben
        'local' => '%storage_path%/app/',
        'public' => '%storage_path%/app/public/',
    ],

    /**
     * Should storage_path() be suffixed.
     */
    'suffix_storage_path' => true,  // ✅ storage_path() wird suffixed

    /**
     * Asset helper tenancy
     */
    'asset_helper_tenancy' => true,  // ✅ asset() calls sind tenant-aware
],
```

---

## 📁 Storage Struktur

### Central Storage (Standard Laravel)

```
storage/
├── app/
│   ├── private/          → local disk (Central)
│   ├── public/           → public disk (Central)
│   └── backup-temp/
├── framework/
│   ├── cache/
│   ├── sessions/
│   └── views/
└── logs/
    └── laravel.log
```

### Tenant Storage (Dynamisch erstellt)

```
storage/
├── app/
│   ├── tenanttestclub/
│   │   ├── private/      → local disk für testclub
│   │   └── public/       → public disk für testclub
│   ├── tenantarsenal/
│   │   ├── private/
│   │   └── public/
│   └── tenantliverpool/
│       ├── private/
│       └── public/
└── logs/
    ├── laravel.log            (Central)
    ├── tenanttestclub.log     (Tenant: testclub)
    └── tenantarsenal.log      (Tenant: arsenal)
```

**Pattern**: `storage/app/tenant{tenant_id}/`

---

## 🔧 Wie es funktioniert

### 1. Disk Suffixing

Wenn ein Tenant aktiv ist, werden die Disk-Namen automatisch suffixed:

```php
// Central Context (kein Tenant)
Storage::disk('local');   // → storage/app/private/
Storage::disk('public');  // → storage/app/public/

// Tenant Context (z.B. testclub)
Storage::disk('local');   // → storage/app/tenanttestclub/
Storage::disk('public');  // → storage/app/tenanttestclub/public/
```

### 2. storage_path() Suffixing

```php
// Central Context
storage_path('app/file.txt');  
// → C:\xampp\htdocs\Klubportal-Laravel12\storage/app/file.txt

// Tenant Context (testclub)
storage_path('app/file.txt');  
// → C:\xampp\htdocs\Klubportal-Laravel12\storage/tenanttestclub/app/file.txt
```

### 3. asset() Helper Tenancy

```php
// Central Context
asset('images/logo.png');  
// → http://localhost:8000/images/logo.png

// Tenant Context (testclub)
asset('images/logo.png');  
// → http://testclub.localhost:8000/storage/images/logo.png
```

**Für globale Assets**: `global_asset('images/logo.png')`

---

## 💻 Code Beispiele

### Datei Upload im Tenant Context

```php
// In Filament Resource oder Livewire Component

use Filament\Forms\Components\FileUpload;

FileUpload::make('avatar')
    ->disk('public')  // Automatisch: storage/app/tenanttestclub/public/
    ->directory('avatars')
    ->image()
    ->maxSize(2048);

// Gespeichert unter:
// storage/app/tenanttestclub/public/avatars/filename.jpg
```

### Datei lesen im Tenant Context

```php
use Illuminate\Support\Facades\Storage;

// Im Tenant Context (z.B. testclub)
$content = Storage::disk('public')->get('documents/contract.pdf');
// Liest: storage/app/tenanttestclub/public/documents/contract.pdf

// Im Central Context
$content = Storage::disk('public')->get('documents/central.pdf');
// Liest: storage/app/public/documents/central.pdf
```

### Manuell zwischen Central und Tenant wechseln

```php
use App\Models\Central\Tenant;

// Tenant Context aktivieren
$tenant = Tenant::find('testclub');
$tenant->run(function () {
    // Hier läuft alles im Tenant Context
    Storage::disk('public')->put('file.txt', 'Tenant data');
    // → storage/app/tenanttestclub/public/file.txt
});

// Nach dem Closure: Wieder im Central Context
Storage::disk('public')->put('file.txt', 'Central data');
// → storage/app/public/file.txt
```

---

## 🖼️ Öffentliche Dateien (Public Storage)

### Symbolic Link erstellen

**Central**:
```bash
php artisan storage:link
```

Erstellt: `public/storage` → `storage/app/public`

**Tenant** (automatisch per Event):
```php
// In app/Providers/TenancyServiceProvider.php

use Stancl\Tenancy\Events\TenancyInitialized;

Event::listen(TenancyInitialized::class, function (TenancyInitialized $event) {
    // Symlink für Tenant erstellen
    $tenant = $event->tenancy->tenant;
    $targetPath = storage_path('app/tenant' . $tenant->id . '/public');
    $linkPath = public_path('storage/tenant' . $tenant->id);
    
    if (!file_exists($linkPath) && file_exists($targetPath)) {
        symlink($targetPath, $linkPath);
    }
});
```

### URL zu öffentlichen Dateien

```php
// Central
Storage::disk('public')->url('images/logo.png');
// → http://localhost:8000/storage/images/logo.png

// Tenant (testclub)
Storage::disk('public')->url('images/club-logo.png');
// → http://testclub.localhost:8000/storage/tenanttestclub/images/club-logo.png
```

---

## 📦 AWS S3 Integration (Optional)

### 1. S3 Disk aktivieren

**In `config/tenancy.php`**:
```php
'filesystem' => [
    'suffix_base' => 'tenant',
    'disks' => [
        'local',
        'public',
        's3',  // ✅ S3 für Multi-Tenancy aktivieren
    ],
],
```

### 2. S3 Konfiguration

**In `.env`**:
```env
AWS_ACCESS_KEY_ID=your-key-id
AWS_SECRET_ACCESS_KEY=your-secret-key
AWS_DEFAULT_REGION=eu-central-1
AWS_BUCKET=klubportal-storage
AWS_URL=https://klubportal-storage.s3.eu-central-1.amazonaws.com
```

### 3. S3 Tenant Paths

```php
// Central Context
Storage::disk('s3')->put('documents/file.pdf', $content);
// S3 Path: s3://klubportal-storage/documents/file.pdf

// Tenant Context (testclub)
Storage::disk('s3')->put('documents/file.pdf', $content);
// S3 Path: s3://klubportal-storage/tenanttestclub/documents/file.pdf
```

**Vorteil**: Jeder Tenant hat automatisch einen eigenen S3 Prefix.

---

## 🧪 Testing

### 1. Storage Pfade testen

```bash
php artisan tinker
```

```php
use App\Models\Central\Tenant;
use Illuminate\Support\Facades\Storage;

// Central Context
echo storage_path('app/test.txt');
// → C:\xampp\htdocs\Klubportal-Laravel12\storage\app\test.txt

echo Storage::disk('public')->path('test.txt');
// → C:\xampp\htdocs\Klubportal-Laravel12\storage\app\public\test.txt

// Tenant Context
$tenant = Tenant::find('testclub');
$tenant->run(function() {
    echo storage_path('app/test.txt') . PHP_EOL;
    // → C:\xampp\htdocs\Klubportal-Laravel12\storage\tenanttestclub\app\test.txt
    
    echo Storage::disk('public')->path('test.txt') . PHP_EOL;
    // → C:\xampp\htdocs\Klubportal-Laravel12\storage\tenanttestclub\app\public\test.txt
});
```

### 2. Datei Upload testen

```php
use App\Models\Central\Tenant;
use Illuminate\Support\Facades\Storage;

// Test Central Upload
Storage::disk('public')->put('test-central.txt', 'Central File');
echo "Central: " . Storage::disk('public')->exists('test-central.txt') . PHP_EOL;

// Test Tenant Upload
$tenant = Tenant::find('testclub');
$tenant->run(function() {
    Storage::disk('public')->put('test-tenant.txt', 'Tenant File');
    echo "Tenant: " . Storage::disk('public')->exists('test-tenant.txt') . PHP_EOL;
});

// Verify Isolation
echo "Central kann Tenant-Datei sehen: " . Storage::disk('public')->exists('test-tenant.txt') . PHP_EOL;
// → false (Isolation funktioniert!)
```

### 3. Disk Liste anzeigen

```bash
php artisan tinker --execute="print_r(array_keys(config('filesystems.disks')));"
```

Erwartete Output:
```
Array
(
    [0] => local
    [1] => backups
    [2] => public
    [3] => s3
)
```

---

## 🛡️ Best Practices

### ✅ DO

1. **Nutze Storage Facade**: `Storage::disk('public')->put()`
2. **Tenant Context beachten**: Wisse ob du im Central oder Tenant Context bist
3. **Disk Namen konsistent**: Nutze immer die gleichen Disk-Namen
4. **Public Files über Symlink**: `php artisan storage:link`
5. **S3 für Produktion**: Skaliert besser als lokales Storage

```php
// Gut: Storage Facade nutzen
Storage::disk('public')->put('avatars/user.jpg', $content);

// Gut: Expliziter Context
tenancy()->initialize($tenant);
Storage::disk('public')->put('file.txt', $content);
tenancy()->end();

// Gut: run() Closure
$tenant->run(function() {
    Storage::disk('public')->put('file.txt', $content);
});
```

### ❌ DON'T

1. **Kein direktes file_put_contents()**: Umgeht Tenant-Isolation
2. **Keine hardcoded Pfade**: `storage/app/public/file.txt`
3. **Kein manuelles Suffixing**: Das macht der Bootstrapper
4. **Keine globalen Disks in Tenant Code**: Nutze tenant-aware Disks

```php
// Schlecht: Direktes file_put_contents
file_put_contents(storage_path('app/file.txt'), $content);
// Problem: Tenant-Isolation wird umgangen!

// Schlecht: Hardcoded Pfad
$path = 'C:\xampp\htdocs\storage\app\public\file.txt';

// Schlecht: Manuelles Suffixing
Storage::disk('public')->put('tenant' . $tenant->id . '/file.txt', $content);
// Problem: Doppeltes Suffixing!
```

---

## 🔧 Troubleshooting

### Problem: Dateien werden nicht gefunden

**Ursache**: Falscher Tenant Context oder Symlink fehlt

```bash
# 1. Prüfe Tenant Context
php artisan tinker --execute="
\$tenant = \App\Models\Central\Tenant::find('testclub');
\$tenant->run(function() {
    echo 'Current Tenant: ' . tenant('id') . PHP_EOL;
    echo 'Storage Path: ' . storage_path('app') . PHP_EOL;
});
"

# 2. Prüfe Symlink existiert
ls -l public/storage

# 3. Erstelle Symlink neu
php artisan storage:link
```

### Problem: Permission Denied

**Ursache**: Fehlende Schreibrechte auf storage/

```powershell
# Windows: Schreibrechte prüfen/setzen
icacls storage /grant "IIS_IUSRS:(OI)(CI)F" /T

# Oder: Storage Ordner neu erstellen
php artisan storage:link --force
```

### Problem: S3 Upload schlägt fehl

```bash
# 1. Prüfe S3 Credentials
php artisan tinker --execute="
echo 'AWS Key: ' . config('filesystems.disks.s3.key') . PHP_EOL;
echo 'AWS Region: ' . config('filesystems.disks.s3.region') . PHP_EOL;
echo 'AWS Bucket: ' . config('filesystems.disks.s3.bucket') . PHP_EOL;
"

# 2. Test S3 Connection
php artisan tinker --execute="
try {
    Storage::disk('s3')->put('test.txt', 'Test');
    echo 'S3 Upload erfolgreich!' . PHP_EOL;
    Storage::disk('s3')->delete('test.txt');
} catch (\Exception \$e) {
    echo 'S3 Error: ' . \$e->getMessage() . PHP_EOL;
}
"
```

---

## 📊 Vergleich: SOLL vs IST

| Feature | Dokumentation | Aktueller Zustand | Status |
|---------|--------------|-------------------|--------|
| **FilesystemTenancyBootstrapper** | ✅ Erforderlich | ✅ Aktiviert | ✅ OK |
| **suffix_base** | `'tenant'` | ✅ `'tenant'` | ✅ OK |
| **Disks: local** | ✅ Aktiviert | ✅ Aktiviert | ✅ OK |
| **Disks: public** | ✅ Aktiviert | ✅ Aktiviert | ✅ OK |
| **Disks: s3** | Optional | ⚠️ Kommentiert | ⚠️ Optional |
| **root_override** | Best Practice | ✅ Konfiguriert | ✅ OK |
| **suffix_storage_path** | `true` | ✅ `true` | ✅ OK |
| **asset_helper_tenancy** | `true` | ✅ `true` | ✅ OK |

### ✅ ZUSAMMENFASSUNG

**Status**: ✅ **VOLLSTÄNDIG KORREKT KONFIGURIERT**

Die Filesystem-Trennung ist optimal implementiert:
- ✅ `FilesystemTenancyBootstrapper` aktiviert
- ✅ `local` und `public` Disks konfiguriert
- ✅ `suffix_base: 'tenant'` gesetzt
- ✅ `storage_path()` Suffixing aktiviert
- ✅ `asset()` Helper ist tenant-aware
- ✅ Root Override korrekt konfiguriert

**Optional**: S3 Disk aktivieren für Cloud Storage (nicht erforderlich für lokale Entwicklung)

---

## 📚 Weiterführende Dokumentation

- [ROUTES_STRUKTUR.md](./ROUTES_STRUKTUR.md) - Central vs Tenant Routes
- [MODELS_STRUKTUR.md](./MODELS_STRUKTUR.md) - Central vs Tenant Models
- [MIGRATIONS_STRUKTUR.md](./MIGRATIONS_STRUKTUR.md) - Migration Structure
- [SYSTEM_CHECK_DEBUG_DOCUMENT.md](./SYSTEM_CHECK_DEBUG_DOCUMENT.md) - System Overview
- [stancl/tenancy Docs](https://tenancyforlaravel.com/docs/v4/filesystem-tenancy) - Official Filesystem Documentation

**Letzte Aktualisierung**: 2025-10-26
