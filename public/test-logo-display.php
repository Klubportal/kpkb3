<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Central\Tenant;
use App\Models\Tenant\TemplateSetting;
use Illuminate\Support\Facades\Storage;

$tenant = Tenant::find('nknapijed');
tenancy()->initialize($tenant);

$settings = TemplateSetting::first();
$logoPath = $settings->logo;

?>
<!DOCTYPE html>
<html>
<head>
    <title>Logo Display Test</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .test-box {
            border: 2px solid #ccc;
            padding: 15px;
            margin: 10px 0;
            background: #f5f5f5;
        }
        .success { border-color: green; }
        .error { border-color: red; }
        img { max-width: 200px; border: 1px solid #ddd; }
        .info { color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <h1>🧪 Logo Display Test - <?= $tenant->name ?></h1>

    <div class="test-box">
        <h2>📊 Datenbank Info</h2>
        <p><strong>Logo Pfad in DB:</strong> <?= $logoPath ?></p>
        <p><strong>Datei existiert:</strong> <?= Storage::disk('public')->exists($logoPath) ? '✅ JA' : '❌ NEIN' ?></p>
        <?php if (Storage::disk('public')->exists($logoPath)): ?>
        <p><strong>Dateigröße:</strong> <?= number_format(Storage::disk('public')->size($logoPath) / 1024, 2) ?> KB</p>
        <?php endif; ?>
    </div>

    <div class="test-box">
        <h2>Test 1: Relative URL (KORREKT für Tenants)</h2>
        <p class="info">URL: /storage/<?= $logoPath ?></p>
        <img src="/storage/<?= $logoPath ?>" alt="Logo Test 1" onerror="this.parentElement.classList.add('error'); this.alt='FEHLER: Bild nicht geladen';" onload="this.parentElement.classList.add('success');">
    </div>

    <div class="test-box">
        <h2>Test 2: asset() Helper (FALSCH - generiert zentrale URL)</h2>
        <p class="info">URL: <?= asset('storage/' . $logoPath) ?></p>
        <img src="<?= asset('storage/' . $logoPath) ?>" alt="Logo Test 2" onerror="this.parentElement.classList.add('error'); this.alt='FEHLER: Bild nicht geladen';" onload="this.parentElement.classList.add('success');">
    </div>

    <div class="test-box">
        <h2>Test 3: url() Helper</h2>
        <p class="info">URL: <?= url('storage/' . $logoPath) ?></p>
        <img src="<?= url('storage/' . $logoPath) ?>" alt="Logo Test 3" onerror="this.parentElement.classList.add('error'); this.alt='FEHLER: Bild nicht geladen';" onload="this.parentElement.classList.add('success');">
    </div>

    <div class="test-box">
        <h2>Test 4: Volle Tenant URL</h2>
        <p class="info">URL: http://nknapijed.localhost:8000/storage/<?= $logoPath ?></p>
        <img src="http://nknapijed.localhost:8000/storage/<?= $logoPath ?>" alt="Logo Test 4" onerror="this.parentElement.classList.add('error'); this.alt='FEHLER: Bild nicht geladen';" onload="this.parentElement.classList.add('success');">
    </div>

    <div class="test-box">
        <h2>📋 Zusammenfassung</h2>
        <ul>
            <li><strong>Test 1 (Relative URL)</strong> sollte ✅ funktionieren</li>
            <li><strong>Test 2 (asset())</strong> wird ❌ fehlschlagen (zeigt auf localhost:8000)</li>
            <li><strong>Test 3 (url())</strong> könnte funktionieren</li>
            <li><strong>Test 4 (Volle URL)</strong> sollte ✅ funktionieren</li>
        </ul>
    </div>

    <div class="test-box">
        <h2>💡 Empfehlung für Filament</h2>
        <p>Verwenden Sie in Templates und Filament:</p>
        <pre>/storage/{{ $logoPath }}</pre>
        <p>Das generiert automatisch die korrekte Tenant-URL.</p>
    </div>

    <script>
        // Log welche Bilder geladen wurden
        document.querySelectorAll('img').forEach(function(img, index) {
            img.addEventListener('load', function() {
                console.log('✅ Test ' + (index + 1) + ' erfolgreich geladen');
            });
            img.addEventListener('error', function() {
                console.error('❌ Test ' + (index + 1) + ' fehlgeschlagen: ' + this.src);
            });
        });
    </script>
</body>
</html>
<?php
tenancy()->end();
?>
