# ⚡ CACHE TENANCY - Central vs Tenant

## ⚠️ WICHTIG: Cache Driver Requirements

### ✅ Status: AKTIVIERT (mit Einschränkung)

Der `CacheTenancyBootstrapper` ist aktiviert, **aber** benötigt einen Cache Driver der **Tags unterstützt**.

---

## 🚫 Problem: Nicht alle Cache Drivers unterstützen Tags

| Driver | Tags Support | Status | Empfehlung |
|--------|--------------|--------|------------|
| `database` | ❌ Nein | Aktuell in `.env` | ⚠️ Wechseln! |
| `file` | ❌ Nein | - | ⚠️ Nicht verwenden |
| `array` | ✅ Ja | Nur für Tests | ⚠️ Nicht persistent |
| **`redis`** | ✅ **Ja** | - | ✅ **EMPFOHLEN (Produktion)** |
| **`memcached`** | ✅ **Ja** | - | ✅ **Alternative** |

---

## ✅ LÖSUNG 1: Redis Cache (Empfohlen)

### Installation

**Windows:**
```powershell
# 1. Redis für Windows herunterladen
# https://github.com/microsoftarchive/redis/releases

# 2. Redis installieren & starten
redis-server

# 3. PHP Redis Extension installieren
# In php.ini aktivieren:
extension=redis
```

**Linux/Mac:**
```bash
# Ubuntu/Debian
sudo apt-get install redis-server
sudo systemctl start redis

# Mac (Homebrew)
brew install redis
brew services start redis

# PHP Extension
sudo apt-get install php-redis
```

### Konfiguration

**1. `.env` anpassen:**
```env
CACHE_STORE=redis

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_DB=0
REDIS_CACHE_DB=1
```

**2. Composer Package (falls noch nicht installiert):**
```bash
composer require predis/predis
```

**3. Cache leeren & testen:**
```bash
php artisan config:clear
php artisan cache:clear

# Test
php artisan tinker --execute="Cache::put('test', 'value', 60); echo Cache::get('test');"
```

---

## ✅ LÖSUNG 2: Memcached Cache (Alternative)

### Installation

**Windows:**
```powershell
# 1. Memcached für Windows
# https://www.urielkatz.com/archive/detail/memcached-64-bit-windows/

# 2. Als Service starten
memcached.exe -d start
```

**Linux:**
```bash
sudo apt-get install memcached
sudo systemctl start memcached

# PHP Extension
sudo apt-get install php-memcached
```

### Konfiguration

**1. `.env` anpassen:**
```env
CACHE_STORE=memcached

MEMCACHED_HOST=127.0.0.1
MEMCACHED_PORT=11211
MEMCACHED_USERNAME=null
MEMCACHED_PASSWORD=null
```

**2. Cache leeren & testen:**
```bash
php artisan config:clear
php artisan cache:clear
```

---

## 📊 Wie Cache Tenancy funktioniert

### Automatische Tag-basierte Isolation

```php
// ========================================
// Central Context
// ========================================
Cache::put('rankings', ['position' => 1, 'team' => 'Bayern'], 3600);
// Intern: Cache ohne Tag gespeichert

$value = Cache::get('rankings');
// → ['position' => 1, 'team' => 'Bayern']


// ========================================
// Tenant Context: testclub
// ========================================
$tenant->run(function() {
    Cache::put('rankings', ['position' => 1, 'team' => 'FC Testclub'], 3600);
    // Intern: Cache mit Tag 'tenanttestclub' gespeichert
    
    $value = Cache::get('rankings');
    // → ['position' => 1, 'team' => 'FC Testclub']
});


// ========================================
// Tenant Context: arsenal
// ========================================
$tenant2->run(function() {
    Cache::put('rankings', ['position' => 1, 'team' => 'Arsenal FC'], 3600);
    // Intern: Cache mit Tag 'tenantarsenal' gespeichert
    
    $value = Cache::get('rankings');
    // → ['position' => 1, 'team' => 'Arsenal FC']
});
```

### Isolation garantiert

```php
// Central sieht NUR Central Cache
Cache::get('rankings'); 
// → ['position' => 1, 'team' => 'Bayern']

// Testclub sieht NUR Testclub Cache
$testclub->run(function() {
    Cache::get('rankings');
    // → ['position' => 1, 'team' => 'FC Testclub']
});

// Arsenal sieht NUR Arsenal Cache
$arsenal->run(function() {
    Cache::get('rankings');
    // → ['position' => 1, 'team' => 'Arsenal FC']
});
```

---

## 💻 Praktische Beispiele

### 1. Filament Dashboard Stats cachen

```php
// In einem Filament Widget

protected function getStats(): array
{
    return Cache::remember('dashboard_stats', 3600, function() {
        return [
            Stat::make('Total Players', Player::count()),
            Stat::make('Total Matches', Match::count()),
            Stat::make('Total News', News::count()),
        ];
    });
}
```

**Ergebnis:**
- **Testclub** cached seine eigenen Stats
- **Arsenal** cached seine eigenen Stats
- **Barcelona** cached seine eigenen Stats

**Automatisch isoliert!**

### 2. API Response cachen

```php
public function getLeagueStandings()
{
    return Cache::remember('league_standings', 3600, function() {
        return Team::with('matches')
            ->orderBy('points', 'desc')
            ->get();
    });
}
```

Jeder Tenant hat seine eigene Tabelle im Cache!

### 3. User Permissions cachen

```php
public function getUserPermissions(User $user)
{
    $cacheKey = "user_{$user->id}_permissions";
    
    return Cache::remember($cacheKey, 3600, function() use ($user) {
        return $user->getAllPermissions();
    });
}
```

Permissions werden pro Tenant getrennt gecached.

### 4. Cache manuell löschen

```php
// Gesamter Cache eines Tenants
$tenant->run(function() {
    Cache::flush(); // Nur dieser Tenant
});

// Spezifischer Key
Cache::forget('rankings');

// Alle Tenants (gefährlich!)
php artisan cache:clear
```

---

## 🧪 Cache Tenancy testen

### Simpler Test (nach Redis/Memcached Installation)

```php
use App\Models\Central\Tenant;
use Illuminate\Support\Facades\Cache;

// Central Cache
Cache::put('test_value', 'Central', 60);
echo "Central: " . Cache::get('test_value') . PHP_EOL;
// → "Central"

// Tenant Cache
$tenant = Tenant::find('testclub');
$tenant->run(function() {
    Cache::put('test_value', 'Testclub', 60);
    echo "Testclub: " . Cache::get('test_value') . PHP_EOL;
    // → "Testclub"
});

// Central wieder
echo "Central nochmal: " . Cache::get('test_value') . PHP_EOL;
// → "Central" (nicht "Testclub" - Isolation!)
```

---

## 📋 Konfiguration Checklist

- [x] `CacheTenancyBootstrapper` in `config/tenancy.php` aktiviert
- [x] `cache.tag_base` = `'tenant'` in `config/tenancy.php`
- [ ] **Redis oder Memcached installiert & läuft**
- [ ] **`CACHE_STORE` in `.env` auf `redis` oder `memcached` gesetzt**
- [ ] **Config Cache geleert: `php artisan config:clear`**
- [ ] **Cache Test erfolgreich**

---

## ⚙️ Aktueller Status

```env
# .env
CACHE_STORE=database  # ⚠️ PROBLEM: Unterstützt keine Tags!
```

### ✅ Nächste Schritte:

1. **Redis installieren** (empfohlen):
   ```powershell
   # Windows: https://github.com/microsoftarchive/redis/releases
   redis-server
   ```

2. **`.env` anpassen**:
   ```env
   CACHE_STORE=redis
   ```

3. **Config Cache leeren**:
   ```bash
   php artisan config:clear
   ```

4. **Testen**:
   ```bash
   php demo-tenant-cache.php
   ```

---

## 🔧 Troubleshooting

### Problem: "This cache store does not support tagging"

**Ursache**: Cache Driver (database/file) unterstützt keine Tags

**Lösung**: Wechsel zu Redis oder Memcached (siehe oben)

### Problem: Redis Connection refused

```bash
# 1. Prüfe ob Redis läuft
redis-cli ping
# Sollte "PONG" zurückgeben

# 2. Redis neu starten
# Windows: redis-server
# Linux: sudo systemctl restart redis
```

### Problem: Memcached Connection refused

```bash
# Prüfe ob Memcached läuft
telnet localhost 11211

# Starte Memcached
# Windows: memcached.exe -d start
# Linux: sudo systemctl restart memcached
```

---

## 📚 Cache Driver Vergleich

| Feature | Database | File | Array | Redis | Memcached |
|---------|----------|------|-------|-------|-----------|
| **Tags Support** | ❌ | ❌ | ✅ | ✅ | ✅ |
| **Persistent** | ✅ | ✅ | ❌ | ✅ | ✅ |
| **Performance** | 🐌 Langsam | 🐌 Langsam | ⚡ Schnell | ⚡ Schnell | ⚡ Schnell |
| **Multi-Tenancy** | ❌ | ❌ | ⚠️ Tests | ✅ | ✅ |
| **Skalierbar** | ❌ | ❌ | ❌ | ✅ | ✅ |
| **Installation** | ✅ Easy | ✅ Easy | ✅ Easy | ⚠️ Medium | ⚠️ Medium |
| **Empfehlung** | ❌ | ❌ | ⚠️ Testing | ✅ **Produktion** | ✅ Alternative |

---

## 🎯 Zusammenfassung

**Status**: ✅ **Konfiguriert** (aber Cache Driver muss gewechselt werden)

**Was funktioniert:**
- ✅ `CacheTenancyBootstrapper` aktiviert
- ✅ `cache.tag_base` = `'tenant'` konfiguriert
- ✅ Automatische Tag-basierte Isolation vorbereitet

**Was noch fehlt:**
- ⚠️ Redis oder Memcached Installation
- ⚠️ `.env` auf `redis`/`memcached` umstellen

**Sobald Redis/Memcached läuft:**
- Jeder Tenant hat automatisch isolierten Cache
- Gleicher Cache-Key, unterschiedliche Werte pro Tenant
- Keine manuelle Namespace-Verwaltung nötig

---

## 📚 Weiterführende Dokumentation

- [STORAGE_FILESYSTEM_STRUKTUR.md](./STORAGE_FILESYSTEM_STRUKTUR.md) - Filesystem Tenancy
- [ROUTES_STRUKTUR.md](./ROUTES_STRUKTUR.md) - Routes Separation
- [MODELS_STRUKTUR.md](./MODELS_STRUKTUR.md) - Models Structure
- [stancl/tenancy Docs](https://tenancyforlaravel.com/docs/v4/cache-tenancy) - Official Cache Documentation

**Letzte Aktualisierung**: 2025-10-26
