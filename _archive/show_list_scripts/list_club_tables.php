<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Tables with 'user' or 'club' in name:\n\n";
$tables = DB::select('SHOW TABLES');
foreach ($tables as $t) {
    $name = array_values((array)$t)[0];
    if (stripos($name, 'user') !== false || stripos($name, 'club') !== false) {
        echo "  - $name\n";
    }
}

echo "\n\nLet me check models directory for relationship hints:\n";
// List model files
$modelPath = base_path('app/Models');
if (is_dir($modelPath)) {
    $files = scandir($modelPath);
    foreach ($files as $file) {
        if (endswith($file, '.php')) {
            echo "  - $file\n";
        }
    }
}

function endswith($string, $test) {
    $strlen = strlen($string);
    $testlen = strlen($test);
    if ($testlen > $strlen) return false;
    return substr_compare($string, $test, $strlen - $testlen, $testlen) === 0;
}
