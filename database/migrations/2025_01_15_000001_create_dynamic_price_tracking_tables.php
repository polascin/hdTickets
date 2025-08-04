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
        // Enhanced ticket price history table (if not exists)
        if (!Schema::hasTable('ticket_price_histories')) {
            Schema::create('ticket_price_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ticket_id')->constrained('scraped_tickets')->onDelete('cascade');
                $table->decimal('price', 10, 2);
                $table->integer('quantity')->default(0);
                $table->timestamp('recorded_at');
                $table->string('source')->default('scraper');
                $table->json('metadata')->nullable();
                $table->timestamps();
                
                $table->index(['ticket_id', 'recorded_at']);
                $table->index(['recorded_at']);
                $table->index(['price']);
            });
        }

        // Price alert thresholds table
        Schema::create('price_alert_thresholds', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('ticket_id')->constrained('scraped_tickets')->onDelete('cascade');
            $table->decimal('target_price', 10, 2);
            $table->enum('alert_type', ['below', 'above', 'percentage_change']);
            $table->decimal('percentage_threshold', 5, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_triggered_at')->nullable();
            $table->integer('trigger_count')->default(0);
            $table->json('notification_channels');
            $table->timestamps();
            
            $table->index(['user_id', 'is_active']);
            $table->index(['ticket_id', 'is_active']);
            $table->index(['target_price']);
        });

        // Price volatility analytics table
        Schema::create('price_volatility_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('scraped_tickets')->onDelete('cascade');
            $table->date('analysis_date');
            $table->decimal('avg_price', 10, 2);
            $table->decimal('min_price', 10, 2);
            $table->decimal('max_price', 10, 2);
            $table->decimal('volatility_score', 5, 4); // Standard deviation / mean
            $table->integer('price_changes_count');
            $table->decimal('max_single_change', 5, 2); // Max percentage change in one update
            $table->enum('trend_direction', ['increasing', 'decreasing', 'stable']);
            $table->json('hourly_data')->nullable(); // Store hourly aggregated data
            $table->timestamps();
            
            $table->unique(['ticket_id', 'analysis_date']);
            $table->index(['analysis_date']);
            $table->index(['volatility_score']);
            $table->index(['trend_direction']);
        });

        // Real-time price monitoring queue table
        Schema::create('price_monitoring_queue', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('scraped_tickets')->onDelete('cascade');
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->timestamp('next_check_at');
            $table->integer('check_interval_minutes')->default(15);
            $table->integer('consecutive_failures')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('monitoring_settings')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamps();
            
            $table->index(['next_check_at', 'is_active']);
            $table->index(['priority']);
            $table->index(['ticket_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_monitoring_queue');
        Schema::dropIfExists('price_volatility_analytics');
        Schema::dropIfExists('price_alert_thresholds');
        Schema::dropIfExists('ticket_price_histories');
    }
};
