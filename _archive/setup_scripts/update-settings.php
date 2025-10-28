<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Settings aktualisieren ===\n\n";

// Phone auf null setzen
DB::table('settings')
    ->where('group', 'general')
    ->where('name', 'phone')
    ->update(['payload' => 'null']);

echo "✓ Phone auf null gesetzt\n";

// Sekundärfarbe hinzufügen
$exists = DB::table('settings')
    ->where('group', 'general')
    ->where('name', 'secondary_color')
    ->exists();

if (!$exists) {
    DB::table('settings')->insert([
        'group' => 'general',
        'name' => 'secondary_color',
        'locked' => 0,
        'payload' => '"#FF6B35"',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    echo "✓ Sekundärfarbe hinzugefügt\n";
} else {
    echo "⚠ Sekundärfarbe existiert bereits\n";
}

echo "\n=== Fertig ===\n";
