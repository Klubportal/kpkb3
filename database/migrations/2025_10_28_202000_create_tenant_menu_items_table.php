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
        Schema::create('tenant_menu_items', function (Blueprint $table) {
            $table->id();
            $table->string('label'); // Menü-Text (z.B. "Spieler", "News")
            $table->string('icon')->nullable(); // Heroicon Name
            $table->string('url')->nullable(); // URL oder Route Name
            $table->string('route')->nullable(); // Route Name (alternativ zu URL)
            $table->json('route_parameters')->nullable(); // Parameter für Route
            $table->integer('sort_order')->default(0); // Sortierung
            $table->boolean('is_active')->default(true); // Sichtbarkeit
            $table->string('group')->nullable(); // Gruppierung (z.B. "Content", "Settings")
            $table->string('badge')->nullable(); // Badge-Text (z.B. "Neu", "5")
            $table->string('badge_color')->nullable(); // Badge-Farbe
            $table->json('permissions')->nullable(); // Erforderliche Permissions
            $table->json('roles')->nullable(); // Erforderliche Rollen
            $table->foreignId('parent_id')->nullable()->constrained('tenant_menu_items')->onDelete('cascade'); // Für Submenüs
            $table->timestamps();

            // Index für Performance
            $table->index(['sort_order', 'is_active']);
            $table->index('parent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_menu_items');
    }
};
