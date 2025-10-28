<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║   UPDATE TOP SCORERS - TEAM LOGOS                            ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

$logoDir = public_path('images/kp_team_logo_images');

if (!File::exists($logoDir)) {
    echo "❌ Logo directory not found: $logoDir\n";
    exit(1);
}

// Scanne alle Logo-Dateien
$logoFiles = File::files($logoDir);
echo "Found " . count($logoFiles) . " logo files in directory\n\n";

// Erstelle Mapping: club_id => logo_path
$logoMap = [];
foreach ($logoFiles as $file) {
    $filename = $file->getFilename();
    $extension = $file->getExtension();

    // Extrahiere club_id aus Dateinamen (z.B. "598.png" -> 598)
    if (preg_match('/^(\d+)\.(png|jpg|jpeg|gif)$/i', $filename, $matches)) {
        $clubId = (int)$matches[1];

        // Bevorzuge PNG, dann JPG, dann JPEG, dann GIF
        $priority = ['png' => 1, 'jpg' => 2, 'jpeg' => 3, 'gif' => 4];
        $ext = strtolower($extension);

        if (!isset($logoMap[$clubId]) || ($priority[$ext] ?? 999) < ($priority[$logoMap[$clubId]['ext']] ?? 999)) {
            $logoMap[$clubId] = [
                'path' => "images/kp_team_logo_images/$filename",
                'ext' => $ext
            ];
        }
    }
}

echo "Mapped " . count($logoMap) . " unique club logos\n";
echo str_repeat("=", 70) . "\n\n";

// Hole alle einzigartigen club_ids aus top_scorers
$clubIds = DB::connection('central')
    ->table('comet_top_scorers')
    ->select('club_id')
    ->where('club_id', '>', 0)
    ->distinct()
    ->pluck('club_id');

echo "Found " . $clubIds->count() . " unique clubs in top_scorers table\n\n";

$updated = 0;
$notFound = 0;

foreach ($clubIds as $clubId) {
    if (isset($logoMap[$clubId])) {
        $logoPath = $logoMap[$clubId]['path'];

        $affectedRows = DB::connection('central')
            ->table('comet_top_scorers')
            ->where('club_id', $clubId)
            ->update(['team_logo' => $logoPath]);

        if ($affectedRows > 0) {
            echo "✓ Club $clubId: $logoPath ($affectedRows scorers)\n";
            $updated++;
        }
    } else {
        $notFound++;
        if ($notFound <= 10) { // Zeige nur erste 10
            echo "⚠ Club $clubId: No logo found\n";
        }
    }
}

if ($notFound > 10) {
    echo "... and " . ($notFound - 10) . " more clubs without logos\n";
}

echo "\n" . str_repeat("=", 70) . "\n";
echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║                     UPDATE SUMMARY                            ║\n";
echo "╠═══════════════════════════════════════════════════════════════╣\n";
echo "║ Total Clubs:          " . str_pad($clubIds->count(), 38) . "║\n";
echo "║ Logos Found:          " . str_pad($updated, 38) . "║\n";
echo "║ Logos Not Found:      " . str_pad($notFound, 38) . "║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

// Statistik
$withLogo = DB::connection('central')->table('comet_top_scorers')->whereNotNull('team_logo')->count();
$total = DB::connection('central')->table('comet_top_scorers')->count();

echo "Top Scorers with team_logo: $withLogo / $total\n\n";

echo "✅ TEAM LOGOS UPDATE COMPLETED!\n";
