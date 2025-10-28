# Multi-Template System - Dokumentation

## Übersicht

Das Multi-Template System ermöglicht es Tenants, zwischen verschiedenen Design-Templates für ihre Website zu wählen.

## Komponenten

### 1. Template Model (`App\Models\Central\Template`)

Eigenschaften:
- `name` - Template Name (z.B. "Klubportal Standard")
- `slug` - Eindeutiger Slug (z.B. "kp", "modern", "classic")
- `description` - Beschreibung des Templates
- `preview_image` - Vorschaubild
- `features` - JSON Array mit verfügbaren Features
- `colors` - JSON Array mit Farbschema
- `layout_path` - Blade Layout Pfad (z.B. "layouts.modern")
- `is_active` - Template ist verfügbar
- `is_default` - Standard-Template

### 2. Templates Verwalten (Central Admin)

**Zugriff:** http://localhost:8000/admin/templates

Hier können Super-Admins:
- Neue Templates erstellen
- Vorhandene Templates bearbeiten
- Template-Features definieren
- Farbschemata festlegen
- Templates aktivieren/deaktivieren

### 3. Template Auswahl (Tenant)

**Zugriff:** http://nknapijed.localhost:8000/club/clubs (Club bearbeiten)

Im Central Admin kann bei der Club-Erstellung/Bearbeitung das Template ausgewählt werden:
- Feld: "Website-Template"
- Dropdown mit allen aktiven Templates

### 4. Template Loader Service

Der `TemplateLoaderService` lädt automatisch das richtige Template basierend auf dem aktuellen Tenant.

**Verwendung in Blade:**

```blade
{{-- Aktuelles Template abrufen --}}
@inject('templateLoader', 'App\Services\TemplateLoaderService')

{{-- Layout verwenden --}}
@extends($templateLoader->getLayoutPath())

{{-- Farbe abrufen --}}
<div style="background-color: {{ $templateLoader->getColor('primary') }}">

{{-- Feature-Check --}}
@if($templateLoader->hasFeature('hero_slider'))
    <x-hero-slider />
@endif

{{-- Blade Directives --}}
@templateColor('primary', '#000000')

@hasFeature('match_center')
    <x-match-center />
@endHasFeature
```

**Verwendung in Controllern:**

```php
use App\Services\TemplateLoaderService;

class HomeController extends Controller
{
    public function index(TemplateLoaderService $templateLoader)
    {
        $template = $templateLoader->getCurrentTemplate();
        $colors = $templateLoader->getColors();
        $layout = $templateLoader->getLayoutPath();
        
        return view('home', compact('template', 'colors'));
    }
}
```

### 5. View Sharing

Template-Daten werden automatisch mit allen Views geteilt:

```blade
{{-- In jedem View verfügbar --}}
{{ $currentTemplate->name }}
{{ $templateColors['primary'] }}
@foreach($templateFeatures as $feature)
    {{ $feature }}
@endforeach
```

### 6. CSS Variablen

CSS Variablen für Template-Farben generieren:

```blade
<style>
    {!! app('template.loader')->getCssVariables() !!}
</style>

{{-- Ergebnis: --}}
<style>
    :root {
        --color-primary: #1e40af;
        --color-secondary: #dc2626;
        --color-accent: #f59e0b;
    }
</style>
```

## Standard-Templates

### 1. Klubportal Standard (slug: kp)
- **Farben:** Blau (#1e40af), Rot (#dc2626)
- **Features:** Responsive, Dark/Light Mode, COMET, Match Center, Player Stats, News, Gallery
- **Layout:** `layouts.frontend`

### 2. Modern Pro (slug: modern)
- **Farben:** Cyan (#0ea5e9), Lila (#8b5cf6)
- **Features:** Minimal Design, Fast Loading, Clean Layout, Mobile First
- **Layout:** `layouts.modern`

### 3. Classic Sport (slug: classic)
- **Farben:** Grün (#15803d), Rot (#be123c)
- **Features:** Traditional Layout, Club Heritage, Classic Navigation
- **Layout:** `layouts.classic`

## Template-spezifische Views

Templates können eigene Views haben:

```
resources/views/
  └── templates/
      ├── kp/
      │   ├── home.blade.php
      │   └── about.blade.php
      ├── modern/
      │   └── home.blade.php
      └── classic/
          └── home.blade.php
```

**Automatisches Laden:**

```php
// Lädt templates.modern.home wenn vorhanden, sonst home
return view($templateLoader->getView('home'));
```

## Datenbank Schema

### templates Tabelle
```sql
CREATE TABLE templates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT,
    preview_image VARCHAR(255),
    features JSON,
    colors JSON,
    layout_path VARCHAR(255) DEFAULT 'layouts.frontend',
    is_active BOOLEAN DEFAULT true,
    is_default BOOLEAN DEFAULT false,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### tenants Tabelle
```sql
ALTER TABLE tenants ADD COLUMN template_id BIGINT UNSIGNED;
ALTER TABLE tenants ADD FOREIGN KEY (template_id) REFERENCES templates(id);
```

## Beispiel: Neues Template erstellen

1. **Im Central Admin:**
   - Navigation → Templates → Erstellen
   - Name: "Sport Pro"
   - Slug: "sport-pro"
   - Features: ["match_center", "live_ticker", "statistics"]
   - Colors: {"primary": "#e11d48", "secondary": "#0f172a"}
   - Layout Path: "layouts.sport-pro"
   - Als aktiv markieren

2. **Layout erstellen:**
   ```blade
   {{-- resources/views/layouts/sport-pro.blade.php --}}
   <!DOCTYPE html>
   <html>
   <head>
       <style>
           :root {
               --primary: {{ $templateColors['primary'] ?? '#e11d48' }};
               --secondary: {{ $templateColors['secondary'] ?? '#0f172a' }};
           }
       </style>
   </head>
   <body>
       @yield('content')
   </body>
   </html>
   ```

3. **Template zuweisen:**
   - Central Admin → Clubs → Club bearbeiten
   - "Website-Template" → "Sport Pro" auswählen
   - Speichern

## Best Practices

1. **Fallbacks verwenden:**
   ```php
   $color = $templateLoader->getColor('primary', '#000000');
   ```

2. **Feature-Checks vor Ausgabe:**
   ```blade
   @if($templateLoader->hasFeature('hero_slider'))
       <x-hero-slider />
   @endif
   ```

3. **Template-spezifische Komponenten:**
   ```blade
   {{-- components/templates/modern/hero.blade.php --}}
   <div class="modern-hero">...</div>
   ```

4. **CSS Variablen nutzen:**
   ```css
   .button-primary {
       background-color: var(--color-primary);
   }
   ```

## Fehlerbehebung

**Problem:** Template wird nicht geladen
- Prüfen ob Template aktiv ist (`is_active = true`)
- Prüfen ob Tenant `template_id` gesetzt hat
- Cache leeren: `php artisan cache:clear`

**Problem:** Views werden nicht gefunden
- Layout Path prüfen in Template-Einstellungen
- View-Pfad prüfen: `resources/views/{layout_path}.blade.php`

**Problem:** Farben werden nicht angezeigt
- `colors` JSON in Template-Einstellungen prüfen
- CSS Variablen im `<head>` einfügen

