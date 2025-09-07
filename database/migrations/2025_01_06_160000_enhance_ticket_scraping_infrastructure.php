<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations for enhanced ticket scraping infrastructure
     */
    public function up(): void
    {
        // Add new columns to scraped_tickets table for enhanced features
        Schema::table('scraped_tickets', function (Blueprint $table) {
            // Analytics and engagement columns
            $table->unsignedInteger('view_count')->default(0)->after('predicted_demand');
            $table->unsignedInteger('bookmark_count')->default(0)->after('view_count');
            $table->unsignedInteger('share_count')->default(0)->after('bookmark_count');
            $table->decimal('popularity_score', 5, 2)->default(0)->after('share_count');
            
            // Price tracking
            $table->decimal('previous_min_price', 8, 2)->nullable()->after('max_price');
            $table->decimal('previous_max_price', 8, 2)->nullable()->after('previous_min_price');
            $table->timestamp('last_price_change')->nullable()->after('previous_max_price');
            $table->decimal('price_change_percentage', 5, 2)->default(0)->after('last_price_change');
            
            // Enhanced availability tracking
            $table->timestamp('last_available_at')->nullable()->after('is_available');
            $table->unsignedInteger('availability_changes')->default(0)->after('last_available_at');
            
            // SEO and searchability
            $table->text('description')->nullable()->after('metadata');
            $table->json('tags')->nullable()->after('description');
            $table->string('slug')->nullable()->unique()->after('tags');
            
            // Geographic data
            $table->string('country', 2)->default('US')->after('location');
            $table->string('timezone', 50)->default('UTC')->after('country');
            $table->decimal('latitude', 10, 8)->nullable()->after('timezone');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            
            // Performance tracking
            $table->timestamp('last_scraped_successfully')->nullable()->after('scraped_at');
            $table->unsignedTinyInteger('scraping_quality_score')->default(100)->after('last_scraped_successfully');
            
            // Add indexes
            $table->index(['view_count', 'created_at'], 'idx_scraped_tickets_popular');
            $table->index(['bookmark_count', 'is_available'], 'idx_scraped_tickets_bookmarked');
            $table->index(['last_price_change', 'price_change_percentage'], 'idx_scraped_tickets_price_changes');
            $table->index(['country', 'sport', 'event_date'], 'idx_scraped_tickets_location_sport');
            $table->index(['popularity_score', 'is_high_demand'], 'idx_scraped_tickets_demand');
        });

        // Create ticket price history table
        Schema::create('ticket_price_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scraped_ticket_id')->constrained()->onDelete('cascade');
            $table->decimal('min_price', 8, 2)->nullable();
            $table->decimal('max_price', 8, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->boolean('is_available')->default(true);
            $table->unsignedInteger('available_quantity')->nullable();
            $table->json('price_breakdown')->nullable(); // Different price tiers
            $table->timestamp('recorded_at');
            $table->timestamps();
            
            // Indexes for price history queries
            $table->index(['scraped_ticket_id', 'recorded_at'], 'idx_price_history_ticket_time');
            $table->index(['recorded_at', 'min_price'], 'idx_price_history_time_price');
            $table->index(['is_available', 'recorded_at'], 'idx_price_history_availability');
        });

        // Create ticket bookmarks table for user favorites
        Schema::create('ticket_bookmarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('scraped_ticket_id')->constrained()->onDelete('cascade');
            $table->boolean('notify_price_drop')->default(true);
            $table->boolean('notify_availability')->default(true);
            $table->decimal('price_alert_threshold', 8, 2)->nullable();
            $table->json('notification_preferences')->nullable();
            $table->timestamps();
            
            // Unique constraint and indexes
            $table->unique(['user_id', 'scraped_ticket_id'], 'unique_user_ticket_bookmark');
            $table->index(['user_id', 'created_at'], 'idx_bookmarks_user_recent');
            $table->index(['scraped_ticket_id', 'notify_price_drop'], 'idx_bookmarks_ticket_alerts');
        });

        // Create ticket views table for analytics
        Schema::create('ticket_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scraped_ticket_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('session_id', 100)->nullable();
            $table->ipAddress('ip_address');
            $table->text('user_agent')->nullable();
            $table->string('referrer')->nullable();
            $table->unsignedInteger('view_duration_seconds')->nullable();
            $table->json('interaction_data')->nullable(); // Clicks, scrolls, etc.
            $table->timestamp('viewed_at');
            
            // Indexes for analytics
            $table->index(['scraped_ticket_id', 'viewed_at'], 'idx_views_ticket_time');
            $table->index(['user_id', 'viewed_at'], 'idx_views_user_time');
            $table->index(['viewed_at', 'ip_address'], 'idx_views_time_ip');
        });

        // Create search analytics table
        Schema::create('search_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('session_id', 100)->nullable();
            $table->string('search_query', 500);
            $table->json('filters_applied')->nullable();
            $table->unsignedInteger('results_count')->default(0);
            $table->unsignedInteger('clicks_count')->default(0);
            $table->decimal('click_through_rate', 5, 4)->default(0);
            $table->ipAddress('ip_address');
            $table->text('user_agent')->nullable();
            $table->timestamp('searched_at');
            
            // Indexes for search analytics
            $table->index(['search_query', 'searched_at'], 'idx_search_query_time');
            $table->index(['user_id', 'searched_at'], 'idx_search_user_time');
            $table->index(['results_count', 'click_through_rate'], 'idx_search_performance');
        });

        // Add composite indexes to existing tables for performance
        DB::statement('CREATE INDEX IF NOT EXISTS idx_scraped_tickets_complex_search 
                      ON scraped_tickets (sport, is_available, event_date, min_price, is_high_demand)');
        
        DB::statement('CREATE INDEX IF NOT EXISTS idx_scraped_tickets_location_search 
                      ON scraped_tickets (venue, location, country, event_date)');
        
        DB::statement('CREATE INDEX IF NOT EXISTS idx_scraped_tickets_platform_performance 
                      ON scraped_tickets (platform, scraping_quality_score, last_scraped_successfully)');

        // Add full-text search indexes
        if (config('database.default') === 'mysql') {
            DB::statement('CREATE FULLTEXT INDEX idx_scraped_tickets_fulltext_search 
                          ON scraped_tickets (title, venue, location, team, description)');
        }

        // Create materialized view for popular tickets (MySQL doesn't support materialized views, so we'll use a regular table)
        DB::statement('
            CREATE TABLE popular_tickets_view AS
            SELECT 
                st.*,
                COALESCE(tv.view_count, 0) as total_views,
                COALESCE(tb.bookmark_count, 0) as total_bookmarks,
                COALESCE(st.view_count + st.bookmark_count * 5 + st.share_count * 3, 0) as calculated_popularity
            FROM scraped_tickets st
            LEFT JOIN (
                SELECT scraped_ticket_id, COUNT(*) as view_count
                FROM ticket_views 
                WHERE viewed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY scraped_ticket_id
            ) tv ON st.id = tv.scraped_ticket_id
            LEFT JOIN (
                SELECT scraped_ticket_id, COUNT(*) as bookmark_count
                FROM ticket_bookmarks
                GROUP BY scraped_ticket_id
            ) tb ON st.id = tb.scraped_ticket_id
            WHERE st.status = "active" AND st.event_date > NOW()
        ');

        // Add indexes to the materialized view
        DB::statement('CREATE INDEX idx_popular_tickets_popularity ON popular_tickets_view (calculated_popularity DESC, event_date)');
        DB::statement('CREATE INDEX idx_popular_tickets_sport ON popular_tickets_view (sport, calculated_popularity DESC)');
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        // Drop materialized view
        DB::statement('DROP TABLE IF EXISTS popular_tickets_view');
        
        // Drop new tables
        Schema::dropIfExists('search_analytics');
        Schema::dropIfExists('ticket_views');
        Schema::dropIfExists('ticket_bookmarks');
        Schema::dropIfExists('ticket_price_history');
        
        // Remove added columns from scraped_tickets
        Schema::table('scraped_tickets', function (Blueprint $table) {
            $table->dropColumn([
                'view_count', 'bookmark_count', 'share_count', 'popularity_score',
                'previous_min_price', 'previous_max_price', 'last_price_change', 'price_change_percentage',
                'last_available_at', 'availability_changes',
                'description', 'tags', 'slug',
                'country', 'timezone', 'latitude', 'longitude',
                'last_scraped_successfully', 'scraping_quality_score'
            ]);
            
            // Drop added indexes
            $table->dropIndex('idx_scraped_tickets_popular');
            $table->dropIndex('idx_scraped_tickets_bookmarked');
            $table->dropIndex('idx_scraped_tickets_price_changes');
            $table->dropIndex('idx_scraped_tickets_location_sport');
            $table->dropIndex('idx_scraped_tickets_demand');
        });
        
        // Drop composite indexes
        DB::statement('DROP INDEX IF EXISTS idx_scraped_tickets_complex_search ON scraped_tickets');
        DB::statement('DROP INDEX IF EXISTS idx_scraped_tickets_location_search ON scraped_tickets');
        DB::statement('DROP INDEX IF EXISTS idx_scraped_tickets_platform_performance ON scraped_tickets');
        
        // Drop full-text indexes
        if (config('database.default') === 'mysql') {
            DB::statement('DROP INDEX IF EXISTS idx_scraped_tickets_fulltext_search ON scraped_tickets');
        }
    }
};
