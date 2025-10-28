# 🎯 Fußball CMS - Multi-Tenancy System - Schnelstart

Willkommen! Dies ist ein umfassendes Multi-Tenancy CMS für Fußballvereine. Das System kann bis zu **1000 Vereine** verwalten, wobei jeder Verein seine eigenen Daten in einer separaten Datenbank hat.

## 📋 Was wurde bis jetzt konfiguriert?

### ✅ Zentrale Komponenten
1. **Multi-Tenancy Setup** (Stancl/Tenancy)
   - Club Model mit Vereins-spezifischen Feldern
   - Central Database für Verwaltung aller Vereine
   - Separate Datenbanken für jeden Verein
   - Domain-basierte Tenant-Identifikation

2. **Datenbank-Architektur**
   - Central Database: `kp_club_management`
     - `tenants` - Alle registrierten Vereine
     - `domains` - Zuordnung Subdomain ↔ Verein
     - `users` - Super Admin Konten
     - Weitere zentrale Tabellen
   - Tenant Databases: `tenant_[UUID]`
     - Jeder Verein hat eine eigene Datenbank
     - Enthält: Teams, Spieler, Spiele, Training, Finances

3. **Filament Admin Panel**
   - Super-Admin Panel unter `/super-admin`
   - Club Management mit Filtern
   - Subscription Plan Management
   - Analytics & Statistiken

4. **Routen & Strukturen**
   - Central Routes für Super-Admin
   - Tenant-spezifische Routes mit Domain-Middleware
   - API Endpoints pro Verein

5. **Migrations**
   - Central Migrations (Tenants, Domains, Users)
   - Tenant Migrations für jede neue Club-Datenbank

## 🚀 Erste Schritte

### 1. Installation & Konfiguration
```bash
cd C:\xampp\htdocs\kp_club_management

# Installiere Abhängigkeiten (falls noch nicht geschehen)
composer install

# Konfiguriere .env (bereits gemacht):
# - DB_CONNECTION=mysql
# - DB_DATABASE=kp_club_management
# - DB_HOST=127.0.0.1
```

### 2. Starte den Entwicklungsserver
```bash
php artisan serve
```

Öffne dann: **http://localhost:8000**

### 3. Zugang zum Super-Admin Panel
```
URL: http://localhost:8000/super-admin
Email: admin@example.com
Passwort: password
```

## 📊 Datenbank-Struktur

### Central Database (kp_club_management)
```
┌──────────────────────────────────────────┐
│ ZENTRALE DATENBANK                       │
├──────────────────────────────────────────┤
│ • tenants (Alle Vereine)                 │
│ • domains (Domain-Zuordnung)             │
│ • users (Super Admin)                    │
│ • jobs, cache, sessions (Laravel)        │
└──────────────────────────────────────────┘
```

**Tenants Tabelle Struktur:**
```sql
- id (UUID)
- club_name (Vereinsname)
- club_short_name (Kürzel)
- primary_color, secondary_color (Farben)
- email, phone, website (Kontakt)
- league, division (Ligainformationen)
- stadium_name, stadium_capacity (Stadion)
- subscription_plan (basic, premium, professional)
- subscription_expires_at (Abo-Status)
- is_active (Aktiv/Inaktiv)
- created_at, updated_at
```

### Tenant Databases (tenant_[ID])
Jeder Verein bekommt automatisch eine Datenbank mit:

```
├─ teams
│  ├─ id, name, short_name
│  ├─ coach_name, assistant_coach_name
│  ├─ age_group (Kinder, Jugend, Herren, Damen)
│  ├─ league, division
│  └─ statistics (matches_played, wins, goals_for, etc.)
│
├─ players
│  ├─ id, first_name, last_name, shirt_number
│  ├─ position (Torwart, Abwehr, Mittelfeld, Sturm)
│  ├─ date_of_birth, nationality
│  ├─ contract_start, contract_end
│  ├─ statistics (appearances, goals, assists, cards)
│  └─ team_id (FK)
│
├─ matches
│  ├─ id, team_id, opponent_name
│  ├─ scheduled_at, match_type (Heim/Auswärts)
│  ├─ our_goals, opponent_goals, result
│  ├─ venue, referee_name
│  └─ status (geplant, laufend, abgeschlossen)
│
└─ finances
   ├─ id, type (Einnahme/Ausgabe)
   ├─ category (Mitgliedsbeiträge, Gehälter, etc.)
   ├─ amount, currency
   ├─ transaction_date, payment_status
   ├─ team_id (optional)
   └─ invoice_number, receipt_number
```

## 🎮 Häufig verwendete Befehle

### Super Admin Verwaltung
```bash
# Neuen Super Admin erstellen
php artisan tinker
> App\Models\User::create([
    'name' => 'Admin Name',
    'email' => 'admin@example.com',
    'password' => bcrypt('sicheres_passwort')
])

# Existierende Super Admins anzeigen
> App\Models\User::all()
```

### Verein-Management
```bash
# Neuen Verein über Tinker erstellen
php artisan tinker
> $club = App\Models\Club::create([
    'club_name' => 'FC Bayern',
    'club_short_name' => 'FCB',
    'email' => 'info@fcbayern.de',
    'subscription_plan' => 'professional',
    'country' => 'Deutschland'
])
> $club->domains()->create(['domain' => 'fcbayern.local'])

# Alle Vereine anzeigen
> App\Models\Club::all()

# Verein mit Subscription abfragen
> App\Models\Club::where('subscription_plan', 'professional')->get()
```

### Tenant Migrations
```bash
# Führe Migrations für alle Tenants durch
php artisan tenants:migrate

# Führe Migrationen Fresh für alle Tenants durch
php artisan tenants:migrate-fresh

# Starte Seeds für alle Tenants
php artisan tenants:seed
```

### Datenbank-Verwaltung
```bash
# Zeige alle Tenant-Datenbanken
mysql> SHOW DATABASES LIKE 'tenant_%';

# Zähle Tenants
mysql> SELECT COUNT(*) FROM kp_club_management.tenants;

# Zeige aktive Clubs
mysql> SELECT id, club_name, league, is_active FROM kp_club_management.tenants WHERE is_active = 1;
```

## 🔧 Nächste Schritte

### 1. Test-Verein erstellen
```bash
php artisan tinker
```
```php
$club = App\Models\Club::create([
    'id' => 'test-club-001',
    'club_name' => 'Test Fußball Club',
    'club_short_name' => 'TFC',
    'email' => 'test@testclub.de',
    'subscription_plan' => 'premium',
    'country' => 'Deutschland',
    'subscription_expires_at' => now()->addYear()
]);

$club->domains()->create(['domain' => 'testclub.local']);
exit
```

### 2. Hosts-Datei konfigurieren (für lokale Domains)
Windows: `C:\Windows\System32\drivers\etc\hosts`
```
127.0.0.1 localhost
127.0.0.1 testclub.local
127.0.0.1 fcbayern.local
```

Dann können Sie zugreifen:
- Super Admin: http://localhost:8000/super-admin
- Test Club: http://testclub.local:8000

### 3. Tenant-Admin Panel erstellen
```bash
php artisan make:filament-panel ClubAdmin
```

### 4. Models & Resources für Tenant-Daten
```bash
php artisan make:model Team -m
php artisan make:model Player -m
php artisan make:model Match -m
php artisan make:model TrainingSession -m
php artisan make:model Finance -m
```

## 📱 API Struktur

### Super Admin APIs (Central)
```
GET /api/super-admin/stats         → Statistiken aller Vereine
GET /api/super-admin/clubs         → Liste aller Vereine
GET /api/super-admin/clubs/{id}    → Vereins-Details
```

### Verein-spezifische APIs (Tenant)
```
GET  /{domain}/api/club-info       → Club-Informationen
GET  /{domain}/api/players         → Spielerliste
GET  /{domain}/api/teams           → Teams
GET  /{domain}/api/matches         → Spiele
GET  /{domain}/api/training        → Trainings
GET  /{domain}/api/finances        → Finanzen
```

## 🎯 Subscription Plans

Drei Abo-Modelle sind definiert:

```php
'basic'         => 'Basic (bis 50 Spieler)',
'premium'       => 'Premium (bis 200 Spieler)',
'professional'  => 'Professional (unbegrenzt)'
```

Der Plan bestimmt:
- Maximale Anzahl Spieler
- Verfügbare Features
- Support-Level
- Preis

## 🔒 Sicherheit

- ✅ Super Admin ist auf Central Domain beschränkt
- ✅ Jeder Club hat isolierte Datenbank
- ✅ Tenant-Isolation durch Middleware
- ✅ CSRF-Schutz für alle Forms
- ⚠️ TODO: API Authentication mit Sanctum/Passport

## 🚨 Troubleshooting

### Problem: "Database does not exist"
**Lösung:**
```bash
php artisan migrate --database=central --force
```

### Problem: Tenant-Migrations schlagen fehl
**Lösung:**
```bash
php artisan tenants:migrate-fresh --force
```

### Problem: Super Admin Login funktioniert nicht
**Lösung:**
```bash
# Verifiziere User in DB
php artisan tinker
> App\Models\User::where('email', 'admin@example.com')->first()

# Falls nicht vorhanden, erstelle neuen
> App\Models\User::create([...])
```

### Problem: Subdomain funktioniert nicht
**Lösung:**
1. Füge Domain zu `hosts` Datei hinzu
2. Überprüfe, dass Club mit dieser Domain existiert:
   ```bash
   php artisan tinker
   > App\Models\Club::with('domains')->get()
   ```

## 📚 Weitere Ressourcen

- Stancl Tenancy: https://tenancyforlaravel.com/
- Filament Docs: https://filamentphp.com/
- Laravel Docs: https://laravel.com/docs
- DATABASE_SETUP.md - Detaillierte Datenbank-Anleitung

---

**Version:** 1.0  
**Letzte Aktualisierung:** 2025-10-23  
**Entwickelt für:** Fußball CMS Multi-Tenancy System
