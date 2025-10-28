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
        Schema::connection('central')->create('comet_top_scorers', function (Blueprint $table) {
            $table->id();

            // Foreign Keys
            $table->foreignId('competition_id')->constrained('comet_competitions')->cascadeOnDelete();
            $table->bigInteger('person_fifa_id')->comment('Player FIFA ID');
            $table->bigInteger('club_fifa_id')->nullable()->comment('Club/Organisation FIFA ID');
            $table->bigInteger('team_fifa_id')->nullable()->comment('Team FIFA ID');

            // Scorer Info
            $table->string('first_name');
            $table->string('last_name');
            $table->string('popular_name')->nullable();
            $table->string('club_name')->nullable();
            $table->string('team_name')->nullable();

            // Statistics
            $table->integer('goals')->default(0);
            $table->integer('penalties')->default(0)->comment('Goals from penalties');
            $table->integer('position')->nullable()->comment('Ranking position');

            // Metadata
            $table->json('api_data')->nullable()->comment('Full API response');

            $table->timestamps();

            // Unique constraint - one record per player per competition
            $table->unique(['competition_id', 'person_fifa_id'], 'competition_player_unique');

            // Indexes
            $table->index('person_fifa_id');
            $table->index('club_fifa_id');
            $table->index('team_fifa_id');
            $table->index('goals');
            $table->index(['competition_id', 'goals']);
            $table->index(['club_fifa_id', 'goals']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('central')->dropIfExists('comet_top_scorers');
    }
};
