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
        Schema::create('scraped_tickets', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('platform'); // stubhub, ticketmaster, viagogo, etc.
            $table->string('external_id')->nullable();
            $table->string('title');
            $table->string('venue')->nullable();
            $table->string('location')->nullable();
            $table->string('event_type')->default('sports');
            $table->string('sport')->default('football');
            $table->string('team')->nullable();
            $table->datetime('event_date')->nullable();
            $table->decimal('min_price', 8, 2)->nullable();
            $table->decimal('max_price', 8, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->integer('availability')->default(0);
            $table->boolean('is_available')->default(true);
            $table->boolean('is_high_demand')->default(false);
            $table->text('ticket_url')->nullable();
            $table->string('search_keyword')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('scraped_at');
            $table->timestamps();
            
            $table->index(['platform', 'external_id']);
            $table->index(['is_available', 'event_date']);
            $table->index(['is_high_demand', 'min_price']);
            $table->index(['title', 'venue']);
            $table->index('scraped_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scraped_tickets');
    }
};
