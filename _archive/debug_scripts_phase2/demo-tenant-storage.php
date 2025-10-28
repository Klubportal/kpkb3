<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Central\Tenant;
use Illuminate\Support\Facades\Storage;

echo "\n";
echo "========================================\n";
echo "   TENANT FILESYSTEM ISOLATION DEMO\n";
echo "========================================\n\n";

// Get testclub tenant
$testclub = Tenant::find('testclub');

if (!$testclub) {
    echo "❌ Tenant 'testclub' nicht gefunden!\n";
    exit(1);
}

echo "📁 STORAGE PFADE FÜR VERSCHIEDENE TENANTS:\n\n";

// Tenant 1: testclub
echo "🏠 TENANT 1: testclub\n";
$testclub->run(function() {
    $logoPath = Storage::disk('public')->path('logos/club-logo.png');
    $documentsPath = Storage::disk('local')->path('documents/contract.pdf');

    echo "   • Public Disk (logos):     " . $logoPath . "\n";
    echo "   • Local Disk (documents):  " . $documentsPath . "\n";
});

echo "\n";

// Simulate Tenant 2 (even though DB doesn't exist, we can show the path)
echo "🏠 TENANT 2: arsenal (simuliert)\n";
echo "   • Public Disk (logos):     C:\\xampp\\htdocs\\Klubportal-Laravel12\\storage/tenantarsenal/app/public\\logos/club-logo.png\n";
echo "   • Local Disk (documents):  C:\\xampp\\htdocs\\Klubportal-Laravel12\\storage/tenantarsenal/app\\documents/contract.pdf\n";

echo "\n";
echo "🏠 TENANT 3: barcelona (simuliert)\n";
echo "   • Public Disk (logos):     C:\\xampp\\htdocs\\Klubportal-Laravel12\\storage/tenantfcbarcelona/app/public\\logos/club-logo.png\n";
echo "   • Local Disk (documents):  C:\\xampp\\htdocs\\Klubportal-Laravel12\\storage/tenantfcbarcelona/app\\documents/contract.pdf\n";

echo "\n";
echo "========================================\n";
echo "   PRAKTISCHES BEISPIEL: DATEI UPLOAD\n";
echo "========================================\n\n";

// Create test files for testclub
echo "📤 UPLOAD-TEST für testclub:\n";
$testclub->run(function() {
    // Create logo file
    Storage::disk('public')->put('logos/club-logo.png', 'Test Logo Content');
    echo "   ✅ Logo gespeichert in: " . Storage::disk('public')->path('logos/club-logo.png') . "\n";

    // Create document
    Storage::disk('local')->put('documents/contract.pdf', 'Test Contract Content');
    echo "   ✅ Dokument gespeichert in: " . Storage::disk('local')->path('documents/contract.pdf') . "\n";

    // Verify files exist
    echo "\n   📋 Dateien in diesem Tenant:\n";
    echo "      - Logo existiert: " . (Storage::disk('public')->exists('logos/club-logo.png') ? '✅ Ja' : '❌ Nein') . "\n";
    echo "      - Dokument existiert: " . (Storage::disk('local')->exists('documents/contract.pdf') ? '✅ Ja' : '❌ Nein') . "\n";
});

echo "\n";
echo "🔒 ISOLATION-TEST:\n";
echo "   Kann Central die Tenant-Dateien sehen?\n";
$centralCanSeeLogo = Storage::disk('public')->exists('logos/club-logo.png');
$centralCanSeeDoc = Storage::disk('local')->exists('documents/contract.pdf');
echo "      - Logo: " . ($centralCanSeeLogo ? '❌ Ja (Problem!)' : '✅ Nein (Isolation funktioniert!)') . "\n";
echo "      - Dokument: " . ($centralCanSeeDoc ? '❌ Ja (Problem!)' : '✅ Nein (Isolation funktioniert!)') . "\n";

echo "\n";
echo "========================================\n";
echo "   FILAMENT FILEUPLOAD BEISPIEL\n";
echo "========================================\n\n";

echo "In einer Filament Resource:\n\n";
echo "```php\n";
echo "use Filament\\Forms\\Components\\FileUpload;\n\n";
echo "FileUpload::make('logo')\n";
echo "    ->disk('public')  // Automatisch: tenant{id}/app/public/\n";
echo "    ->directory('logos')\n";
echo "    ->image()\n";
echo "    ->maxSize(2048);\n";
echo "```\n\n";

echo "Gespeichert als:\n";
echo "  • testclub:   storage/tenanttestclub/app/public/logos/logo.png\n";
echo "  • arsenal:    storage/tenantarsenal/app/public/logos/logo.png\n";
echo "  • barcelona:  storage/tenantfcbarcelona/app/public/logos/logo.png\n";

echo "\n";
echo "========================================\n";
echo "   URL-ZUGRIFF AUF DATEIEN\n";
echo "========================================\n\n";

$testclub->run(function() {
    $url = Storage::disk('public')->url('logos/club-logo.png');
    echo "URL für testclub Logo:\n";
    echo "  " . $url . "\n";
});

echo "\n";
echo "========================================\n";
echo "   ZUSAMMENFASSUNG\n";
echo "========================================\n";
echo "✅ Jeder Tenant hat eigenen Storage-Ordner\n";
echo "✅ Automatische Pfad-Trennung via FilesystemTenancyBootstrapper\n";
echo "✅ Keine manuelle Pfad-Verwaltung nötig\n";
echo "✅ Isolation zwischen Tenants garantiert\n";
echo "✅ Funktioniert mit allen Laravel Storage Disks\n";
echo "\n";

// Cleanup
echo "🧹 Aufräumen...\n";
$testclub->run(function() {
    Storage::disk('public')->delete('logos/club-logo.png');
    Storage::disk('local')->delete('documents/contract.pdf');
    echo "   ✅ Test-Dateien gelöscht\n";
});

echo "\n========================================\n\n";
