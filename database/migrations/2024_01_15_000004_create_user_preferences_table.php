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
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('key', 100)->index(); // Preference key
            $table->json('value'); // Preference value (stored as JSON)
            $table->enum('type', ['string', 'integer', 'boolean', 'array', 'json'])->default('json');
            $table->string('category', 50)->default('general')->index(); // Group preferences by category
            $table->timestamps();

            // Unique constraint to prevent duplicate preferences
            $table->unique(['user_id', 'key']);
            
            // Indexes for performance
            $table->index(['user_id', 'category']);
            $table->index(['key', 'category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_preferences');
    }
};
