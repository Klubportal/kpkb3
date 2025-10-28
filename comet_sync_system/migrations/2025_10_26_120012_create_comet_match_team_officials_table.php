<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Match Team Officials (Trainer/Betreuer bei Matches)
     */
    public function up(): void
    {
        Schema::connection('central')->create('comet_match_team_officials', function (Blueprint $table) {
            $table->id();

            // FIFA IDs
            $table->bigInteger('match_fifa_id')->comment('FK to comet_matches');
            $table->bigInteger('team_fifa_id')->comment('FK to comet_clubs_extended');
            $table->bigInteger('person_fifa_id')->comment('Official person FIFA ID');

            // Person Info
            $table->string('person_name')->nullable();
            $table->string('local_person_name')->nullable();

            // Team Context
            $table->enum('team_nature', ['HOME', 'AWAY'])->comment('Home or Away team');

            // Official Role
            $table->enum('role', [
                'COACH',
                'ASSISTANT_COACH',
                'GOALKEEPER_COACH',
                'PHYSICAL_TRAINER',
                'TEAM_DOCTOR',
                'PHYSIOTHERAPIST',
                'DIRECTOR',
                'TECHNICAL_DIRECTOR',
                'MANAGER',
                'OTHER'
            ])->comment('Official role in team');

            $table->string('role_description')->nullable();
            $table->string('comet_role_name')->nullable()->comment('COMET label key');
            $table->string('comet_role_name_key')->nullable()->comment('Translation key');

            // Match Events (Karten für Trainer/Betreuer)
            $table->integer('yellow_cards')->default(0);
            $table->integer('red_cards')->default(0);

            // Metadata
            $table->json('additional_info')->nullable()->comment('Extra official data');
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('match_fifa_id');
            $table->index('team_fifa_id');
            $table->index('person_fifa_id');
            $table->index(['match_fifa_id', 'team_fifa_id']);
            $table->unique(['match_fifa_id', 'team_fifa_id', 'person_fifa_id'], 'match_team_official_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('central')->dropIfExists('comet_match_team_officials');
    }
};
