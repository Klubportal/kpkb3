<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Set tenant context
$tenant = \App\Models\Tenant::where('id', 'nkprigorjem')->first();
tenancy()->initialize($tenant);

echo "【 Checking Match-Competition Mapping 】\n";
echo "════════════════════════════════════════════════════════════════\n\n";

// Get a sample match
$match = DB::table('comet_matches')->first();
echo "Sample Match:\n";
echo "  ID: {$match->id}\n";
echo "  competition_fifa_id: {$match->competition_fifa_id}\n\n";

// Check if competition exists with this ID
$compByFifaId = DB::table('comet_competitions')
    ->where('comet_id', $match->competition_fifa_id)
    ->first();

if ($compByFifaId) {
    echo "✅ Competition found by comet_id = {$match->competition_fifa_id}\n";
    echo "   Name: {$compByFifaId->name}\n";
    echo "   Age Category: {$compByFifaId->age_category}\n\n";
} else {
    echo "❌ Competition NOT found by comet_id = {$match->competition_fifa_id}\n\n";
}

// Check total unmapped matches
$unmapped = DB::table('comet_matches as m')
    ->leftJoin('comet_competitions as c', 'm.competition_fifa_id', '=', 'c.comet_id')
    ->whereNull('c.id')
    ->count();

echo "Unmapped matches: {$unmapped} / " . DB::table('comet_matches')->count() . "\n";

// Check if we need competition_id field instead
$needsCompId = DB::table('comet_matches')->whereNull('competition_id')->count();
echo "Matches without competition_id: {$needsCompId}\n";
