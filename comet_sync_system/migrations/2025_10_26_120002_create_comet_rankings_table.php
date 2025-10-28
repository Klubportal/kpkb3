<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection('central')->create('comet_rankings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_id')->constrained('comet_competitions')->cascadeOnDelete();
            $table->bigInteger('comet_id')->unique()->comment('Comet API Ranking ID');
            $table->string('name');
            $table->integer('position');
            $table->bigInteger('club_fifa_id')->comment('FK to comet_clubs_extended');
            $table->integer('matches_played')->default(0);
            $table->integer('wins')->default(0);
            $table->integer('draws')->default(0);
            $table->integer('losses')->default(0);
            $table->integer('goals_for')->default(0);
            $table->integer('goals_against')->default(0);
            $table->integer('points')->default(0);
            $table->json('form')->nullable()->comment('Recent form (W/D/L)');
            $table->timestamps();

            // Unique constraint
            $table->unique(['competition_id', 'club_fifa_id']);

            // Indexes
            $table->index('position');
            $table->index('points');
            $table->index(['competition_id', 'position']);
            $table->index('club_fifa_id');

            // Foreign key
            $table->foreign('club_fifa_id')->references('club_fifa_id')->on('comet_clubs_extended')->cascadeOnDelete();
        });

        // Add virtual column for goal difference
        DB::connection('central')->statement('ALTER TABLE comet_rankings ADD goal_difference INT GENERATED ALWAYS AS (goals_for - goals_against) STORED');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('central')->dropIfExists('comet_rankings');
    }
};
