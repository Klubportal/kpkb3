# Theme-System - Dokumentation

## Übersicht

Das Klubportal verfügt über ein flexibles Theme-System mit 10 vorgefertigten Themes und vollständiger Anpassungsmöglichkeit.

## Technologie-Stack

### Frontend/Design
- **TailwindCSS v4.1.16** - Utility-First CSS Framework
- **@tailwindcss/typography** - Typografie-Plugin
- **@tailwindcss/vite** - Vite-Integration
- **Vite v7.1.12** - Build-Tool

### Filament Packages
- **filament/filament v4.1.10** - Admin Panel Framework
- **filament/forms** - Form-Builder
- **filament/widgets** - Dashboard-Widgets
- **filament/notifications** - Notification-System
- **awcodes/filament-badgeable-column** - Spalten mit Badges
- **awcodes/filament-sticky-header** - Sticky Headers
- **pxlrbt/filament-environment-indicator** - Environment-Anzeige

## Verfügbare Themes

### 1. Standard (Klubportal)
**Farben:** Rot & Grau
- **Primary:** #dc2626 (Rot)
- **Secondary:** #737373 (Grau)
- **Accent:** #f59e0b (Amber)

**Verwendung:** Standard-Theme für alle neuen Installationen

### 2. Blauer Ozean
**Farben:** Blau & Türkis
- **Primary:** #0ea5e9 (Sky Blue)
- **Secondary:** #0284c7 (Light Blue)
- **Accent:** #06b6d4 (Cyan)

**Verwendung:** Modern und professionell, ideal für Wassersportvereine

### 3. Grüner Wald
**Farben:** Grün & Braun
- **Primary:** #16a34a (Green)
- **Secondary:** #059669 (Emerald)
- **Accent:** #84cc16 (Lime)

**Verwendung:** Natürlich und sportlich, perfekt für Outdoor-Sportvereine

### 4. Königliches Lila
**Farben:** Lila & Gold
- **Primary:** #9333ea (Purple)
- **Secondary:** #7c3aed (Violet)
- **Accent:** #eab308 (Yellow)

**Verwendung:** Premium und elegant, für exklusive Clubs

### 5. Orange Energie
**Farben:** Orange & Gelb
- **Primary:** #ea580c (Orange)
- **Secondary:** #f97316 (Orange)
- **Accent:** #fbbf24 (Amber)

**Verwendung:** Energetisch und dynamisch, für aktive Sportvereine

### 6. Dark Mode
**Farben:** Dunkel & Neon
- **Primary:** #8b5cf6 (Violet)
- **Secondary:** #6366f1 (Indigo)
- **Accent:** #06b6d4 (Cyan)
- **Background:** Dunkle Töne

**Verwendung:** Modernes Dark Theme für Nachtarbeit

### 7. Klassisch Navy
**Farben:** Navy & Beige
- **Primary:** #1e40af (Blue)
- **Secondary:** #1e3a8a (Blue)
- **Accent:** #d97706 (Amber)

**Verwendung:** Klassisch und seriös, für traditionelle Vereine

### 8. Sport Rot
**Farben:** Rot & Schwarz
- **Primary:** #dc2626 (Red)
- **Secondary:** #b91c1c (Red)
- **Accent:** #fbbf24 (Amber)

**Verwendung:** Sportlich und kraftvoll, ideal für Fußballvereine

### 9. Frisches Teal
**Farben:** Teal & Mint
- **Primary:** #14b8a6 (Teal)
- **Secondary:** #0d9488 (Teal)
- **Accent:** #34d399 (Emerald)

**Verwendung:** Frisch und modern, für junge Vereine

### 10. Elegantes Rosa
**Farben:** Rosa & Grau
- **Primary:** #f43f5e (Rose)
- **Secondary:** #e11d48 (Rose)
- **Accent:** #ec4899 (Pink)

**Verwendung:** Elegant und modern, für diverse Sportarten

## Theme-Einstellungen

### Zugriff
**URL:** `/admin/manage-theme-settings`
**Navigation:** Einstellungen → Theme & Design

### Verfügbare Optionen

#### 1. Vorgefertigte Themes
- **Theme auswählen:** Dropdown mit allen 10 Themes
- **Live-Vorschau:** Farben werden sofort angewendet
- **Auto-Apply:** Farben werden automatisch gesetzt

#### 2. Farbanpassungen
- **Header Hintergrundfarbe**
- **Footer Hintergrundfarbe**
- **Textfarbe**
- **Link-Farbe**

Alle Farben haben ColorPicker-Felder.

#### 3. Design-Optionen

**Schriftart:**
- Inter (Standard)
- Roboto
- Poppins
- Open Sans
- Lato
- Montserrat

**Ecken-Rundung:**
- Keine (0px)
- Klein (4px)
- Mittel (8px)
- Groß (12px)
- Extra Groß (16px)

**Sidebar-Breite:**
- Schmal (200px)
- Normal (250px)
- Breit (300px)

**Button-Stil:**
- Abgerundet
- Eckig
- Pill (vollständig rund)

**Layout-Stil:**
- Volle Breite
- Boxed (begrenzt)

**Dark Mode:**
- Toggle für dunkles Farbschema

## ThemeService

### Verfügbare Methoden

```php
use App\Services\ThemeService;

// Alle Themes abrufen
$themes = ThemeService::getAvailableThemes();

// Spezifisches Theme abrufen
$theme = ThemeService::getTheme('blue_ocean');

// Theme anwenden
use App\Settings\ThemeSettings;
$themeSettings = app(ThemeSettings::class);
ThemeService::applyTheme('blue_ocean', $themeSettings);

// Für Filament Select formatieren
$options = ThemeService::getThemesForSelect();

// HTML-Vorschau generieren
$html = ThemeService::getThemePreviewHtml('blue_ocean');
```

### Theme-Struktur

```php
[
    'name' => 'Blauer Ozean',
    'description' => 'Modern und professionell mit Blau- und Türkistönen',
    'primary_color' => '#0ea5e9',
    'secondary_color' => '#0284c7',
    'accent_color' => '#06b6d4',
    'text_color' => '#0f172a',
    'link_color' => '#0ea5e9',
    'header_bg' => '#f0f9ff',
    'footer_bg' => '#0c4a6e',
    'preview_image' => 'blue_ocean.png',
]
```

## Theme-Anwendung

### Automatische Anwendung (über UI)

1. Öffne `/admin/manage-theme-settings`
2. Wähle ein Theme aus dem Dropdown
3. Farben werden automatisch gesetzt
4. Klicke "Speichern"

### Programmatische Anwendung

```php
use App\Services\ThemeService;
use App\Settings\ThemeSettings;

$themeSettings = app(ThemeSettings::class);

// Theme anwenden
ThemeService::applyTheme('green_forest', $themeSettings);

// Oder manuell setzen
$themeSettings->active_theme = 'purple_royal';
$themeSettings->header_bg_color = '#faf5ff';
$themeSettings->footer_bg_color = '#581c87';
$themeSettings->save();
```

## Integration in Panels

### CentralPanelProvider

```php
use App\Settings\ThemeSettings;

public function panel(Panel $panel): Panel
{
    $themeSettings = app(ThemeSettings::class);
    
    return $panel
        ->colors([
            'primary' => Color::hex($themeSettings->header_bg_color ?? '#dc2626'),
        ])
        ->darkMode($themeSettings->dark_mode_enabled)
        // ... weitere Panel-Konfiguration
}
```

### AdminPanelProvider (Tenant)

```php
// Tenant-Panels können eigene Themes haben oder
// das Central-Theme übernehmen via Cross-Database-Query
use Illuminate\Support\Facades\DB;

$themeSettings = DB::connection('central')
    ->table('settings')
    ->where('group', 'theme')
    ->pluck('payload', 'name')
    ->map(fn($value) => json_decode($value, true));

$primaryColor = $themeSettings['header_bg_color'] ?? '#dc2626';
```

## Custom Themes erstellen

### Neues Theme hinzufügen

Bearbeite `app/Services/ThemeService.php`:

```php
'your_theme' => [
    'name' => 'Ihr Theme-Name',
    'description' => 'Theme-Beschreibung',
    'primary_color' => '#hexcode',
    'secondary_color' => '#hexcode',
    'accent_color' => '#hexcode',
    'text_color' => '#hexcode',
    'link_color' => '#hexcode',
    'header_bg' => '#hexcode',
    'footer_bg' => '#hexcode',
    'preview_image' => 'your_theme.png',
],
```

## TailwindCSS-Konfiguration

Die Farben in `tailwind.config.js` sollten mit den Theme-Farben übereinstimmen:

```javascript
theme: {
    extend: {
        colors: {
            'primary': {
                500: '#dc2626', // Aus ThemeSettings
                // ... weitere Schattierungen
            },
        },
    },
}
```

## Best Practices

### Theme-Auswahl

✅ **DO:**
- Wähle ein Theme das zur Vereinsidentität passt
- Teste das Theme auf verschiedenen Bildschirmgrößen
- Prüfe Kontrast für Barrierefreiheit
- Verwende konsistente Farben

❌ **DON'T:**
- Zu viele verschiedene Farben mischen
- Zu geringen Kontrast (Text vs. Hintergrund)
- Theme zu oft wechseln (Nutzer-Verwirrung)
- Custom CSS überschreiben ohne Theme-System

### Farb-Kontrast

Achte auf WCAG 2.1 Kontrast-Richtlinien:
- **AA Standard:** Mindestens 4.5:1 für normalen Text
- **AAA Standard:** Mindestens 7:1 für normalen Text
- **Große Texte:** Mindestens 3:1

**Tools:**
- [WebAIM Contrast Checker](https://webaim.org/resources/contrastchecker/)
- [Coolors Contrast Checker](https://coolors.co/contrast-checker)

### Performance

- Themes verwenden nur CSS-Variablen (keine zusätzlichen Assets)
- Keine Performance-Einbußen durch Theme-Wechsel
- Farben werden gecached via Spatie Settings

## Empfehlungen nach Vereinstyp

| Vereinstyp | Empfohlene Themes |
|------------|-------------------|
| Fußball | Sport Rot, Orange Energie |
| Wassersport | Blauer Ozean, Frisches Teal |
| Tennis | Klassisch Navy, Grüner Wald |
| Volleyball | Orange Energie, Frisches Teal |
| Handball | Sport Rot, Orange Energie |
| Basketball | Sport Rot, Dark Mode |
| Kampfsport | Dark Mode, Sport Rot |
| Yoga/Pilates | Elegantes Rosa, Frisches Teal |
| Outdoor | Grüner Wald, Orange Energie |
| Premium-Club | Königliches Lila, Klassisch Navy |

## Troubleshooting

### Problem: Theme wird nicht angewendet

**Lösungen:**
1. Cache leeren: `php artisan optimize:clear`
2. Prüfe ob Settings gespeichert wurden
3. Browser-Cache leeren (Ctrl+F5)
4. Prüfe `ThemeSettings` in Datenbank

### Problem: Farben stimmen nicht

**Lösungen:**
1. Prüfe tailwind.config.js
2. Rebuild mit `npm run build`
3. Stelle sicher dass Hex-Codes korrekt sind
4. Prüfe ob Custom CSS Farben überschreibt

### Problem: Dark Mode funktioniert nicht

**Lösungen:**
1. Toggle in Theme-Settings aktivieren
2. Filament Panel muss darkMode-Support haben
3. TailwindCSS darkMode: 'class' in Config

## Zusammenfassung

✅ 10 vorgefertigte Themes verfügbar
✅ Vollständig anpassbar über UI
✅ Live-Vorschau bei Theme-Auswahl
✅ TailwindCSS v4 + Filament v4
✅ ColorPicker für alle Farben
✅ Schriftart, Rundung, Layout konfigurierbar
✅ Dark Mode Support
✅ Performance-optimiert
✅ WCAG-konform bei richtiger Nutzung

**Nächste Schritte:**
1. Öffne `/admin/manage-theme-settings`
2. Wähle ein Theme aus
3. Passe Farben an (optional)
4. Speichern
5. Genieße das neue Design!
