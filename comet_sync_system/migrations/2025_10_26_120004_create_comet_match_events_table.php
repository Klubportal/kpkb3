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
        Schema::connection('central')->create('comet_match_events', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('match_event_fifa_id')->unique()->comment('Unique event ID from API');
            $table->bigInteger('match_fifa_id')->comment('FK to comet_matches');
            $table->bigInteger('competition_fifa_id')->comment('Competition ID');
            $table->bigInteger('player_fifa_id')->nullable()->comment('Primary player involved');
            $table->string('player_name')->nullable();
            $table->integer('shirt_number')->nullable();
            $table->bigInteger('player_fifa_id_2')->nullable()->comment('Secondary player (e.g., for assist/substitution)');
            $table->string('player_name_2')->nullable();
            $table->bigInteger('team_fifa_id')->comment('Team ID');
            $table->enum('match_team', ['HOME', 'AWAY']);
            $table->enum('event_type', [
                'goal',
                'penalty_goal',
                'own_goal',
                'yellow_card',
                'red_card',
                'yellow_red_card',
                'substitution',
                'penalty_missed'
            ]);
            $table->integer('event_minute')->comment('Minute when event occurred');
            $table->text('description')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('match_fifa_id');
            $table->index('competition_fifa_id');
            $table->index('player_fifa_id');
            $table->index('team_fifa_id');
            $table->index('event_type');
            $table->index(['match_fifa_id', 'event_minute']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('central')->dropIfExists('comet_match_events');
    }
};
