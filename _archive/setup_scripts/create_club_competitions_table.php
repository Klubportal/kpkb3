<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

echo "Creating comet_club_competitions table...\n";

Schema::connection('central')->dropIfExists('comet_club_competitions');

Schema::connection('central')->create('comet_club_competitions', function (Blueprint $table) {
    $table->id();
    $table->integer('competitionFifaId')->nullable()->index();
    $table->string('ageCategory', 100);
    $table->string('ageCategoryName', 100);
    $table->string('internationalName', 255);
    $table->smallInteger('season');
    $table->string('status', 50);
    $table->integer('flag_played_matches')->nullable();
    $table->integer('flag_scheduled_matches')->nullable();
    $table->timestamps();
});

echo "✅ Table created successfully!\n";

// Show structure
echo "\nTable structure:\n";
$columns = DB::connection('central')->select('SHOW COLUMNS FROM comet_club_competitions');
foreach ($columns as $column) {
    echo "  - {$column->Field} ({$column->Type})\n";
}
