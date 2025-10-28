<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('comet_players', function (Blueprint $table) {
            $table->id();

            // Core identifiers
            $table->unsignedBigInteger('club_fifa_id')->nullable()->index();
            $table->unsignedBigInteger('person_fifa_id')->nullable()->unique();

            // Names
            $table->string('name')->nullable()->index();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('popular_name')->nullable();

            // Bio
            $table->date('date_of_birth')->nullable()->index();
            $table->string('place_of_birth')->nullable();
            $table->string('country_of_birth', 2)->nullable();
            $table->string('nationality')->nullable();
            $table->string('nationality_code', 3)->nullable();
            $table->string('gender', 10)->nullable();

            // Player info
            $table->string('position', 20)->nullable()->index();
            $table->unsignedSmallInteger('shirt_number')->nullable();
            $table->string('photo_url')->nullable();
            $table->unsignedSmallInteger('height_cm')->nullable();
            $table->unsignedSmallInteger('weight_kg')->nullable();
            $table->string('foot', 10)->nullable();
            $table->string('status', 20)->nullable()->default('active')->index();
            $table->string('injury_info')->nullable();
            $table->date('return_date')->nullable();

            // Career totals
            $table->unsignedInteger('total_matches')->nullable()->default(0);
            $table->unsignedInteger('total_goals')->nullable()->default(0);
            $table->unsignedInteger('total_assists')->nullable()->default(0);
            $table->unsignedInteger('total_yellow_cards')->nullable()->default(0);
            $table->unsignedInteger('total_red_cards')->nullable()->default(0);

            // Current season
            $table->unsignedInteger('season_matches')->nullable()->default(0);
            $table->unsignedInteger('season_goals')->nullable()->default(0);
            $table->unsignedInteger('season_assists')->nullable()->default(0);
            $table->unsignedInteger('season_yellow_cards')->nullable()->default(0);
            $table->unsignedInteger('season_red_cards')->nullable()->default(0);

            // Misc metrics
            $table->decimal('market_value_eur', 12, 2)->nullable();
            $table->decimal('average_rating', 5, 2)->nullable();

            // Sync/meta
            $table->boolean('is_synced')->default(false);
            $table->timestamp('last_synced_at')->nullable();
            $table->json('sync_metadata')->nullable();
            $table->json('local_names')->nullable();

            $table->timestamps();

            // Helpful composite indexes
            $table->index(['club_fifa_id', 'position']);
            $table->index(['club_fifa_id', 'season_goals']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comet_players');
    }
};
