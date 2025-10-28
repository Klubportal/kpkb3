# ===============================================
# HOSTS-DATEI AUTOMATISCH AKTUALISIEREN
# ===============================================
# Dieses Script muss ALS ADMINISTRATOR ausgeführt werden!
#
# Rechtsklick auf PowerShell → "Als Administrator ausführen"
# Dann: .\update-hosts.ps1
# ===============================================

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "  HOSTS-DATEI AKTUALISIEREN" -ForegroundColor Cyan
Write-Host "========================================`n" -ForegroundColor Cyan

# Administrator-Rechte prüfen
$currentPrincipal = New-Object Security.Principal.WindowsPrincipal([Security.Principal.WindowsIdentity]::GetCurrent())
$isAdmin = $currentPrincipal.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)

if (-not $isAdmin) {
    Write-Host "❌ FEHLER: Dieses Script muss als Administrator ausgeführt werden!" -ForegroundColor Red
    Write-Host "`nBitte:" -ForegroundColor Yellow
    Write-Host "1. PowerShell als Administrator öffnen" -ForegroundColor White
    Write-Host "2. Script erneut ausführen: .\update-hosts.ps1`n" -ForegroundColor White
    pause
    exit 1
}

$hostsPath = "C:\Windows\System32\drivers\etc\hosts"
$domain = "testclub.localhost"

Write-Host "📝 hosts-Datei Pfad: $hostsPath" -ForegroundColor Gray
Write-Host "🌐 Domain hinzufügen: $domain`n" -ForegroundColor Gray

# hosts-Datei lesen
$hostsContent = Get-Content $hostsPath -Raw

# Prüfen ob Domain bereits existiert
if ($hostsContent -match [regex]::Escape($domain)) {
    Write-Host "ℹ️  Domain '$domain' ist bereits in der hosts-Datei vorhanden!" -ForegroundColor Yellow
    Write-Host "✅ Keine Änderung notwendig.`n" -ForegroundColor Green
} else {
    # Eintrag hinzufügen
    $newEntry = "`n127.0.0.1       $domain"
    Add-Content -Path $hostsPath -Value $newEntry -NoNewline

    Write-Host "✅ Domain '$domain' erfolgreich hinzugefügt!" -ForegroundColor Green
    Write-Host "`n📋 Neue hosts-Datei Einträge:" -ForegroundColor Cyan
    Write-Host "127.0.0.1       testclub.localhost`n" -ForegroundColor White
}

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  NÄCHSTE SCHRITTE" -ForegroundColor Cyan
Write-Host "========================================`n" -ForegroundColor Cyan

Write-Host "1. ✅ hosts-Datei aktualisiert" -ForegroundColor Green
Write-Host "`n2. Verbindung testen:" -ForegroundColor Yellow
Write-Host "   ping testclub.localhost" -ForegroundColor White
Write-Host "   (Sollte antworten mit: Antwort von 127.0.0.1)`n" -ForegroundColor Gray

Write-Host "3. Cache leeren:" -ForegroundColor Yellow
Write-Host "   php artisan optimize:clear`n" -ForegroundColor White

Write-Host "4. Server starten:" -ForegroundColor Yellow
Write-Host "   php artisan serve`n" -ForegroundColor White

Write-Host "5. Im Browser öffnen:" -ForegroundColor Yellow
Write-Host "   http://testclub.localhost:8000/club`n" -ForegroundColor Cyan

Write-Host "========================================`n" -ForegroundColor Cyan

# Verbindung testen
Write-Host "🔍 Teste Verbindung zu testclub.localhost..." -ForegroundColor Yellow
$pingResult = Test-Connection -ComputerName "testclub.localhost" -Count 1 -Quiet

if ($pingResult) {
    Write-Host "✅ Ping erfolgreich! Domain ist erreichbar.`n" -ForegroundColor Green
} else {
    Write-Host "⚠️  Ping fehlgeschlagen. Bitte manuell prüfen.`n" -ForegroundColor Yellow
}

pause
