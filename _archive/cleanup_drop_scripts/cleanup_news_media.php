<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Lösche alte Media-Referenzen...\n";

$deleted = DB::connection('central')
    ->table('media')
    ->where('model_id', 8)
    ->where('model_type', 'App\Models\Central\News')
    ->delete();

echo "✓ {$deleted} Media-Referenzen gelöscht\n\n";
echo "JETZT:\n";
echo "1. Cache leeren: php artisan optimize:clear\n";
echo "2. Gehe zu: http://localhost:8000/admin/news/8/edit\n";
echo "3. Lade ein Featured Image hoch\n";
echo "4. Speichere die News\n";
echo "5. Das Bild sollte jetzt auf 'public' Disk gespeichert werden\n";
