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
        Schema::connection('central')->create('comet_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_id')->constrained('comet_competitions')->cascadeOnDelete();
            $table->bigInteger('comet_id')->unique()->comment('Comet API Match ID');
            $table->bigInteger('home_club_fifa_id')->comment('FK to comet_clubs_extended');
            $table->bigInteger('away_club_fifa_id')->comment('FK to comet_clubs_extended');
            $table->dateTime('kickoff_time');
            $table->enum('status', ['scheduled', 'live', 'finished', 'postponed', 'cancelled'])->default('scheduled');
            $table->integer('home_goals')->nullable();
            $table->integer('away_goals')->nullable();
            $table->integer('home_goals_ht')->nullable()->comment('Half-time score');
            $table->integer('away_goals_ht')->nullable()->comment('Half-time score');
            $table->string('stadium')->nullable();
            $table->integer('attendance')->nullable();
            $table->string('referee')->nullable();
            $table->string('round')->nullable()->comment('e.g., Matchday 1, Quarter Final');
            $table->integer('week')->nullable();
            $table->integer('minute')->nullable()->comment('Current match minute (for live matches)');
            $table->json('extra_time')->nullable()->comment('Extra time/penalty info');
            $table->timestamps();

            // Indexes
            $table->index('status');
            $table->index('kickoff_time');
            $table->index(['competition_id', 'kickoff_time']);
            $table->index(['status', 'kickoff_time']);
            $table->index('home_club_fifa_id');
            $table->index('away_club_fifa_id');

            // Foreign keys
            $table->foreign('home_club_fifa_id')->references('club_fifa_id')->on('comet_clubs_extended')->cascadeOnDelete();
            $table->foreign('away_club_fifa_id')->references('club_fifa_id')->on('comet_clubs_extended')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('central')->dropIfExists('comet_matches');
    }
};
