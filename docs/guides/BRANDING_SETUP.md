# Zentrale Branding-Konfiguration für Klubportal

## ✅ Was wurde implementiert

### 1. Zentrale Settings in der Datenbank
- Tabelle: `settings` (bereits vorhanden)
- Gruppe: `general`
- Felder für Branding:
  - `site_name` - Website Name
  - `logo` - Logo Pfad
  - `favicon` - Favicon Pfad
  - `site_description` - Beschreibung
  - `primary_color`, `secondary_color` - Farben
  - `font_family`, `font_size` - Schriftarten
  - `contact_email`, `phone` - Kontaktdaten

### 2. CentralPanelProvider (✅ FERTIG)
**Datei**: `app/Providers/Filament/CentralPanelProvider.php`

Das Central Panel lädt nun automatisch:
- Logo aus Settings oder Fallback zu `public/images/logo.svg`
- Favicon aus Settings oder Fallback  
- Site Name aus Settings oder "Klubportal Central"

```php
$settings = app(GeneralSettings::class);
$brandName = $settings->site_name ?? 'Klubportal Central';
$logo = $settings->logo ? Storage::url($settings->logo) : asset('images/logo.svg');
$favicon = $settings->favicon ? Storage::url($settings->favicon) : asset('images/logo.svg');

return $panel
    ->brandName($brandName)
    ->favicon($favicon)
    ->brandLogo($logo)
    ->brandLogoHeight('2.5rem')
    // ...
```

### 3. AdminPanelProvider für Tenants (✅ FERTIG)
**Datei**: `app/Providers/Filament/AdminPanelProvider.php`

Das Tenant Admin Panel lädt Settings aus der **Central Database**:
```php
// Cross-database query zur Central DB
$settings = DB::connection('central')
    ->table('settings')
    ->where('group', 'general')
    ->pluck('payload', 'name')
    ->map(fn($value) => json_decode($value, true));
```

Mit Fallback-Logik falls Settings nicht verfügbar sind.

## 🎯 Wie du es verwendest

### Logo & Favicon hochladen

#### Option 1: Direkt in der Datenbank (schnell)
```sql
-- In der klubportal_landlord (central) Datenbank
UPDATE settings 
SET payload = '"branding/logo.svg"'  
WHERE `group` = 'general' AND name = 'logo';

UPDATE settings 
SET payload = '"branding/favicon.ico"'  
WHERE `group` = 'general' AND name = 'favicon';
```

#### Option 2: Über die Settings UI (in Arbeit)
Die Settings-Page ist vorbereitet, hat aber ein Kompatibilitätsproblem mit Filament v4.
Als Alternative kannst du:

1. **Manuell Settings bearbeiten:**
```bash
php artisan tinker
```

```php
// Logo hochladen
$settings = app(\App\Settings\GeneralSettings::class);
$settings->logo = 'branding/my-logo.svg';
$settings->favicon = 'branding/my-favicon.ico';
$settings->site_name = 'Mein Verein Portal';
$settings->save();
```

2. **Files in Storage legen:**
```bash
# Kopiere dein Logo
copy my-logo.svg storage/app/public/branding/logo.svg

# Erstelle Symlink falls noch nicht vorhanden
php artisan storage:link
```

### Site Name ändern

```bash
php artisan tinker
```
```php
$settings = app(\App\Settings\GeneralSettings::class);
$settings->site_name = 'FC Beispielverein Portal';
$settings->save();
```

Dann Cache leeren:
```bash
php artisan optimize:clear
```

## 📁 Dateistruktur

```
storage/app/public/branding/
├── logo.svg          # Hauptlogo (wird in Navigation angezeigt)
├── favicon.ico       # Favicon für Browser-Tab
└── ...

public/images/
└── logo.svg          # Fallback Logo (wenn Settings leer)
```

## 🔄 Wie die Synchronisation funktioniert

1. **Central Backend** (`/admin`):
   - Lädt Settings direkt aus `GeneralSettings` Klasse
   - Verwendet Central Database Connection
   - Zeigt aktuelles Logo/Favicon sofort nach Änderung

2. **Tenant Backend** (`subdomain.localhost:8000/admin`):
   - Lädt Settings über `DB::connection('central')`
   - Cross-Database Query zur zentralen Datenbank
   - Alle Tenants sehen dasselbe Branding
   - Cache-Clearing erforderlich nach Änderungen

## ⚙️ Konfiguration

### Farben anpassen
```php
// In CentralPanelProvider.php
->colors([
    'primary' => Color::Blue,  // Ändere Hauptfarbe
])

// In AdminPanelProvider.php  
->colors([
    'primary' => Color::Amber,  // Tenant Farbe
])
```

### Logo-Höhe anpassen
```php
->brandLogoHeight('2.5rem')  // Größer: '3rem', Kleiner: '2rem'
```

## 🐛 Troubleshooting

### Logo wird nicht angezeigt
1. Prüfe ob File existiert:
   ```bash
   Test-Path storage/app/public/branding/logo.svg
   ```

2. Prüfe Storage Link:
   ```bash
   php artisan storage:link
   ```

3. Prüfe Settings:
   ```bash
   php artisan tinker --execute="echo app(\App\Settings\GeneralSettings::class)->logo;"
   ```

### Änderungen werden nicht übernommen
```bash
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
```

### Tenant sieht falsches Logo
- Stelle sicher dass `DB::connection('central')` in AdminPanelProvider funktioniert
- Prüfe `.env` dass `DB_CONNECTION_CENTRAL` korrekt ist

## 📝 Nächste Schritte

1. **Settings UI Page reparieren** (optional):
   - Filament v4 Enum-Kompatibilität lösen
   - Oder alternative UI mit Resource statt SettingsPage

2. **Erweiterte Features**:
   - Tenant-spezifische Logos (Override von Central)
   - Dark Mode Logos
   - Multi-Size Favicon Support

3. **Caching optimieren**:
   - Settings in Redis Cache
   - Panel Provider Caching

## 🎨 Beispiel-Verwendung

```bash
# 1. Logo hochladen
copy C:\Users\you\Downloads\vereinslogo.svg storage\app\public\branding\logo.svg

# 2. Settings aktualisieren
php artisan tinker
```

```php
$s = app(\App\Settings\GeneralSettings::class);
$s->logo = 'branding/logo.svg';
$s->favicon = 'branding/logo.svg';  // Nutze gleiches Logo
$s->site_name = 'FC Sportverein Admin';
$s->save();
exit;
```

```bash
# 3. Cache leeren
php artisan optimize:clear

# 4. Panel öffnen
# Central: http://localhost:8000/admin
# Tenant: http://testclub.localhost:8000/admin
```

Beide Panels zeigen jetzt dein Logo und Site Name! 🎉
