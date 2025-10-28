<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Deactivating competitions for NK Prigorje (598)...\n";
echo "===================================================\n\n";

// Deactivate all competitions for club 598
$updated = DB::connection('mysql')->table('kpkb3.comet_club_competitions')
    ->where('club_fifa_id', 598)
    ->update(['status' => 'inactive']);

echo "✓ Deactivated {$updated} competitions for NK Prigorje (598)\n\n";

// Show active competitions for NK Naprijed (396)
$activeNaprijed = DB::connection('mysql')->table('kpkb3.comet_club_competitions')
    ->where('club_fifa_id', 396)
    ->where('status', 'active')
    ->get();

echo "Active competitions for NK Naprijed (396): " . $activeNaprijed->count() . "\n";

foreach ($activeNaprijed as $comp) {
    echo "  - {$comp->internationalName} (FIFA ID: {$comp->competitionFifaId})\n";
}

echo "\n✓ Ready to sync only NK Naprijed (396) data!\n";
