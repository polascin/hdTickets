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
        Schema::create('ticket_price_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('scraped_tickets')->onDelete('cascade');
            $table->decimal('price', 10, 2);
            $table->integer('quantity')->default(0);
            $table->timestamp('recorded_at');
            $table->string('source', 50)->default('scraper'); // scraper, manual, api, etc.
            $table->json('metadata')->nullable(); // Additional data like price_change, quantity_change
            $table->timestamps();

            // Indexes for performance
            $table->index(['ticket_id', 'recorded_at']);
            $table->index(['ticket_id', 'source']);
            $table->index('recorded_at');
            $table->index(['price', 'recorded_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_price_histories');
    }
};
