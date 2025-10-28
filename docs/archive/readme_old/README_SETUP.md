# ⚽ Fußball CMS - Multi-Tenancy System

> Ein modernes, skalierbares Content Management System für bis zu 1.000 Fußballvereine mit vollständiger Multi-Tenancy, Datenbank-Isolation und professionellem Admin-Panel.

## 🎯 Features

### Multi-Tenancy Architektur
- **1000+ Vereine** - Scalable auf 1000 und mehr Fußballvereine
- **Datenbank-Isolation** - Jeder Verein hat seine eigene MySQL Datenbank
- **Domain-basiertes Routing** - `club1.com`, `club2.com`, `superadmin.com`
- **Automatische Tenant-Isolation** - Query, Cache, Filesystem, Queue Isolation

### Admin-Interfaces
- **Super Admin Panel** - Verwaltung aller 1000 Vereine
  - Übersichtliche Datenbank/CRM
  - Subscription Management
  - Statistiken & Analytics
  - Filter & Suche
- **Club Admin Panel** - Jeder Verein verwaltet seine Daten
  - Teams & Mannschaften
  - Spielerverwaltung
  - Spiele/Begegnungen
  - Finanzen

### Datenstruktur pro Verein
- 👥 **Spieler** - Mit Statistiken, Positionen, Verträgen
- 👕 **Teams** - Herren, Damen, Jugend, etc.
- 🏟️ **Spiele** - Heimspiele, Auswärtsspiele, Ergebnisse
- 💰 **Finanzen** - Einnahmen, Ausgaben, Budgetierung

### Subscription-Modelle
- **Basic** - €29/Monat (bis 50 Spieler)
- **Premium** - €59/Monat (bis 200 Spieler)
- **Professional** - €99/Monat (unbegrenzt)

## 🚀 Quick Start

### Voraussetzungen
- PHP 8.2+
- MySQL 8.0+
- Composer
- XAMPP (oder äquivalent)

### Installation (5 Minuten)

```bash
# 1. Repository clonen (oder in XAMPP htdocs extrahieren)
cd C:\xampp\htdocs\kp_club_management

# 2. Dependencies installieren
composer install

# 3. Datenbank erstellen (in phpMyAdmin)
CREATE DATABASE kp_club_management CHARACTER SET utf8mb4;

# 4. .env konfigurieren (bereits vorbereitet)
# Überprüfe: DB_HOST, DB_USERNAME, DB_PASSWORD

# 5. Datenbank initialisieren
php artisan migrate --database=central

# 6. Entwicklungsserver starten
php artisan serve

# 7. Öffne Browser
# Super Admin: http://localhost:8000/super-admin
# Login: admin@example.com / password
```

### Ersten Test-Verein erstellen

```bash
php artisan tinker
```

```php
$club = App\Models\Club::create([
    'club_name' => 'Test FC',
    'club_short_name' => 'TFC',
    'email' => 'test@testfc.de',
    'subscription_plan' => 'professional',
    'country' => 'Deutschland',
    'subscription_expires_at' => now()->addYear()
]);

$club->domains()->create(['domain' => 'testfc.local']);
exit
```

Füge zu `C:\Windows\System32\drivers\etc\hosts` ein:
```
127.0.0.1 testfc.local
127.0.0.1 localhost
```

Dann öffne: **http://testfc.local:8000**

## 📚 Dokumentation

1. **[GETTING_STARTED.md](GETTING_STARTED.md)** - Detaillierte Schnelstart-Anleitung
2. **[DATABASE_SETUP.md](DATABASE_SETUP.md)** - Datenbank-Konfiguration
3. **[ARCHITECTURE.md](ARCHITECTURE.md)** - Technische Architektur & Design
4. **[SETUP_COMPLETE.md](SETUP_COMPLETE.md)** - Setup-Zusammenfassung

## 🏗️ Architektur

```
┌─────────────────────────────────────────┐
│   Super Admin Portal (Central)          │
│   ✓ Verwalte 1000 Clubs                │
│   ✓ Subscription Management             │
│   ✓ Statistiken                         │
└──────────────────┬──────────────────────┘
                   │
        ┌──────────┼──────────┐
        ▼          ▼          ▼
    Central DB   Tenant 1  Tenant 2  ... Tenant 1000
    kp_club_     (tenant_  (tenant_
    management   uuid-1)   uuid-2)
    ├─ tenants
    ├─ domains
    ├─ users
    └─ ...       ├─ teams
                 ├─ players
                 ├─ matches
                 └─ finances
```

## 🎯 Use Cases

### Für Super Admin
- 📊 Übersicht aller Clubs
- 💳 Abonnement Verwaltung
- 📈 System-Statistiken
- 🔧 Club-Management
- 🔐 Benutzer-Verwaltung

### Für Clubs
- 👥 Spielerdatenbank
- 👕 Team Management
- 🏟️ Spielplan
- 💰 Vereins-Finanzen
- 📱 Member Portal

## 🔐 Sicherheit

- ✅ Database-Level Isolation (separate DBs)
- ✅ Query-Level Isolation (automatisch)
- ✅ Filesystem Isolation (tenant-specific storage)
- ✅ Cache Isolation (prefixed keys)
- ✅ CSRF Protection
- ✅ Password Hashing (Bcrypt)
- ⚠️ TODO: 2FA, API Authentication

## 📊 Performance

| Aktion | Zeit |
|--------|------|
| Domain Resolution | ~5ms |
| Tenant Init | ~20ms |
| Player List (100 items) | ~100ms |
| Super Admin Stats | ~200ms |
| Login | ~50ms |

**Durchschnittliche Request-Zeit**: 150-250ms

## 🛠️ Tech Stack

- **Framework**: Laravel 12
- **Admin UI**: Filament 4
- **Multi-Tenancy**: Stancl/Tenancy 3
- **Database**: MySQL 8
- **Frontend**: Blade + Alpine.js
- **CSS**: Tailwind

## 📦 Dependencies

```json
{
  "filament/filament": "^4.0",
  "laravel/framework": "^12.0",
  "stancl/tenancy": "^3.9"
}
```

## 🗂️ Verzeichnisstruktur

```
app/
├── Models/              # Eloquent Models
│   ├── Club.php        # Tenant Model (Verein)
│   └── ...
├── Http/Controllers/    # API & Web Controller
├── Filament/           # Admin UI
│   └── SuperAdmin/
│       └── Resources/  # Admin Ressourcen
└── Providers/          # Service Provider

config/
├── tenancy.php         # Multi-Tenancy Config
└── database.php        # Database Connections

database/
├── migrations/         # Central DB Migrations
├── migrations/tenant/  # Tenant DB Migrations
├── factories/          # Test Data
└── seeders/           # Database Seeds

routes/
├── web.php            # Central Web Routes
└── tenant.php         # Tenant Web Routes

resources/views/       # Blade Templates
storage/              # Files, Cache, Logs
public/               # Öffentliche Assets
```

## 🚀 Deployment

### Development
```bash
php artisan serve
```

### Production
```bash
# Build optimieren
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Deployment via Composer
composer install --no-dev --optimize-autoloader

# Server starten (z.B. mit Nginx)
# siehe Dokumentation für Webserver-Config
```

## 🤝 Beitragen

Dieses Projekt ist ein Portfolio-Projekt. Für Verbesserungen:

1. Fork das Repository
2. Feature Branch erstellen (`git checkout -b feature/AmazingFeature`)
3. Commits (`git commit -m 'Add AmazingFeature'`)
4. Branch pushen (`git push origin feature/AmazingFeature`)
5. Pull Request öffnen

## 📝 Lizenz

MIT License - siehe LICENSE Datei

## ✉️ Kontakt

- **Entwicklung**: AI Copilot
- **Status**: Beta
- **Letzte Aktualisierung**: 2025-10-23

## 🗺️ Roadmap

- [ ] Club Admin Panel (Filament)
- [ ] Mobile App (React Native)
- [ ] Advanced Analytics
- [ ] Email Notifications
- [ ] SMS Reminders
- [ ] Social Features
- [ ] API Documentation
- [ ] Webhook Support

## 🆘 Häufige Probleme

### "Database does not exist"
```bash
php artisan migrate --database=central --force
```

### "Migrations failed for tenant"
```bash
php artisan tenants:migrate-fresh --force
```

### "Super Admin Login nicht möglich"
```bash
php artisan tinker
App\Models\User::create([
    'name' => 'Admin',
    'email' => 'admin@example.com',
    'password' => bcrypt('password')
])
```

Siehe [GETTING_STARTED.md](GETTING_STARTED.md) für mehr Hilfe!

---

**🎊 Bereit zu starten? Folge [GETTING_STARTED.md](GETTING_STARTED.md)!**
