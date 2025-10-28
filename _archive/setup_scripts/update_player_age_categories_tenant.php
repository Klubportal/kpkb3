<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tenant = \App\Models\Tenant::where('id', 'nkprigorjem')->first();
$tenant->run(function () {
    echo "【 Updating Player Age Categories 】\n";
    echo "════════════════════════════════════════════════════════════════\n\n";

    $players = DB::table('comet_players')->whereNotNull('person_fifa_id')->get();
    $updated = 0;

    foreach ($players as $player) {
        // Get most common age_category from matches
        $ageCategory = DB::table('comet_match_players as mp')
            ->join('comet_matches as m', 'mp.match_fifa_id', '=', 'm.match_fifa_id')
            ->where('mp.person_fifa_id', $player->person_fifa_id)
            ->select('m.age_category', DB::raw('COUNT(*) as count'))
            ->groupBy('m.age_category')
            ->orderByDesc('count')
            ->first();

        // Calculate birth year from date_of_birth
        $birthYear = null;
        if ($player->date_of_birth) {
            $birthYear = date('Y', strtotime($player->date_of_birth));
        }

        if ($ageCategory || $birthYear) {
            DB::table('comet_players')
                ->where('id', $player->id)
                ->update([
                    'primary_age_category' => $ageCategory?->age_category,
                    'birth_year' => $birthYear,
                    'updated_at' => now(),
                ]);

            $updated++;

            if ($updated % 50 == 0) {
                echo "Updated {$updated} players...\n";
            }
        }
    }

    echo "\n✅ Updated {$updated} players\n";

    // Show distribution
    echo "\n【 Age Category Distribution 】\n";
    $distribution = DB::table('comet_players')
        ->select('primary_age_category', DB::raw('COUNT(*) as count'))
        ->whereNotNull('primary_age_category')
        ->groupBy('primary_age_category')
        ->orderByDesc('count')
        ->get();

    foreach ($distribution as $d) {
        echo sprintf("%-20s: %3d players\n", $d->primary_age_category ?? 'NULL', $d->count);
    }
});
