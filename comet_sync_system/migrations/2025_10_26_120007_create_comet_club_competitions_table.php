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
        Schema::connection('central')->create('comet_club_competitions', function (Blueprint $table) {
            $table->id();
            $table->integer('competitionFifaId')->nullable()->index();
            $table->string('ageCategory', 100);
            $table->string('ageCategoryName', 100);
            $table->string('internationalName', 255);
            $table->smallInteger('season');
            $table->string('status', 50);
            $table->integer('flag_played_matches')->nullable();
            $table->integer('flag_scheduled_matches')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('central')->dropIfExists('comet_club_competitions');
    }
};
