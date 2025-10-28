# Datenbank Setup für Fußball CMS Multi-Tenancy

## Voraussetzungen
- XAMPP mit MySQL läuft
- PHP 8.2+
- Composer installiert

## Schritt 1: Datenbank erstellen

Öffne phpMyAdmin (http://localhost/phpmyadmin) oder nutze die MySQL CLI:

```sql
CREATE DATABASE kp_club_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

## Schritt 2: Abhängigkeiten installieren

```bash
composer install
```

## Schritt 3: Migrations durchführen

### 3a) Central Database Migrations (Hauptdatenbank für alle Vereine)
```bash
php artisan migrate --database=central
```

Dies erstellt folgende Tabellen:
- `central_migrations` - Versionskontrolle
- `central_tenants` - Alle registrierten Fußballvereine
- `central_domains` - Subdomains/Domains für jeden Verein
- `central_users` - Super Admin Benutzer
- `central_password_reset_tokens` - Passwort-Reset Tokens
- `central_sessions` - Sessions

### 3b) Tenant-Template Migrationen (Template für neue Verein-Datenbanken)
```bash
php artisan tenants:migrate-fresh
```

Dies erstellt die Tenant-Template-Migrations.

## Schritt 4: Datenbankstruktur

### Central Database (kp_club_management)
- `tenants` - Alle Vereine mit ihren Informationen
- `domains` - Zuordnung von Domains zu Vereinen
- `users` - Super Admin Benutzer
- Weitere zentrale Tabellen

### Tenant Databases (tenant_[ID])
Jeder Verein bekommt eine eigene Datenbank mit:
- `players` - Spieler
- `teams` - Teams/Mannschaften
- `matches` - Spiele
- `finances` - Finanzinformationen
- `users` - Verein-eigene Benutzer (Trainer, Betreuer, etc.)

## Schritt 5: Super Admin Benutzer erstellen

```bash
php artisan tinker
```

```php
App\Models\User::create([
    'name' => 'Super Admin',
    'email' => 'admin@example.com',
    'password' => bcrypt('password'),
    'is_super_admin' => true
]);
```

## Schritt 6: Entwicklungsserver starten

```bash
php artisan serve
```

Öffne dann: http://localhost:8000

### Super Admin Panel:
http://localhost:8000/super-admin

### Test-Verein (nach dem ersten Club erstellen):
http://[club-domain].local:8000

## Datenbank-Struktur Visualisierung

```
┌─────────────────────────────────────────────────────────────┐
│           ZENTRALE DATENBANK (kp_club_management)           │
├─────────────────────────────────────────────────────────────┤
│ • tenants (id, club_name, email, subscription_plan, ...)    │
│ • domains (domain, tenant_id)                               │
│ • users (Super Admin Accounts)                              │
│ • password_reset_tokens                                     │
│ • cache                                                     │
│ • sessions                                                  │
└─────────────────────────────────────────────────────────────┘
            │
            ├────────────┬────────────┬────────────┐
            │            │            │            │
            ▼            ▼            ▼            ▼
     ┌──────────────┐┌──────────────┐┌──────────────┐┌──────────────┐
     │   tenant_1   ││   tenant_2   ││   tenant_3   ││  tenant_N    │
     ├──────────────┤├──────────────┤├──────────────┤├──────────────┤
     │ • players    ││ • players    ││ • players    ││ • players    │
     │ • teams      ││ • teams      ││ • teams      ││ • teams      │
     │ • matches    ││ • matches    ││ • matches    ││ • matches    │
     │ • finances   ││ • finances   ││ • finances   ││ • finances   │
     │ • users      ││ • users      ││ • users      ││ • users      │
     └──────────────┘└──────────────┘└──────────────┘└──────────────┘
    Verein 1      Verein 2      Verein 3     Verein N
```

## MySQL Befehle zur Verwaltung

### Alle Datenbanken anzeigen:
```sql
SHOW DATABASES LIKE 'tenant_%';
```

### Tenants zählen:
```sql
SELECT COUNT(*) FROM kp_club_management.tenants;
```

### Vereine mit Subscription Status:
```sql
SELECT id, club_name, subscription_plan, is_active, created_at 
FROM kp_club_management.tenants 
ORDER BY created_at DESC 
LIMIT 10;
```

## Troubleshooting

### Migrations schlagen fehl
- Überprüfe, ob MySQL läuft: `SHOW VARIABLES LIKE "version";`
- Überprüfe .env Datenbank-Einstellungen
- Lösche alte tenant_* Datenbanken und starten Sie neu

### Keine Subdomains verfügbar
- Füge zu `hosts` Datei hinzu:
  - Windows: `C:\Windows\System32\drivers\etc\hosts`
  - `127.0.0.1 localhost testclub.local`

### Super Admin kann sich nicht anmelden
- Stelle sicher, dass der User erstellt wurde (siehe Schritt 5)
- Überprüfe den User in der Datenbank

## Performance-Tipps für 1000 Vereine

1. **Datenbank-Indizes**: Indices auf `tenants.is_active`, `tenants.league`, `domains.domain`
2. **Connection Pooling**: Nutze ProxySQL für bessere Ressourcennutzung
3. **Caching**: Redis für Tenant-Lookups
4. **Monitoring**: Tools wie New Relic zur Performance-Überwachung
5. **Loadbalancing**: Mehrere App-Server hinter einem Reverse Proxy
