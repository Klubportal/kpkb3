# ========================================
# Klubportal Laravel 12 - RESTORE SCRIPT
# ========================================

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "   KLUBPORTAL BACKUP WIEDERHERSTELLEN" -ForegroundColor Cyan
Write-Host "========================================`n" -ForegroundColor Cyan

# Verfügbare Backups anzeigen
Write-Host "Verfügbare Backups:`n" -ForegroundColor Yellow

$backups = Get-ChildItem "backups\backup_*.zip" -ErrorAction SilentlyContinue |
    Sort-Object LastWriteTime -Descending

if ($backups.Count -eq 0) {
    Write-Host "Keine Backups gefunden!`n" -ForegroundColor Red
    exit
}

for ($i = 0; $i -lt $backups.Count; $i++) {
    $size = $backups[$i].Length / 1MB
    $date = $backups[$i].LastWriteTime
    Write-Host "$($i + 1). $($backups[$i].Name)" -ForegroundColor White
    Write-Host "   Datum: $($date.ToString('dd.MM.yyyy HH:mm:ss')) | Größe: $([math]::Round($size, 2)) MB" -ForegroundColor Gray
    Write-Host ""
}

# Backup auswählen
$selection = Read-Host "Welches Backup möchtest du wiederherstellen? (1-$($backups.Count))"
$selectedBackup = $backups[$selection - 1]

if (-not $selectedBackup) {
    Write-Host "`nUngültige Auswahl!`n" -ForegroundColor Red
    exit
}

Write-Host "`n⚠️  WARNUNG: Dies überschreibt die aktuellen Daten!" -ForegroundColor Red
$confirm = Read-Host "Möchtest du wirklich '$($selectedBackup.Name)' wiederherstellen? (ja/nein)"

if ($confirm -ne "ja") {
    Write-Host "`nWiederherstellung abgebrochen.`n" -ForegroundColor Yellow
    exit
}

# ========================================
# BACKUP ENTPACKEN
# ========================================
Write-Host "`n1. Entpacke Backup..." -ForegroundColor Yellow

$tempDir = "backups\temp_restore"
if (Test-Path $tempDir) {
    Remove-Item $tempDir -Recurse -Force
}

Expand-Archive -Path $selectedBackup.FullName -DestinationPath $tempDir -Force
$backupContent = Get-ChildItem $tempDir | Select-Object -First 1
Write-Host "   ✓ Backup entpackt" -ForegroundColor Green

# ========================================
# DATENBANKEN WIEDERHERSTELLEN
# ========================================
Write-Host "`n2. Stelle Datenbanken wieder her..." -ForegroundColor Yellow

# Central DB
if (Test-Path "$backupContent\database\central.sqlite") {
    Copy-Item "$backupContent\database\central.sqlite" "database\central.sqlite" -Force
    Write-Host "   ✓ Central DB wiederhergestellt" -ForegroundColor Green
}

# Tenant DBs
$tenantBackups = Get-ChildItem "$backupContent\database\*.sqlite" -Exclude "central.sqlite" -ErrorAction SilentlyContinue
if ($tenantBackups) {
    if (-not (Test-Path "database\tenant")) {
        New-Item -ItemType Directory -Path "database\tenant" -Force | Out-Null
    }
    foreach ($db in $tenantBackups) {
        Copy-Item $db.FullName "database\tenant\$($db.Name)" -Force
    }
    Write-Host "   ✓ $($tenantBackups.Count) Tenant DB(s) wiederhergestellt" -ForegroundColor Green
}

# ========================================
# DATEIEN WIEDERHERSTELLEN
# ========================================
Write-Host "`n3. Stelle Dateien wieder her..." -ForegroundColor Yellow

# Storage
if (Test-Path "$backupContent\files\storage_public") {
    if (-not (Test-Path "storage\app\public")) {
        New-Item -ItemType Directory -Path "storage\app\public" -Force | Out-Null
    }
    Copy-Item -Path "$backupContent\files\storage_public\*" -Destination "storage\app\public\" -Recurse -Force
    Write-Host "   ✓ Storage wiederhergestellt" -ForegroundColor Green
}

# Public Files
if (Test-Path "$backupContent\files\public_images") {
    Copy-Item -Path "$backupContent\files\public_images\*" -Destination "public\images\" -Recurse -Force
    Write-Host "   ✓ Public Images wiederhergestellt" -ForegroundColor Green
}

# Logo & Favicon
if (Test-Path "$backupContent\files\logo.svg") {
    Copy-Item "$backupContent\files\logo.svg" "public\" -Force
}
if (Test-Path "$backupContent\files\favicon.ico") {
    Copy-Item "$backupContent\files\favicon.ico" "public\" -Force
}

# .env (mit Warnung)
if (Test-Path "$backupContent\files\.env") {
    Write-Host "`n   ⚠️  .env Datei gefunden im Backup" -ForegroundColor Yellow
    $envRestore = Read-Host "   Möchtest du die .env überschreiben? (ja/nein)"
    if ($envRestore -eq "ja") {
        Copy-Item "$backupContent\files\.env" ".env" -Force
        Write-Host "   ✓ .env wiederhergestellt" -ForegroundColor Green
    } else {
        Write-Host "   - .env übersprungen" -ForegroundColor Gray
    }
}

# ========================================
# AUFRÄUMEN
# ========================================
Write-Host "`n4. Räume auf..." -ForegroundColor Yellow
Remove-Item $tempDir -Recurse -Force
Write-Host "   ✓ Temporäre Dateien gelöscht" -ForegroundColor Green

# ========================================
# POST-RESTORE BEFEHLE
# ========================================
Write-Host "`n5. Führe Post-Restore Befehle aus..." -ForegroundColor Yellow

# Storage Link
php artisan storage:link 2>&1 | Out-Null
Write-Host "   ✓ Storage Link erstellt" -ForegroundColor Green

# Cache leeren
php artisan optimize:clear 2>&1 | Out-Null
Write-Host "   ✓ Cache geleert" -ForegroundColor Green

# ========================================
# FERTIG
# ========================================
Write-Host "`n========================================" -ForegroundColor Green
Write-Host "   WIEDERHERSTELLUNG ERFOLGREICH!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green

Write-Host "`nWICHTIG:" -ForegroundColor Yellow
Write-Host "1. Prüfe die .env Datei (Datenbankpfade, APP_KEY, etc.)" -ForegroundColor White
Write-Host "2. Teste die Anwendung gründlich" -ForegroundColor White
Write-Host "3. Prüfe ob alle Uploads/Bilder sichtbar sind`n" -ForegroundColor White
