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
        Schema::create('user_favorite_venues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('venue_id')->constrained()->cascadeOnDelete();
            $table->integer('priority')->default(1)->comment('User priority rating 1-5');
            $table->json('notification_settings')->nullable()->comment('Venue-specific notification preferences');
            $table->timestamps();
            
            // Prevent duplicate venue favorites per user
            $table->unique(['user_id', 'venue_id'], 'user_venue_unique');
            
            // Index for efficient queries
            $table->index(['user_id', 'priority'], 'user_priority_index');
            $table->index('venue_id', 'venue_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_favorite_venues');
    }
};
