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
        Schema::connection('central')->create('comet_player_competition_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained('comet_players')->cascadeOnDelete();
            $table->foreignId('competition_id')->constrained('comet_competitions')->cascadeOnDelete();
            $table->integer('matches')->default(0);
            $table->integer('goals')->default(0);
            $table->integer('assists')->default(0);
            $table->integer('yellow_cards')->default(0);
            $table->integer('red_cards')->default(0);
            $table->decimal('average_rating', 3, 2)->nullable();
            $table->json('detailed_stats')->nullable()->comment('Additional stats like shots, passes, etc.');
            $table->timestamps();

            // Unique constraint
            $table->unique(['player_id', 'competition_id']);

            // Indexes
            $table->index('goals');
            $table->index(['competition_id', 'goals']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('central')->dropIfExists('comet_player_competition_stats');
    }
};
