# Klubportal - Backend Setup Anleitung

## Automatische Resource-Generierung

Um alle Filament Resources automatisch zu erstellen, führe folgende Befehle einzeln aus:

```bash
# 1. Team Resource
php artisan make:filament-resource Team --panel=superadmin --simple

# 2. Player Resource  
php artisan make:filament-resource Player --panel=superadmin --simple

# 3. Match Resource
php artisan make:filament-resource FootballMatch --panel=superadmin --simple

# 4. Training Resource
php artisan make:filament-resource Training --panel=superadmin --simple

# 5. News Resource
php artisan make:filament-resource News --panel=superadmin --simple

# 6. Member Resource
php artisan make:filament-resource Member --panel=superadmin --simple

# 7. Event Resource
php artisan make:filament-resource Event --panel=superadmin --simple

# 8. Season Resource
php artisan make:filament-resource Season --panel=superadmin --simple

# 9. Standing Resource
php artisan make:filament-resource Standing --panel=superadmin --simple
```

## Oder: Alle auf einmal (Kopiere diesen Block in PowerShell)

```powershell
php artisan make:filament-resource Team --panel=superadmin --simple
php artisan make:filament-resource Player --panel=superadmin --simple
php artisan make:filament-resource FootballMatch --panel=superadmin --simple
php artisan make:filament-resource Training --panel=superadmin --simple
php artisan make:filament-resource News --panel=superadmin --simple
php artisan make:filament-resource Member --panel=superadmin --simple
php artisan make:filament-resource Event --panel=superadmin --simple
php artisan make:filament-resource Season --panel=superadmin --simple
php artisan make:filament-resource Standing --panel=superadmin --simple
```

## Nach der Generierung

### 1. Berechtigungen generieren
```bash
php artisan shield:generate --all
# Wähle: superadmin
# Wähle: no

php artisan shield:super-admin --user=2
# Wähle: superadmin
```

### 2. Cache leeren
```bash
php artisan optimize:clear
```

### 3. Zugriff
- URL: http://localhost:8000/super-admin
- User: michael@klubportal.com
- Pass: Zagreb123!

## Was wurde erstellt?

### Resources (in `app/Filament/SuperAdmin/Resources/`)
- TeamResource.php
- PlayerResource.php
- FootballMatchResource.php
- TrainingResource.php
- NewsResource.php
- MemberResource.php
- EventResource.php
- SeasonResource.php
- StandingResource.php

Jede Resource hat automatisch:
- ✅ Formular zum Erstellen/Bearbeiten
- ✅ Tabelle mit Spalten aus der Datenbank
- ✅ Filter
- ✅ Aktionen (Edit, Delete)
- ✅ Pagination

## Erweitern der Resources

### Beispiel: Media Library zu Team hinzufügen

Öffne `app/Filament/SuperAdmin/Resources/TeamResource.php`:

```php
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;

public static function form(Form $form): Form
{
    return $form
        ->schema([
            // ... existing fields
            
            SpatieMediaLibraryFileUpload::make('logo')
                ->label('Team Logo')
                ->collection('team_logos')
                ->image()
                ->maxSize(2048),
        ]);
}
```

### Beispiel: Tags hinzufügen

```php
use Filament\Forms\Components\SpatieTagsInput;

SpatieTagsInput::make('tags')
    ->label('Tags'),
```

### Beispiel: Google Maps hinzufügen

```php
use Cheesegrits\FilamentGoogleMaps\Fields\Map;

Map::make('location')
    ->mapControls([
        'mapTypeControl' => true,
        'scaleControl' => true,
        'streetViewControl' => true,
        'rotateControl' => true,
        'fullscreenControl' => true,
    ])
    ->height('400px')
    ->defaultZoom(15),
```

### Beispiel: KI-Integration

```php
use Filament\Forms\Components\Actions\Action;
use App\Services\AIService;

TextInput::make('description')
    ->suffixAction(
        Action::make('generate')
            ->icon('heroicon-o-sparkles')
            ->action(function (Set $set, Get $get) {
                $ai = new AIService();
                $text = $ai->generateWithOpenAI("Beschreibe: " . $get('name'));
                $set('description', $text);
            })
    )
```

## Widgets erstellen

```bash
# Stats Widget
php artisan make:filament-widget StatsOverview --panel=superadmin
# Wähle: Stats overview

# Chart Widget
php artisan make:filament-widget TeamChart --panel=superadmin
# Wähle: Chart

# Table Widget
php artisan make:filament-widget RecentMatches --panel=superadmin
# Wähle: Table
```

Widgets werden in `app/Filament/SuperAdmin/Widgets/` erstellt.

## Navigation anpassen

In `app/Providers/Filament/SuperAdminPanelProvider.php`:

```php
->navigationGroups([
    'Vereinsverwaltung',
    'Spielbetrieb',
    'Mitglieder',
    'Einstellungen',
])
```

In jeder Resource:

```php
protected static ?string $navigationGroup = 'Vereinsverwaltung';
protected static ?int $navigationSort = 1;
```

## Probleme?

### Resource wird nicht angezeigt
```bash
php artisan optimize:clear
php artisan shield:generate --all
```

### Keine Berechtigung
```bash
php artisan shield:super-admin --user=2
```

### Fehler beim Speichern
Prüfe die Fillable-Properties im Model:
```php
protected $fillable = ['name', 'slug', 'category', ...];
```

## Nächste Schritte

1. **Anpassen**: Resources an deine Bedürfnisse anpassen
2. **Relations**: Beziehungen zwischen Models hinzufügen
3. **Validierung**: Validierungsregeln einfügen
4. **API Keys**: OpenAI, Anthropic Keys in .env eintragen
5. **Testen**: System ausgiebig testen
6. **Styling**: Logo und Farben anpassen

## Support

Bei Fragen siehe:
- `AI-INTEGRATION.md` - KI-Funktionen
- Filament Docs: https://filamentphp.com
- Laravel Docs: https://laravel.com/docs
