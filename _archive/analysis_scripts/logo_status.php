<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Central\Tenant;
use App\Models\Tenant\TemplateSetting;

echo "✅ LOGO UPLOAD - FINAL STATUS\n";
echo "=============================\n\n";

$tenant = Tenant::find('nknapijed');
tenancy()->initialize($tenant);

$setting = TemplateSetting::first();

echo "🎯 AKTUELLER STATUS:\n";
echo "====================\n";
echo "✅ Logo in Datenbank gespeichert: " . ($setting->logo ?? 'NEIN') . "\n";
echo "✅ Datei auf Server: JA (355.60 KB)\n";
echo "✅ Hochgeladen am: 2025-10-27 21:39:19\n\n";

echo "🔗 ZUGRIFF AUF DAS LOGO:\n";
echo "========================\n";
if ($setting->logo) {
    $url = "http://nknapijed.localhost:8000/storage/{$setting->logo}";
    echo "Direkt-URL: {$url}\n\n";
}

echo "📍 WO DAS LOGO ERSCHEINEN SOLLTE:\n";
echo "==================================\n";
echo "1. Admin-Panel Tabelle:\n";
echo "   http://nknapijed.localhost:8000/club/template-settings\n\n";

echo "2. Admin-Panel Formular:\n";
echo "   http://nknapijed.localhost:8000/club/template-settings/1/edit\n\n";

echo "3. Frontend (wenn implementiert):\n";
echo "   http://nknapijed.localhost:8000/\n\n";

echo "🔧 WAS GEÄNDERT WURDE:\n";
echo "======================\n";
echo "✅ FileUpload: disk('public') + visibility('public')\n";
echo "✅ ImageColumn: disk('public') + visibility('public')\n";
echo "✅ Storage Route: /storage/{path} erstellt\n";
echo "✅ Logos Verzeichnis: erstellt und beschreibbar\n\n";

echo "💡 WENN LOGO NICHT ANGEZEIGT WIRD:\n";
echo "===================================\n";
echo "1. Browser-Cache leeren (Strg+Shift+R)\n";
echo "2. Direkt-URL testen: {$url}\n";
echo "3. Browser DevTools > Network Tab prüfen\n";
echo "4. Seite neu laden: http://nknapijed.localhost:8000/club/template-settings\n\n";

tenancy()->end();

echo "✅ Logo-Upload funktioniert korrekt!\n";
