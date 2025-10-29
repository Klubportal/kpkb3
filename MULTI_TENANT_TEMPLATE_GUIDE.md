# Multi-Tenant Template System - Implementierungsanleitung

## 📋 Übersicht

Dein Fußballprojekt hat jetzt ein vollständiges Multi-Tenant Template-System mit:

✅ **Einheitliche Menüstruktur** für alle Vereine
✅ **8 vorgefertigte Themes** (Farben, Layouts, Assets)
✅ **Stancl Tenancy** für Multi-Tenancy
✅ **Theme-Service** für dynamische Template-Auswahl

## 🎨 Verfügbare Themes

| Theme | Farben | Beschreibung |
|-------|--------|--------------|
| `default` | Rot/Grau | Standard Klubportal |
| `blue_ocean` | Blau/Türkis | Modern & Professional |
| `green_forest` | Grün/Braun | Natur & Sport |
| `purple_royal` | Lila/Gold | Premium & Elegant |
| `orange_energy` | Orange/Gelb | Energetisch & Dynamisch |
| `dark_mode` | Dunkel/Neon | Modern Dark Theme |
| `classic_navy` | Navy/Beige | Klassisch & Seriös |
| `sport_red` | Rot/Schwarz | Sportlich & Kraftvoll |

## 📁 Dateistruktur

```
app/
├── Services/
│   ├── MenuService.php          # Zentrale Menü-Struktur
│   └── ThemeService.php         # Theme-Management
resources/
├── views/
│   ├── components/
│   │   └── tenant-menu.blade.php  # Einheitliches Menü
│   └── themes/
│       ├── default/
│       │   ├── layout.blade.php
│       │   ├── home.blade.php
│       │   └── components/
│       ├── modern/
│       ├── classic/
│       └── sport/
public/
└── themes/
    ├── default/
    │   └── assets/
    │       ├── css/
    │       ├── js/
    │       └── images/
    ├── modern/
    └── ...
```

## 🔧 Verwendung

### 1. Menü einbinden (in jedem Layout)

```blade
@php
    $primaryColor = \App\Services\ThemeService::getThemeColors()['primary_color'] ?? '#dc2626';
@endphp

<x-tenant-menu :primaryColor="$primaryColor" />
```

### 2. Theme für Tenant setzen

```php
// Im Filament Admin-Panel oder Settings
use App\Services\ThemeService;

// Theme setzen
ThemeService::setTheme('blue_ocean');

// Aktuelles Theme abrufen
$currentTheme = ThemeService::getCurrentTheme();

// Verfügbare Themes anzeigen
$themes = ThemeService::getAvailableThemes();
```

### 3. Theme-spezifische Views laden

```php
// In Controller
$theme = ThemeService::getCurrentTheme();
return view("themes.{$theme}.home");

// Oder mit Helper
return view(ThemeService::getViewPath('home'));
```

### 4. Theme-Assets laden

```blade
{{-- In Blade Templates --}}
<link rel="stylesheet" href="{{ \App\Services\ThemeService::getAsset('css/style.css') }}">
<script src="{{ \App\Services\ThemeService::getAsset('js/app.js') }}"></script>
```

## 🎯 Menü-Struktur anpassen

Bearbeite `app/Services/MenuService.php`:

```php
public static function getMainMenu(): array
{
    return [
        [
            'label' => 'Startseite',
            'url' => '/',
            'icon' => 'heroicon-o-home',
            'active' => 'home',
        ],
        [
            'label' => 'Mannschaften',
            'icon' => 'heroicon-o-user-group',
            'children' => [
                // Untermenüs
            ],
        ],
        // Weitere Menüpunkte
    ];
}
```

## 🎨 Neues Theme erstellen

1. **Theme-Definition** in `ThemeService.php`:
```php
'my_theme' => [
    'name' => 'Mein Theme',
    'description' => 'Beschreibung',
    'primary_color' => '#3b82f6',
    'secondary_color' => '#1e40af',
    'accent_color' => '#f59e0b',
    'text_color' => '#1f2937',
    'link_color' => '#3b82f6',
    'header_bg' => '#ffffff',
    'footer_bg' => '#1f2937',
    'preview_image' => 'my_theme.png',
],
```

2. **Views erstellen**:
```
resources/views/themes/my_theme/
├── layout.blade.php
├── home.blade.php
├── components/
└── ...
```

3. **Assets erstellen**:
```
public/themes/my_theme/assets/
├── css/
│   └── style.css
├── js/
│   └── app.js
└── images/
```

## 🔐 Theme-Auswahl im Filament Admin

Füge ein Select-Feld in `TemplateSettingResource` hinzu:

```php
Select::make('theme')
    ->label('Design-Template')
    ->options(\App\Services\ThemeService::getAvailableThemes())
    ->default('default')
    ->required()
    ->reactive()
    ->afterStateUpdated(function ($state) {
        \App\Services\ThemeService::setTheme($state);
    }),
```

## 📦 Empfohlene zusätzliche Packages

Falls du noch mehr Features brauchst:

```bash
# Für fortgeschrittenes Menu-Management
composer require spatie/laravel-menu

# Für Asset-Management
composer require laravel/mix

# Für erweiterte Settings
composer require spatie/laravel-settings  # Bereits installiert!
```

## 🚀 Next Steps

1. ✅ Menü ist zentral definiert
2. ✅ 8 Themes sind vorbereitet
3. ✅ Theme-Service ist einsatzbereit
4. ⏭️ Views für jedes Theme erstellen
5. ⏭️ Assets (CSS/JS) pro Theme anpassen
6. ⏭️ Theme-Auswahl in Filament Admin einbauen

## 💡 Best Practices

- **Menü zentral halten**: Alle Änderungen nur in `MenuService.php`
- **Theme-Farben nutzen**: Verwende `ThemeService::getThemeColors()` in Views
- **Cache nutzen**: Menü wird automatisch gecacht (1 Stunde)
- **Fallbacks**: Theme-Service hat Fallbacks für fehlende Views/Assets
- **Mobile First**: Menü ist responsive (Desktop + Mobile)

## 🎯 Vorteile dieser Architektur

✅ **Einheitlich**: Alle Vereine haben dieselbe Menüstruktur
✅ **Flexibel**: Vereine können Farben/Layout wählen
✅ **Wartbar**: Zentrale Menü-Definition
✅ **Skalierbar**: Neue Themes einfach hinzufügen
✅ **Performance**: Menu-Caching, Asset-Optimierung

---

**Fertig!** 🎉 Du hast jetzt ein vollständiges Multi-Tenant Template-System!
