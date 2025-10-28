<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Match Players (Aufstellungen, Spieler in Matches)
     */
    public function up(): void
    {
        Schema::connection('central')->create('comet_match_players', function (Blueprint $table) {
            $table->id();

            // FIFA IDs
            $table->bigInteger('match_fifa_id')->comment('FK to comet_matches');
            $table->bigInteger('team_fifa_id')->comment('FK to comet_clubs_extended');
            $table->bigInteger('person_fifa_id')->comment('FK to comet_players');

            // Player Info
            $table->string('person_name')->nullable();
            $table->string('local_person_name')->nullable();

            // Match Details
            $table->integer('shirt_number')->nullable();
            $table->string('position')->nullable()->comment('Player position for this match');
            $table->enum('team_nature', ['HOME', 'AWAY'])->comment('Home or Away team');

            // Status Flags
            $table->boolean('captain')->default(false)->comment('Is team captain');
            $table->boolean('goalkeeper')->default(false)->comment('Is goalkeeper');
            $table->boolean('starting_lineup')->default(false)->comment('In starting 11');
            $table->boolean('played')->default(false)->comment('Entered the match');

            // Match Statistics
            $table->integer('goals')->default(0)->comment('Goals scored in this match');
            $table->integer('assists')->default(0)->comment('Assists in this match');
            $table->integer('yellow_cards')->default(0)->comment('Yellow cards received');
            $table->integer('red_cards')->default(0)->comment('Red cards received');
            $table->integer('minutes_played')->nullable()->comment('Minutes played');

            // Substitution Info
            $table->integer('substituted_in_minute')->nullable()->comment('When substituted in');
            $table->integer('substituted_out_minute')->nullable()->comment('When substituted out');
            $table->bigInteger('substituted_by_player_fifa_id')->nullable()->comment('Who replaced this player');

            // Metadata
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('match_fifa_id');
            $table->index('team_fifa_id');
            $table->index('person_fifa_id');
            $table->index(['match_fifa_id', 'team_fifa_id']);
            $table->index(['match_fifa_id', 'starting_lineup']);
            $table->unique(['match_fifa_id', 'person_fifa_id'], 'match_player_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('central')->dropIfExists('comet_match_players');
    }
};
