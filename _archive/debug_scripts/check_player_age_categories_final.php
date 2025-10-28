<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Set tenant context
$tenant = \App\Models\Tenant::where('id', 'nkprigorjem')->first();
tenancy()->initialize($tenant);

echo "【 Player Age Category Distribution 】\n";
echo "════════════════════════════════════════════════════════════════\n\n";

$distribution = DB::table('comet_players')
    ->select('primary_age_category', DB::raw('COUNT(*) as count'))
    ->groupBy('primary_age_category')
    ->orderBy('count', 'DESC')
    ->get();

foreach ($distribution as $dist) {
    $category = $dist->primary_age_category ?? 'NULL';
    echo sprintf("%-20s: %3d players\n", $category, $dist->count);
}

echo "\n【 Sample Players with Age Categories 】\n";
echo "════════════════════════════════════════════════════════════════\n\n";

$samples = DB::table('comet_players')
    ->whereNotNull('primary_age_category')
    ->whereNotIn('primary_age_category', ['OTHER', 'SENIORS'])
    ->limit(15)
    ->get(['first_name', 'last_name', 'primary_age_category', 'birth_year']);

foreach ($samples as $player) {
    echo sprintf("%-25s | %-15s | %s\n",
        $player->first_name . ' ' . $player->last_name,
        $player->primary_age_category,
        $player->birth_year
    );
}

echo "\n✅ FERTIG!\n";
