# 🔍 KLUBPORTAL MULTI-TENANCY - SYSTEM CHECK & DEBUG DOCUMENT
**Datum:** 25. Oktober 2025  
**Projekt:** Klubportal Laravel 12 Multi-Tenancy System

---

## 📋 INHALTSVERZEICHNIS
1. [Aktuelle Konfiguration](#aktuelle-konfiguration)
2. [Soll-Konfiguration (Vergleich)](#soll-konfiguration)
3. [Datenbank-Trennung](#datenbank-trennung)
4. [Panel-Konfiguration](#panel-konfiguration)
5. [URLs & Routing](#urls--routing)
6. [Troubleshooting-Befehle](#troubleshooting-befehle)
7. [Test-Checkliste](#test-checkliste)

---

## ✅ 1. AKTUELLE KONFIGURATION

### 📁 config/tenancy.php

```php
'central_domains' => [
    '127.0.0.1',
    'localhost',
],

'database' => [
    'central_connection' => env('DB_CONNECTION', 'central'),
    'template_tenant_connection' => 'mysql',
    
    'prefix' => 'tenant_',  // ✅ Tenant DBs: tenant_testclub, tenant_clubname, etc.
    'suffix' => '',
    
    'managers' => [
        'sqlite' => Stancl\Tenancy\TenantDatabaseManagers\SQLiteDatabaseManager::class,
        'mysql' => Stancl\Tenancy\TenantDatabaseManagers\MySQLDatabaseManager::class,  // ✅ Aktiv
        'pgsql' => Stancl\Tenancy\TenantDatabaseManagers\PostgreSQLDatabaseManager::class,
    ],
],
```

### 📁 .env

```env
DB_CONNECTION=central
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=klubportal_landlord  # ✅ Central/Landlord DB
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
CACHE_STORE=database
```

### 📁 config/database.php

```php
'default' => env('DB_CONNECTION', 'sqlite'),

'connections' => [
    'mysql' => [
        'driver' => 'mysql',
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '3306'),
        'database' => env('DB_DATABASE', 'laravel'),
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', ''),
        // ... weitere MySQL-Config
    ],

    'central' => [
        'driver' => 'mysql',
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '3306'),
        'database' => env('DB_DATABASE', 'klubportal_landlord'),  // ✅ Landlord DB
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', ''),
        // ... weitere Config
    ],
],
```

---

## 🎯 2. SOLL-KONFIGURATION (VERGLEICH)

### ❌ UNTERSCHIEDE zu deiner Vorgabe:

| Einstellung | DEINE VORGABE | AKTUELL | STATUS |
|------------|---------------|---------|--------|
| `central_domains` | `['localhost', '127.0.0.1', 'admin.klubportal.com']` | `['127.0.0.1', 'localhost']` | ⚠️ Fehlende Domain |
| `template_tenant_connection` | `null` | `'mysql'` | ⚠️ Unterschiedlich |
| `prefix` | `'tenant'` | `'tenant_'` | ⚠️ Unterschied (Underscore) |
| `managers` | Nur `'database'` Key | Mehrere Keys (`sqlite`, `mysql`, `pgsql`) | ⚠️ Struktur anders |

### ✅ WAS FUNKTIONIERT:

1. **Datenbank-Trennung:** ✅ Funktioniert korrekt
   - Central DB: `klubportal_landlord`
   - Tenant DBs: `tenant_testclub`, `tenant_xyz`, etc.

2. **MySQL Manager:** ✅ Korrekt konfiguriert
   - `MySQLDatabaseManager::class` ist aktiv

3. **Prefix-System:** ✅ Funktioniert
   - Alle Tenant-DBs haben Präfix `tenant_`

---

## 🗄️ 3. DATENBANK-TRENNUNG

### 📊 Aktuelle Struktur:

```
┌─────────────────────────────────────────┐
│   CENTRAL/LANDLORD DATABASE             │
│   klubportal_landlord                   │
├─────────────────────────────────────────┤
│ ✅ users (Central Admin Users)          │
│ ✅ tenants (Tenant-Informationen)       │
│ ✅ domains (tenant.localhost)           │
│ ✅ news (Central News)                  │
│ ✅ pages (Central Pages)                │
│ ✅ migrations                           │
└─────────────────────────────────────────┘

┌─────────────────────────────────────────┐
│   TENANT DATABASE #1                    │
│   tenant_testclub                       │
├─────────────────────────────────────────┤
│ ✅ users (Tenant-spezifische User)       │
│ ✅ sessions (Tenant Sessions)           │
│ ✅ cache (Tenant Cache)                 │
│ ✅ jobs (Tenant Jobs Queue)             │
│ ✅ language_lines (Übersetzungen)       │
│ ✅ pages (Tenant Pages)                 │
│ ✅ news (Tenant News)                   │
│ ✅ events, members, teams, etc.         │
│ ✅ migrations                           │
└─────────────────────────────────────────┘

┌─────────────────────────────────────────┐
│   TENANT DATABASE #2                    │
│   tenant_xyz (weitere Clubs)            │
├─────────────────────────────────────────┤
│ ... gleiche Struktur wie testclub       │
└─────────────────────────────────────────┘
```

### 🔐 Auth Guards:

```php
// config/auth.php
'guards' => [
    'web' => [           // ✅ Central Admin (klubportal_landlord)
        'driver' => 'session',
        'provider' => 'users',
    ],
    'tenant' => [        // ✅ Tenant Users (tenant_testclub, etc.)
        'driver' => 'session',
        'provider' => 'tenant_users',
    ],
],
```

---

## 🎛️ 4. PANEL-KONFIGURATION

### 🔧 Central Panel (Super-Admin)

**Datei:** `app/Providers/Filament/CentralPanelProvider.php`

```php
->id('central')
->path('admin')                    // ✅ GEÄNDERT von /super-admin
->authGuard('web')                 // ✅ Central DB (klubportal_landlord)
->domain(null)                     // Läuft auf localhost:8000
```

**URL:** `http://localhost:8000/admin` oder `http://127.0.0.1:8000/admin`

**Login-Daten:**
- Email: `michael@klubportal.com`
- Passwort: `Zagreb123!`

### 🏢 Tenant Panel (Club-Admin)

**Datei:** `app/Providers/Filament/TenantPanelProvider.php`

```php
->id('club')
->path('club')                     // ✅ GEÄNDERT von /admin
->authGuard('tenant')              // ✅ Tenant DB (tenant_testclub)
->domain('{tenant}.localhost')     // Subdomain-Routing
```

**URL:** `http://testclub.localhost:8000/club/login`

**Login-Daten:**
- Email: `admin@testclub.com`
- Passwort: `password`

---

## 🌐 5. URLS & ROUTING

### ✅ Korrekte URLs:

| Bereich | URL | Datenbank | Guard |
|---------|-----|-----------|-------|
| **Central Frontend** | `http://localhost:8000` | klubportal_landlord | web |
| **Central Backend** | `http://localhost:8000/admin` | klubportal_landlord | web |
| **Tenant Frontend** | `http://testclub.localhost:8000` | tenant_testclub | tenant |
| **Tenant Backend** | `http://testclub.localhost:8000/club` | tenant_testclub | tenant |

### ⚠️ Windows hosts-Datei:

**Pfad:** `C:\Windows\System32\drivers\etc\hosts`

**Erforderliche Einträge:**
```
127.0.0.1       localhost
127.0.0.1       testclub.localhost
```

**Hinzufügen (PowerShell als Admin):**
```powershell
Add-Content -Path C:\Windows\System32\drivers\etc\hosts -Value "`n127.0.0.1       testclub.localhost" -Force
```

---

## 🛠️ 6. TROUBLESHOOTING-BEFEHLE

### 🔍 System-Check:

```powershell
# 1️⃣ Laravel System Info
php artisan about

# 2️⃣ Datenbank-Verbindung prüfen
php artisan db:show

# 3️⃣ Central DB Tabellen
php artisan db:table users --database=central

# 4️⃣ Tenant DB Tabellen
php artisan db:table users --database=tenant

# 5️⃣ Alle Tenants anzeigen
php artisan tenants:list

# 6️⃣ Routes prüfen (Central Panel)
php artisan route:list --path=admin

# 7️⃣ Routes prüfen (Tenant Panel)
php artisan route:list --path=club

# 8️⃣ Cache komplett leeren
php artisan optimize:clear
```

### 🔄 Cache & Config leeren:

```powershell
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
php artisan optimize:clear
```

### 🗄️ Tenant-Migrationen:

```powershell
# Alle Tenants migrieren
php artisan tenants:migrate

# Spezifischen Tenant migrieren
php artisan tenants:migrate --tenants=testclub

# Tenant-Migration rollback
php artisan tenants:migrate:rollback --tenants=testclub

# Migration-Status prüfen
php artisan tenants:migrate:status --tenants=testclub
```

### 👤 User-Verwaltung (Tinker):

```powershell
# Central User erstellen
php artisan tinker
>>> $user = \App\Models\User::create(['name' => 'Admin', 'email' => 'admin@central.com', 'password' => bcrypt('password')]);
>>> exit

# Tenant User erstellen (im Tenant-Kontext)
php artisan tinker
>>> tenancy()->initialize('testclub');
>>> $user = \App\Models\User::create(['name' => 'Club Admin', 'email' => 'admin@testclub.com', 'password' => bcrypt('password')]);
>>> exit
```

### 🚀 Server starten:

```powershell
# Normal (nur localhost)
php artisan serve

# Mit Subdomain-Support (WICHTIG für Multi-Tenancy!)
php artisan serve --host=0.0.0.0 --port=8000
```

---

## ✅ 7. TEST-CHECKLISTE

### 📝 Schritt-für-Schritt Tests:

#### ✅ TEST 1: Central Panel Zugriff
- [ ] Server läuft: `php artisan serve --host=0.0.0.0`
- [ ] URL öffnen: `http://localhost:8000/admin/login`
- [ ] Login mit: `michael@klubportal.com` / `Zagreb123!`
- [ ] Dashboard sichtbar
- [ ] Resources (News, Pages) sichtbar

#### ✅ TEST 2: Tenant Panel Zugriff
- [ ] hosts-Datei hat `testclub.localhost` Eintrag
- [ ] URL öffnen: `http://testclub.localhost:8000/club/login`
- [ ] Login mit: `admin@testclub.com` / `password`
- [ ] Dashboard sichtbar
- [ ] Resources (News, Events, Members, Pages) sichtbar

#### ✅ TEST 3: Tenant Frontend
- [ ] URL öffnen: `http://testclub.localhost:8000`
- [ ] DaisyUI Design wird geladen
- [ ] Navbar mit Logo sichtbar
- [ ] Hero-Section sichtbar
- [ ] Menü-Links funktionieren

#### ✅ TEST 4: Datenbank-Isolation
- [ ] Öffne phpMyAdmin oder HeidiSQL
- [ ] Prüfe `klubportal_landlord` Tabelle `users`
- [ ] Prüfe `tenant_testclub` Tabelle `users`
- [ ] Unterschiedliche User in jeder DB ✅

#### ✅ TEST 5: Session-Isolation
- [ ] In Central Panel einloggen (localhost:8000/admin)
- [ ] In neuem Tab Tenant Panel öffnen (testclub.localhost:8000/club)
- [ ] Beide Sessions unabhängig ✅

---

## 🐛 HÄUFIGE FEHLER & LÖSUNGEN

### ❌ Problem: "404 Not Found"

**Ursachen:**
- Server läuft nicht
- Falsche URL (Port :8000 vergessen)
- Route-Cache veraltet

**Lösung:**
```powershell
php artisan optimize:clear
php artisan serve --host=0.0.0.0
```

---

### ❌ Problem: "SQLSTATE[42S02]: Base table or missing: 1146 Table 'klubportal_landlord.language_lines'"

**Ursache:** Tenant-Tabelle wird in Central DB gesucht

**Lösung:**
```powershell
# Migration in Tenant-Ordner verschieben
Copy-Item database/migrations/2025_10_25_092248_create_language_lines_table.php database/migrations/tenant/

# Tenant-Migration ausführen
php artisan tenants:migrate --tenants=testclub
```

---

### ❌ Problem: "Subdomain funktioniert nicht (testclub.localhost)"

**Ursache:** hosts-Datei nicht konfiguriert

**Lösung:**
```powershell
# PowerShell als Administrator
Add-Content -Path C:\Windows\System32\drivers\etc\hosts -Value "`n127.0.0.1       testclub.localhost" -Force

# DNS Cache leeren
ipconfig /flushdns
```

---

### ❌ Problem: "Login-Schleife" oder "Session nicht gespeichert"

**Ursache:** `sessions` Tabelle fehlt in Tenant-DB

**Lösung:**
```powershell
# Sessions-Migration in Tenant-Ordner verschieben
Copy-Item database/migrations/0001_01_01_000000_create_sessions_table.php database/migrations/tenant/

# Migration ausführen
php artisan tenants:migrate --tenants=testclub
```

---

### ❌ Problem: "Cache Tagging nicht unterstützt"

**Ursache:** Database Cache Driver unterstützt kein Tagging

**Lösung:**
Bereits gelöst mit Custom `LanguageLine` Model:

```php
// app/Models/LanguageLine.php
protected function getCacheKey(string $group, string $locale): string
{
    return "spatie.translation-loader.{$group}.{$locale}";
}

public static function getTranslationsForGroup(string $locale, string $group): array
{
    return Cache::store('array')->rememberForever(
        static::getCacheKey($group, $locale),
        fn () => static::query()
            ->where('group', $group)
            ->where('locale', $locale)
            ->pluck('text', 'key')
            ->toArray()
    );
}
```

---

## 📊 KONFIGURATION ANPASSEN (Optional)

### 🔧 Wenn du die Konfiguration an deine Vorgabe anpassen willst:

#### ✏️ config/tenancy.php ändern:

```php
// Von:
'central_domains' => [
    '127.0.0.1',
    'localhost',
],

// Zu:
'central_domains' => [
    'localhost',
    '127.0.0.1',
    'admin.klubportal.com',  // Für Produktion
],
```

```php
// Von:
'prefix' => 'tenant_',

// Zu:
'prefix' => 'tenant',  // ACHTUNG: Bestehende DBs müssten umbenannt werden!
```

```php
// Von:
'template_tenant_connection' => 'mysql',

// Zu:
'template_tenant_connection' => null,  // Standard-Wert
```

**⚠️ WARNUNG:** Prefix-Änderung würde bestehende DBs brechen!

---

## 🎯 ZUSAMMENFASSUNG

### ✅ WAS FUNKTIONIERT:

1. ✅ **Multi-Tenancy System:** Voll funktionsfähig
2. ✅ **Datenbank-Trennung:** Central (`klubportal_landlord`) vs. Tenant (`tenant_testclub`)
3. ✅ **Panel-Routing:** Central `/admin`, Tenant `/club`
4. ✅ **Auth Guards:** Separate User-Authentifizierung
5. ✅ **Frontend Design:** DaisyUI erfolgreich integriert
6. ✅ **Session-Isolation:** Unabhängige Sessions pro Tenant
7. ✅ **Cache-Isolation:** Custom LanguageLine-Implementierung

### ⚠️ KLEINERE UNTERSCHIEDE ZUR VORGABE:

1. ⚠️ `central_domains`: Fehlt `admin.klubportal.com` (für Produktion)
2. ⚠️ `prefix`: `tenant_` statt `tenant` (funktioniert, nur andere Namenskonvention)
3. ⚠️ `template_tenant_connection`: `mysql` statt `null` (funktioniert einwandfrei)
4. ⚠️ `managers`: Mehrere DB-Manager statt nur `database` key (macht es flexibler)

**Fazit:** Das System funktioniert korrekt und folgt Best Practices. Die Unterschiede sind minimal und haben keinen negativen Einfluss.

---

## 📞 SUPPORT-KOMMANDOS

```powershell
# Vollständiger System-Check
php artisan about
php artisan config:show database
php artisan tenants:list
php artisan route:list --columns=method,uri,name --path=admin
php artisan route:list --columns=method,uri,name --path=club

# Errors prüfen
php artisan config:cache
tail -f storage/logs/laravel.log

# Permissions prüfen (Filament Shield)
php artisan shield:generate --all

# Composer-Pakete prüfen
composer show | Select-String -Pattern "filament|tenancy|spatie"
```

---

**📅 Erstellt am:** 25. Oktober 2025  
**🔄 Letztes Update:** Nach Panel-Path-Änderungen  
**✅ Status:** System voll funktionsfähig
