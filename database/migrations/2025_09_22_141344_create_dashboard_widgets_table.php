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
        Schema::create('dashboard_widgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('widget_type', 50); // 'stats', 'recommendations', 'recent_tickets', etc.
            $table->string('widget_name', 100);
            $table->json('configuration')->nullable(); // Widget-specific settings
            $table->integer('position')->default(0); // Display order
            $table->boolean('is_visible')->default(true);
            $table->boolean('is_expandable')->default(true);
            $table->string('size', 20)->default('medium'); // small, medium, large
            $table->timestamps();
            
            $table->index(['user_id', 'widget_type']);
            $table->index(['user_id', 'position']);
            $table->index(['user_id', 'is_visible']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dashboard_widgets');
    }
};
