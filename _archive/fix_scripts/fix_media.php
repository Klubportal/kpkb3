<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Lösche fehlerhafte Media-Referenz...\n";

DB::connection('central')->table('media')->where('id', 7)->delete();

echo "✓ Fehlerhafte Media-Referenz gelöscht\n\n";
echo "NÄCHSTER SCHRITT:\n";
echo "1. Gehe zu: http://localhost:8000/admin/news/8/edit\n";
echo "2. Lade ein Featured Image hoch\n";
echo "3. Speichere die News\n";
echo "4. Refresh die Landing Page\n";
