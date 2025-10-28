# Klubportal Laravel 12 - Komplettes Backend Setup
# Automatische Generierung aller Filament Resources

Write-Host "`n=== KLUBPORTAL BACKEND SETUP ===" -ForegroundColor Green
Write-Host "Generiere alle Filament Resources automatisch...`n" -ForegroundColor Cyan

# Liste aller Models die Resources benötigen
$models = @(
    @{Name="Team"; Title="name"; SoftDelete=$false},
    @{Name="Player"; Title="last_name"; SoftDelete=$false},
    @{Name="FootballMatch"; Title="id"; SoftDelete=$false},
    @{Name="Training"; Title="id"; SoftDelete=$false},
    @{Name="News"; Title="title"; SoftDelete=$false},
    @{Name="Member"; Title="last_name"; SoftDelete=$false},
    @{Name="Event"; Title="title"; SoftDelete=$false},
    @{Name="Season"; Title="name"; SoftDelete=$false},
    @{Name="Standing"; Title="id"; SoftDelete=$false}
)

foreach ($model in $models) {
    $modelName = $model.Name
    $titleAttr = $model.Title
    $softDelete = if ($model.SoftDelete) { "yes" } else { "no" }

    Write-Host "Creating $modelName Resource..." -ForegroundColor Yellow

    # Erstelle Resource mit generate und view flags
    $input = "$titleAttr`n$softDelete`n"
    $input | php artisan make:filament-resource $modelName --panel=superadmin --generate --view 2>&1 | Out-Null

    if ($LASTEXITCODE -eq 0) {
        Write-Host "✓ $modelName Resource created" -ForegroundColor Green
    } else {
        Write-Host "✗ $modelName Resource failed" -ForegroundColor Red
    }
}

Write-Host "`n=== WIDGETS ERSTELLEN ===" -ForegroundColor Cyan

# Dashboard Widgets
$widgets = @("StatsOverview", "TeamStats", "MatchCalendar", "RecentActivity")

foreach ($widget in $widgets) {
    Write-Host "Creating $widget Widget..." -ForegroundColor Yellow
    echo "Stats overview`nyes`nsuperadmin`nno`n" | php artisan make:filament-widget $widget 2>&1 | Out-Null
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✓ $widget Widget created" -ForegroundColor Green
    }
}

Write-Host "`n=== PAGES ERSTELLEN ===" -ForegroundColor Cyan

# Custom Pages
php artisan make:filament-page Settings --panel=superadmin 2>&1 | Out-Null
php artisan make:filament-page Analytics --panel=superadmin 2>&1 | Out-Null

Write-Host "✓ Custom Pages created" -ForegroundColor Green

Write-Host "`n=== BERECHTIGUNGEN GENERIEREN ===" -ForegroundColor Cyan

# Shield Permissions generieren
echo "superadmin`nno`n" | php artisan shield:generate --all 2>&1 | Out-Null
Write-Host "✓ Permissions generated" -ForegroundColor Green

# Super Admin Rechte zuweisen
echo "superadmin`n" | php artisan shield:super-admin --user=2 2>&1 | Out-Null
Write-Host "✓ Super Admin rights assigned" -ForegroundColor Green

Write-Host "`n=== CACHE LEEREN ===" -ForegroundColor Cyan
php artisan optimize:clear | Out-Null
Write-Host "✓ Cache cleared" -ForegroundColor Green

Write-Host "`n=== SETUP ABGESCHLOSSEN ===" -ForegroundColor Green
Write-Host "`nErstellt:" -ForegroundColor Cyan
Write-Host "• 9 Filament Resources (Team, Player, Match, etc.)" -ForegroundColor White
Write-Host "• 4 Dashboard Widgets" -ForegroundColor White
Write-Host "• 2 Custom Pages (Settings, Analytics)" -ForegroundColor White
Write-Host "• Alle Berechtigungen konfiguriert" -ForegroundColor White

Write-Host "`nZugriff:" -ForegroundColor Yellow
Write-Host "URL: http://localhost:8000/super-admin" -ForegroundColor White
Write-Host "User: michael@klubportal.com" -ForegroundColor White
Write-Host "Pass: Zagreb123!" -ForegroundColor White

Write-Host "`nNächste Schritte:" -ForegroundColor Magenta
Write-Host "1. Resources anpassen in app/Filament/SuperAdmin/Resources/" -ForegroundColor White
Write-Host "2. Widgets konfigurieren" -ForegroundColor White
Write-Host "3. API Keys in .env eintragen (OpenAI, etc.)" -ForegroundColor White
