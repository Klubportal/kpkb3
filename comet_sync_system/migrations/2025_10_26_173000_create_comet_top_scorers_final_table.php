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

            // Competition Info
            $table->bigInteger('competition_fifa_id')->index();
            $table->string('international_competition_name');
            $table->string('age_category', 50)->nullable();
            $table->string('age_category_name', 100)->nullable();

            // Player Info
            $table->bigInteger('player_fifa_id')->nullable()->index();
            $table->integer('goals')->default(0);
            $table->string('international_first_name');
            $table->string('international_last_name')->index();

            // Club Info
            $table->string('club')->nullable();
            $table->bigInteger('club_id')->default(0)->index();
            $table->string('team_logo')->nullable();

            $table->timestamps();

            // Unique constraint
            $table->unique(['competition_fifa_id', 'player_fifa_id'], 'unique_competition_player');

            // Indexes for common queries
            $table->index(['competition_fifa_id', 'goals']);
            $table->index(['club_id', 'goals']);
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
