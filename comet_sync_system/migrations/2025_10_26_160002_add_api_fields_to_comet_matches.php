<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection('central')->table('comet_matches', function (Blueprint $table) {
            // Competition FIFA ID (für direkte API Queries)
            $table->bigInteger('competition_fifa_id')->nullable()->after('competition_id')->comment('Competition FIFA ID from API');

            // Match Type
            $table->string('match_type', 20)->nullable()->after('status')->comment('OFFICIAL, FRIENDLY');

            // Match Nature
            $table->string('nature', 50)->nullable()->after('match_type')->comment('HOME_AND_AWAY, NEUTRAL, etc.');

            // Facility FIFA ID
            $table->bigInteger('facility_fifa_id')->nullable()->after('stadium')->comment('Stadium/Facility FIFA ID');

            // Match Day (Round number from API)
            $table->integer('match_day')->nullable()->after('round')->comment('Match day/round number from API');

            // Add indexes
            $table->index('competition_fifa_id');
            $table->index('facility_fifa_id');
            $table->index(['competition_fifa_id', 'match_day']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('central')->table('comet_matches', function (Blueprint $table) {
            $table->dropIndex(['comet_matches_competition_fifa_id_index']);
            $table->dropIndex(['comet_matches_facility_fifa_id_index']);
            $table->dropIndex(['comet_matches_competition_fifa_id_match_day_index']);

            $table->dropColumn([
                'competition_fifa_id',
                'match_type',
                'nature',
                'facility_fifa_id',
                'match_day',
            ]);
        });
    }
};
