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
        Schema::create('sports_events', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('venue');
            $table->string('city');
            $table->string('country');
            $table->date('event_date');
            $table->time('event_time');
            $table->string('category'); // football, basketball, baseball, etc.
            $table->string('league')->nullable(); // NFL, NBA, MLB, etc.
            $table->string('home_team')->nullable();
            $table->string('away_team')->nullable();
            $table->enum('status', ['scheduled', 'live', 'completed', 'cancelled'])->default('scheduled');
            $table->boolean('is_monitored')->default(false);
            $table->json('ticket_platforms')->nullable(); // Available platforms
            $table->decimal('min_price', 10, 2)->nullable();
            $table->decimal('max_price', 10, 2)->nullable();
            $table->integer('total_tickets')->nullable();
            $table->integer('available_tickets')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sports_events');
    }
};
