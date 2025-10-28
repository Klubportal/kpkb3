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
        Schema::connection('central')->create('comet_clubs_extended', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('club_fifa_id')->unique()->comment('FIFA Club ID');
            $table->bigInteger('comet_id')->unique()->nullable()->comment('Comet API Club ID');
            $table->bigInteger('fifa_id')->unique()->comment('FIFA Official ID');
            $table->string('name');
            $table->string('code', 10)->nullable()->comment('Short club code (e.g., FCB)');
            $table->integer('founded_year')->nullable();
            $table->string('stadium_name')->nullable();
            $table->integer('stadium_capacity')->nullable();
            $table->string('coach_name')->nullable();
            $table->json('coach_info')->nullable();
            $table->string('country', 3)->nullable();
            $table->string('league_name')->nullable();
            $table->text('club_info')->nullable();
            $table->boolean('is_synced')->default(false);
            $table->timestamp('last_synced_at')->nullable();
            $table->json('sync_metadata')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('club_fifa_id');
            $table->index('country');
            $table->index('is_synced');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('central')->dropIfExists('comet_clubs_extended');
    }
};
