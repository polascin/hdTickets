<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // Platform-specific composite indexes for optimal query performance
            $table->index(['platform', 'created_at'], 'idx_tickets_platform_created');
            $table->index(['platform', 'event_date'], 'idx_tickets_platform_event_date');
            $table->index(['platform', 'location'], 'idx_tickets_platform_location');
            $table->index(['platform', 'status'], 'idx_tickets_platform_status');
            
            // External ID index for duplicate prevention
            $table->index(['external_id', 'platform'], 'idx_tickets_external_platform');
            
            // Search optimization indexes
            $table->index(['title', 'platform'], 'idx_tickets_title_platform');
            $table->index(['price', 'platform'], 'idx_tickets_price_platform');
            
            // Full-text search index for description
            if (DB::getDriverName() === 'mysql') {
                DB::statement('ALTER TABLE tickets ADD FULLTEXT(title, description)');
            }
        });

        Schema::table('scraping_stats', function (Blueprint $table) {
            // Performance tracking indexes
            $table->index(['platform', 'created_at'], 'idx_stats_platform_created');
            $table->index(['platform', 'status'], 'idx_stats_platform_status');
            $table->index(['platform', 'response_time_ms'], 'idx_stats_platform_response_time');
            $table->index(['created_at', 'platform'], 'idx_stats_created_platform');
        });

        // Create platform-specific tables for caching if they don't exist
        if (!Schema::hasTable('platform_cache')) {
            Schema::create('platform_cache', function (Blueprint $table) {
                $table->id();
                $table->string('platform', 50);
                $table->string('cache_key', 255);
                $table->text('cache_data');
                $table->timestamp('expires_at');
                $table->timestamps();
                
                $table->index(['platform', 'cache_key'], 'idx_cache_platform_key');
                $table->index(['expires_at'], 'idx_cache_expires');
            });
        }

        if (!Schema::hasTable('selector_effectiveness')) {
            Schema::create('selector_effectiveness', function (Blueprint $table) {
                $table->id();
                $table->string('platform', 50);
                $table->string('selector', 255);
                $table->string('page_type', 100); // search, detail, listing
                $table->integer('success_count')->default(0);
                $table->integer('failure_count')->default(0);
                $table->decimal('success_rate', 5, 2)->default(0);
                $table->timestamp('last_used_at')->nullable();
                $table->timestamp('last_successful_at')->nullable();
                $table->timestamps();
                
                $table->index(['platform', 'page_type'], 'idx_selector_platform_type');
                $table->index(['success_rate'], 'idx_selector_success_rate');
                $table->unique(['platform', 'selector', 'page_type'], 'uk_selector_platform_type');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex('idx_tickets_platform_created');
            $table->dropIndex('idx_tickets_platform_event_date');
            $table->dropIndex('idx_tickets_platform_location');
            $table->dropIndex('idx_tickets_platform_status');
            $table->dropIndex('idx_tickets_external_platform');
            $table->dropIndex('idx_tickets_title_platform');
            $table->dropIndex('idx_tickets_price_platform');
        });

        Schema::table('scraping_stats', function (Blueprint $table) {
            $table->dropIndex('idx_stats_platform_created');
            $table->dropIndex('idx_stats_platform_status');
            $table->dropIndex('idx_stats_platform_response_time');
            $table->dropIndex('idx_stats_created_platform');
        });

        Schema::dropIfExists('platform_cache');
        Schema::dropIfExists('selector_effectiveness');

        // Drop full-text index if MySQL
        if (DB::getDriverName() === 'mysql') {
            try {
                DB::statement('ALTER TABLE tickets DROP INDEX title');
            } catch (Exception $e) {
                // Index might not exist, ignore
            }
        }
    }
};
