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
        Schema::create('scraping_stats', function (Blueprint $table) {
            $table->id();
            $table->string('platform', 50)->index(); // Platform name (e.g., 'stubhub', 'viagogo')
            $table->enum('method', ['api', 'scraping'])->default('scraping');
            $table->enum('operation', ['search', 'event_details', 'venue_details'])->index();
            $table->string('url', 500)->nullable(); // URL that was scraped/called
            $table->text('search_criteria')->nullable(); // JSON of search criteria used
            $table->enum('status', ['success', 'failed', 'timeout', 'rate_limited', 'bot_detected'])->index();
            $table->integer('response_time_ms')->unsigned()->nullable(); // Response time in milliseconds
            $table->integer('results_count')->unsigned()->default(0); // Number of results returned
            $table->string('error_type', 100)->nullable(); // Exception class name if failed
            $table->text('error_message')->nullable(); // Error message if failed
            $table->text('selectors_used')->nullable(); // JSON array of CSS selectors used
            $table->json('selector_effectiveness')->nullable(); // JSON object tracking selector success rates
            $table->string('user_agent', 500)->nullable(); // User agent used for the request
            $table->ipAddress('ip_address')->nullable(); // IP address used (useful for proxy rotation)
            $table->timestamp('started_at')->nullable(); // When the operation started
            $table->timestamp('completed_at')->nullable(); // When the operation completed
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['platform', 'status', 'created_at']);
            $table->index(['created_at', 'platform']);
            $table->index(['status', 'method']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function reverse(): void
    {
        Schema::dropIfExists('scraping_stats');
    }
};
