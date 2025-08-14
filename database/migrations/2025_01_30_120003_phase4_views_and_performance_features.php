<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations - Phase 4 Database Views and Performance Features
     */
    public function up(): void
    {
        // 1. Create database views for complex queries
        $this->createDatabaseViews();

        // 2. Create materialized views for analytics (MySQL 8.0+ compatible)
        $this->createMaterializedViews();

        // 3. Implement table partitioning for historical data
        $this->implementTablePartitioning();

        // 4. Create stored procedures for complex operations
        $this->createStoredProcedures();

        // 5. Create database triggers for automated tasks
        $this->createDatabaseTriggers();

        // 6. Setup read replica configurations
        $this->setupReadReplicaConfigurations();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop read replica configuration tables
        Schema::dropIfExists('query_routing_rules');
        Schema::dropIfExists('database_connections');

        // Drop triggers
        if (DB::getDriverName() === 'mysql') {
            DB::statement('DROP TRIGGER IF EXISTS update_cache_stats');
            DB::statement('DROP TRIGGER IF EXISTS log_user_changes');
            DB::statement('DROP TRIGGER IF EXISTS update_ticket_availability');
            DB::statement('DROP TRIGGER IF EXISTS update_user_activity_on_purchase');
            DB::statement('DROP TRIGGER IF EXISTS update_user_activity_on_alert_create');
        }

        // Drop stored procedures
        if (DB::getDriverName() === 'mysql') {
            DB::statement('DROP PROCEDURE IF EXISTS UpdateTicketPriceStats');
            DB::statement('DROP PROCEDURE IF EXISTS CleanupOldData');
            DB::statement('DROP PROCEDURE IF EXISTS RefreshMaterializedViews');
        }

        // Drop materialized views (tables)
        Schema::dropIfExists('mv_monthly_revenue_analytics');
        Schema::dropIfExists('mv_weekly_user_activity');
        Schema::dropIfExists('mv_daily_platform_stats');

        // Drop database views
        if (DB::getDriverName() === 'mysql') {
            DB::statement('DROP VIEW IF EXISTS v_alert_effectiveness');
            DB::statement('DROP VIEW IF EXISTS v_price_trend_analysis');
            DB::statement('DROP VIEW IF EXISTS v_platform_performance_metrics');
            DB::statement('DROP VIEW IF EXISTS v_user_purchase_analytics');
            DB::statement('DROP VIEW IF EXISTS v_ticket_availability_summary');
            DB::statement('DROP VIEW IF EXISTS v_active_ticket_monitoring');
        }

        // Note: Table partitioning changes are not easily reversible
        // Manual intervention would be required to remove partitioning
    }

    /**
     * Create database views for complex queries
     */
    private function createDatabaseViews(): void
    {
        if (DB::getDriverName() === 'mysql') {
            // View: Active ticket monitoring with user and event details
            DB::statement('DROP VIEW IF EXISTS v_active_ticket_monitoring');
            DB::statement('
                CREATE VIEW v_active_ticket_monitoring AS
                SELECT 
                    ta.id as alert_id,
                    ta.alert_name,
                    ta.status as alert_status,
                    ta.max_price,
                    ta.min_price,
                    ta.auto_purchase,
                    u.id as user_id,
                    u.name as user_name,
                    u.email as user_email,
                    se.id as event_id,
                    se.name as event_name,
                    se.venue as event_venue,
                    se.event_date,
                    se.status as event_status,
                    ta.last_checked_at,
                    ta.triggered_at,
                    ta.created_at
                FROM ticket_alerts ta
                JOIN users u ON ta.user_id = u.id
                JOIN sports_events se ON ta.sports_event_id = se.id
                WHERE ta.status = "active" 
                  AND u.is_active = 1
                  AND se.status IN ("scheduled", "on_sale")
            ');

            // View: Ticket availability summary by platform
            DB::statement('DROP VIEW IF EXISTS v_ticket_availability_summary');
            DB::statement('
                CREATE VIEW v_ticket_availability_summary AS
                SELECT 
                    st.platform,
                    st.sport,
                    st.event_date,
                    COUNT(*) as total_tickets,
                    SUM(CASE WHEN st.is_available = 1 THEN 1 ELSE 0 END) as available_tickets,
                    AVG(st.min_price) as avg_min_price,
                    AVG(st.max_price) as avg_max_price,
                    MIN(st.min_price) as lowest_price,
                    MAX(st.max_price) as highest_price,
                    SUM(st.availability) as total_availability,
                    COUNT(DISTINCT st.venue) as venues_count,
                    MAX(st.scraped_at) as last_updated
                FROM scraped_tickets st
                WHERE st.status = "active"
                GROUP BY st.platform, st.sport, st.event_date
                HAVING total_tickets > 0
            ');

            // View: User purchase performance analytics
            DB::statement('DROP VIEW IF EXISTS v_user_purchase_analytics');
            DB::statement('
                CREATE VIEW v_user_purchase_analytics AS
                SELECT 
                    u.id as user_id,
                    u.name as user_name,
                    u.email,
                    COUNT(pa.id) as total_attempts,
                    SUM(CASE WHEN pa.status = "success" THEN 1 ELSE 0 END) as successful_purchases,
                    SUM(CASE WHEN pa.status = "failed" THEN 1 ELSE 0 END) as failed_purchases,
                    ROUND((SUM(CASE WHEN pa.status = "success" THEN 1 ELSE 0 END) / COUNT(pa.id)) * 100, 2) as success_rate,
                    AVG(pa.final_price) as avg_purchase_price,
                    SUM(pa.final_price) as total_spent,
                    MIN(pa.started_at) as first_purchase_attempt,
                    MAX(pa.started_at) as latest_purchase_attempt,
                    COUNT(DISTINCT pa.platform) as platforms_used
                FROM users u
                LEFT JOIN purchase_queues pq ON u.id = pq.selected_by_user_id
                LEFT JOIN purchase_attempts pa ON pq.id = pa.purchase_queue_id
                WHERE u.is_active = 1
                GROUP BY u.id, u.name, u.email
                HAVING total_attempts > 0
            ');

            // View: Platform performance metrics
            DB::statement('DROP VIEW IF EXISTS v_platform_performance_metrics');
            DB::statement('
                CREATE VIEW v_platform_performance_metrics AS
                SELECT 
                    ss.platform,
                    COUNT(*) as total_operations,
                    SUM(CASE WHEN ss.status = "success" THEN 1 ELSE 0 END) as successful_operations,
                    SUM(CASE WHEN ss.status = "failed" THEN 1 ELSE 0 END) as failed_operations,
                    ROUND((SUM(CASE WHEN ss.status = "success" THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as success_rate,
                    AVG(ss.response_time_ms) as avg_response_time,
                    MIN(ss.response_time_ms) as min_response_time,
                    MAX(ss.response_time_ms) as max_response_time,
                    AVG(ss.results_count) as avg_results_per_operation,
                    MAX(ss.created_at) as last_operation,
                    COUNT(DISTINCT DATE(ss.created_at)) as active_days
                FROM scraping_stats ss
                WHERE ss.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY ss.platform
                ORDER BY success_rate DESC, avg_response_time ASC
            ');

            // View: Price trend analysis
            DB::statement('DROP VIEW IF EXISTS v_price_trend_analysis');
            DB::statement('
                CREATE VIEW v_price_trend_analysis AS
                SELECT 
                    st.platform,
                    st.sport,
                    st.venue,
                    DATE(tph.recorded_at) as price_date,
                    COUNT(DISTINCT tph.ticket_id) as tickets_tracked,
                    AVG(tph.price) as avg_price,
                    MIN(tph.price) as min_price,
                    MAX(tph.price) as max_price,
                    STDDEV(tph.price) as price_volatility,
                    SUM(tph.quantity) as total_quantity,
                    COUNT(*) as price_records
                FROM ticket_price_histories tph
                JOIN scraped_tickets st ON tph.ticket_id = st.id
                WHERE tph.recorded_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)
                GROUP BY st.platform, st.sport, st.venue, DATE(tph.recorded_at)
                ORDER BY price_date DESC, st.platform, st.sport
            ');

            // View: Alert effectiveness analysis
            DB::statement('DROP VIEW IF EXISTS v_alert_effectiveness');
            DB::statement('
                CREATE VIEW v_alert_effectiveness AS
                SELECT 
                    ta.id as alert_id,
                    ta.alert_name,
                    u.name as user_name,
                    ta.max_price,
                    ta.min_price,
                    ta.created_at as alert_created,
                    ta.triggered_at,
                    DATEDIFF(ta.triggered_at, ta.created_at) as days_to_trigger,
                    COUNT(pa.id) as purchase_attempts,
                    SUM(CASE WHEN pa.status = "success" THEN 1 ELSE 0 END) as successful_purchases,
                    AVG(pa.final_price) as avg_purchase_price,
                    (ta.max_price - AVG(pa.final_price)) as avg_savings,
                    se.name as event_name,
                    se.event_date
                FROM ticket_alerts ta
                JOIN users u ON ta.user_id = u.id
                JOIN sports_events se ON ta.sports_event_id = se.id
                LEFT JOIN purchase_queues pq ON pq.scraped_ticket_id IN (
                    SELECT st.id FROM scraped_tickets st 
                    WHERE st.home_team_id = se.id OR st.away_team_id = se.id OR st.venue_id = se.id
                )
                LEFT JOIN purchase_attempts pa ON pq.id = pa.purchase_queue_id
                WHERE ta.triggered_at IS NOT NULL
                GROUP BY ta.id, ta.alert_name, u.name, ta.max_price, ta.min_price, 
                         ta.created_at, ta.triggered_at, se.name, se.event_date
            ');
        }
    }

    /**
     * Create materialized views for analytics (simulated with tables + triggers)
     */
    private function createMaterializedViews(): void
    {
        if (DB::getDriverName() === 'mysql') {
            // Materialized view: Daily platform statistics
            Schema::create('mv_daily_platform_stats', function (Blueprint $table): void {
                $table->id();
                $table->date('stats_date');
                $table->string('platform', 100);
                $table->integer('total_tickets_scraped')->default(0);
                $table->integer('available_tickets')->default(0);
                $table->integer('successful_scrapes')->default(0);
                $table->integer('failed_scrapes')->default(0);
                $table->decimal('success_rate', 5, 2)->default(0);
                $table->decimal('avg_response_time_ms', 10, 2)->default(0);
                $table->decimal('avg_min_price', 10, 2)->nullable();
                $table->decimal('avg_max_price', 10, 2)->nullable();
                $table->integer('unique_events')->default(0);
                $table->timestamp('last_updated')->useCurrent()->useCurrentOnUpdate();

                $table->unique(['stats_date', 'platform']);
                $table->index(['stats_date', 'platform']);
                $table->index('stats_date');
            });

            // Materialized view: Weekly user activity summary
            Schema::create('mv_weekly_user_activity', function (Blueprint $table): void {
                $table->id();
                $table->date('week_start');
                $table->unsignedBigInteger('user_id');
                $table->integer('alerts_created')->default(0);
                $table->integer('alerts_triggered')->default(0);
                $table->integer('purchase_attempts')->default(0);
                $table->integer('successful_purchases')->default(0);
                $table->decimal('total_spent', 12, 2)->default(0);
                $table->integer('tickets_viewed')->default(0);
                $table->integer('login_count')->default(0);
                $table->timestamp('last_updated')->useCurrent()->useCurrentOnUpdate();

                $table->unique(['week_start', 'user_id']);
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->index(['week_start', 'user_id']);
            });

            // Materialized view: Monthly revenue analytics
            Schema::create('mv_monthly_revenue_analytics', function (Blueprint $table): void {
                $table->id();
                $table->date('month_start');
                $table->string('platform', 100);
                $table->string('sport', 100);
                $table->integer('total_purchases')->default(0);
                $table->decimal('total_revenue', 15, 2)->default(0);
                $table->decimal('avg_ticket_price', 10, 2)->default(0);
                $table->decimal('total_fees', 12, 2)->default(0);
                $table->integer('unique_buyers')->default(0);
                $table->integer('tickets_sold')->default(0);
                $table->timestamp('last_updated')->useCurrent()->useCurrentOnUpdate();

                $table->unique(['month_start', 'platform', 'sport']);
                $table->index(['month_start', 'platform']);
                $table->index('month_start');
            });

            // Initial population of materialized views
            $this->populateMaterializedViews();
        }
    }

    /**
     * Implement table partitioning for historical data
     */
    private function implementTablePartitioning(): void
    {
        if (DB::getDriverName() === 'mysql') {
            // Partition scraping_stats table by month (last 12 months + current + future)
            DB::statement('
                ALTER TABLE scraping_stats 
                PARTITION BY RANGE (YEAR(created_at) * 100 + MONTH(created_at)) (
                    PARTITION p202312 VALUES LESS THAN (202401),
                    PARTITION p202401 VALUES LESS THAN (202402),
                    PARTITION p202402 VALUES LESS THAN (202403),
                    PARTITION p202403 VALUES LESS THAN (202404),
                    PARTITION p202404 VALUES LESS THAN (202405),
                    PARTITION p202405 VALUES LESS THAN (202406),
                    PARTITION p202406 VALUES LESS THAN (202407),
                    PARTITION p202407 VALUES LESS THAN (202408),
                    PARTITION p202408 VALUES LESS THAN (202409),
                    PARTITION p202409 VALUES LESS THAN (202410),
                    PARTITION p202410 VALUES LESS THAN (202411),
                    PARTITION p202411 VALUES LESS THAN (202412),
                    PARTITION p202412 VALUES LESS THAN (202501),
                    PARTITION p202501 VALUES LESS THAN (202502),
                    PARTITION p_future VALUES LESS THAN MAXVALUE
                )
            ');

            // Partition activity_log table by month
            DB::statement('
                ALTER TABLE activity_log 
                PARTITION BY RANGE (YEAR(created_at) * 100 + MONTH(created_at)) (
                    PARTITION al_202312 VALUES LESS THAN (202401),
                    PARTITION al_202401 VALUES LESS THAN (202402),
                    PARTITION al_202402 VALUES LESS THAN (202403),
                    PARTITION al_202403 VALUES LESS THAN (202404),
                    PARTITION al_202404 VALUES LESS THAN (202405),
                    PARTITION al_202405 VALUES LESS THAN (202406),
                    PARTITION al_202406 VALUES LESS THAN (202407),
                    PARTITION al_202407 VALUES LESS THAN (202408),
                    PARTITION al_202408 VALUES LESS THAN (202409),
                    PARTITION al_202409 VALUES LESS THAN (202410),
                    PARTITION al_202410 VALUES LESS THAN (202411),
                    PARTITION al_202411 VALUES LESS THAN (202412),
                    PARTITION al_202412 VALUES LESS THAN (202501),
                    PARTITION al_202501 VALUES LESS THAN (202502),
                    PARTITION al_future VALUES LESS THAN MAXVALUE
                )
            ');

            // Partition audit_logs table by month (if exists)
            if (Schema::hasTable('audit_logs')) {
                DB::statement('
                    ALTER TABLE audit_logs 
                    PARTITION BY RANGE (YEAR(created_at) * 100 + MONTH(created_at)) (
                        PARTITION aud_202312 VALUES LESS THAN (202401),
                        PARTITION aud_202401 VALUES LESS THAN (202402),
                        PARTITION aud_202402 VALUES LESS THAN (202403),
                        PARTITION aud_202403 VALUES LESS THAN (202404),
                        PARTITION aud_202404 VALUES LESS THAN (202405),
                        PARTITION aud_202405 VALUES LESS THAN (202406),
                        PARTITION aud_202406 VALUES LESS THAN (202407),
                        PARTITION aud_202407 VALUES LESS THAN (202408),
                        PARTITION aud_202408 VALUES LESS THAN (202409),
                        PARTITION aud_202409 VALUES LESS THAN (202410),
                        PARTITION aud_202410 VALUES LESS THAN (202411),
                        PARTITION aud_202411 VALUES LESS THAN (202412),
                        PARTITION aud_202412 VALUES LESS THAN (202501),
                        PARTITION aud_202501 VALUES LESS THAN (202502),
                        PARTITION aud_future VALUES LESS THAN MAXVALUE
                    )
                ');
            }
        }
    }

    /**
     * Create stored procedures for complex operations
     */
    private function createStoredProcedures(): void
    {
        if (DB::getDriverName() === 'mysql') {
            // Procedure: Refresh materialized views
            DB::statement('DROP PROCEDURE IF EXISTS RefreshMaterializedViews');
            DB::statement('
                CREATE PROCEDURE RefreshMaterializedViews()
                BEGIN
                    DECLARE EXIT HANDLER FOR SQLEXCEPTION 
                    BEGIN
                        ROLLBACK;
                        RESIGNAL;
                    END;
                    
                    START TRANSACTION;
                    
                    -- Refresh daily platform stats
                    DELETE FROM mv_daily_platform_stats WHERE stats_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY);
                    
                    INSERT INTO mv_daily_platform_stats (
                        stats_date, platform, total_tickets_scraped, available_tickets,
                        successful_scrapes, failed_scrapes, success_rate, avg_response_time_ms,
                        avg_min_price, avg_max_price, unique_events
                    )
                    SELECT 
                        DATE(ss.created_at) as stats_date,
                        ss.platform,
                        COUNT(*) as total_tickets_scraped,
                        SUM(CASE WHEN ss.status = "success" THEN ss.results_count ELSE 0 END) as available_tickets,
                        SUM(CASE WHEN ss.status = "success" THEN 1 ELSE 0 END) as successful_scrapes,
                        SUM(CASE WHEN ss.status = "failed" THEN 1 ELSE 0 END) as failed_scrapes,
                        ROUND((SUM(CASE WHEN ss.status = "success" THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as success_rate,
                        AVG(ss.response_time_ms) as avg_response_time_ms,
                        (SELECT AVG(st.min_price) FROM scraped_tickets st WHERE st.platform = ss.platform AND DATE(st.created_at) = DATE(ss.created_at)) as avg_min_price,
                        (SELECT AVG(st.max_price) FROM scraped_tickets st WHERE st.platform = ss.platform AND DATE(st.created_at) = DATE(ss.created_at)) as avg_max_price,
                        (SELECT COUNT(DISTINCT st.venue) FROM scraped_tickets st WHERE st.platform = ss.platform AND DATE(st.created_at) = DATE(ss.created_at)) as unique_events
                    FROM scraping_stats ss
                    WHERE ss.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                    GROUP BY DATE(ss.created_at), ss.platform;
                    
                    COMMIT;
                END
            ');

            // Procedure: Clean up old data
            DB::statement('DROP PROCEDURE IF EXISTS CleanupOldData');
            DB::statement('
                CREATE PROCEDURE CleanupOldData()
                BEGIN
                    DECLARE cleanup_date DATE DEFAULT DATE_SUB(CURDATE(), INTERVAL 6 MONTH);
                    
                    -- Clean up old scraping stats (keep last 6 months)
                    DELETE FROM scraping_stats WHERE created_at < cleanup_date;
                    
                    -- Clean up old activity logs (keep last 6 months)
                    DELETE FROM activity_log WHERE created_at < cleanup_date;
                    
                    -- Clean up old cache entries
                    DELETE FROM cache_entries WHERE expires_at < NOW();
                    
                    -- Clean up old sessions (keep last 30 days)
                    DELETE FROM sessions WHERE last_activity < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 30 DAY));
                    
                    -- Clean up resolved system alerts (keep last 3 months)
                    DELETE FROM system_alerts 
                    WHERE status = "resolved" 
                      AND resolved_at < DATE_SUB(NOW(), INTERVAL 3 MONTH);
                    
                    -- Optimize tables after cleanup
                    OPTIMIZE TABLE scraping_stats, activity_log, cache_entries, sessions, system_alerts;
                END
            ');

            // Procedure: Update ticket price statistics
            DB::statement('DROP PROCEDURE IF EXISTS UpdateTicketPriceStats');
            DB::statement('
                CREATE PROCEDURE UpdateTicketPriceStats()
                BEGIN
                    UPDATE scraped_tickets st
                    SET 
                        predicted_demand = (
                            SELECT AVG(tph.price) / st.min_price 
                            FROM ticket_price_histories tph 
                            WHERE tph.ticket_id = st.id 
                              AND tph.recorded_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                        )
                    WHERE st.id IN (
                        SELECT DISTINCT tph2.ticket_id 
                        FROM ticket_price_histories tph2 
                        WHERE tph2.recorded_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                    );
                END
            ');
        }
    }

    /**
     * Create database triggers for automated tasks
     */
    private function createDatabaseTriggers(): void
    {
        if (DB::getDriverName() === 'mysql') {
            // Trigger: Update user last_activity_at on various actions
            DB::statement('DROP TRIGGER IF EXISTS update_user_activity_on_alert_create');
            DB::statement('
                CREATE TRIGGER update_user_activity_on_alert_create
                AFTER INSERT ON ticket_alerts
                FOR EACH ROW
                UPDATE users SET last_activity_at = NOW() WHERE id = NEW.user_id
            ');

            DB::statement('DROP TRIGGER IF EXISTS update_user_activity_on_purchase');
            DB::statement('
                CREATE TRIGGER update_user_activity_on_purchase
                AFTER INSERT ON purchase_attempts
                FOR EACH ROW
                UPDATE users u 
                JOIN purchase_queues pq ON pq.id = NEW.purchase_queue_id
                SET u.last_activity_at = NOW() 
                WHERE u.id = pq.selected_by_user_id
            ');

            // Trigger: Auto-update ticket availability status
            DB::statement('DROP TRIGGER IF EXISTS update_ticket_availability');
            DB::statement('
                CREATE TRIGGER update_ticket_availability
                BEFORE UPDATE ON scraped_tickets
                FOR EACH ROW
                SET NEW.is_available = CASE 
                    WHEN NEW.availability > 0 AND NEW.status = "active" THEN 1
                    ELSE 0
                END
            ');

            // Trigger: Log domain events for important entity changes
            DB::statement('DROP TRIGGER IF EXISTS log_user_changes');
            DB::statement('
                CREATE TRIGGER log_user_changes
                AFTER UPDATE ON users
                FOR EACH ROW
                INSERT INTO domain_events (
                    event_id, aggregate_type, aggregate_id, aggregate_version,
                    event_type, event_name, event_data, caused_by_user_id,
                    occurred_at
                )
                VALUES (
                    UUID(), "User", NEW.id, 1,
                    "UserUpdated", "User profile updated",
                    JSON_OBJECT(
                        "old_values", JSON_OBJECT("name", OLD.name, "email", OLD.email),
                        "new_values", JSON_OBJECT("name", NEW.name, "email", NEW.email)
                    ),
                    NEW.id,
                    NOW()
                )
            ');

            // Trigger: Auto-generate cache statistics
            DB::statement('DROP TRIGGER IF EXISTS update_cache_stats');
            DB::statement('
                CREATE TRIGGER update_cache_stats
                BEFORE UPDATE ON cache_entries
                FOR EACH ROW
                SET 
                    NEW.last_accessed_at = NOW(),
                    NEW.access_count = OLD.access_count + 1,
                    NEW.hit_count = OLD.hit_count + 1
            ');
        }
    }

    /**
     * Setup read replica configurations
     */
    private function setupReadReplicaConfigurations(): void
    {
        // Create configuration table for read replica management
        if (! Schema::hasTable('database_connections')) {
            Schema::create('database_connections', function (Blueprint $table): void {
                $table->id();
                $table->string('connection_name', 100)->unique();
                $table->enum('connection_type', ['master', 'read_replica', 'analytics'])->default('master');
                $table->string('host', 255);
                $table->integer('port')->default(3306);
                $table->string('database', 100);
                $table->string('username', 100);
                $table->text('password_encrypted'); // Encrypted password
                $table->json('connection_options')->nullable();
                $table->boolean('is_active')->default(TRUE);
                $table->integer('weight')->default(1); // For load balancing
                $table->integer('max_connections')->default(100);
                $table->integer('current_connections')->default(0);
                $table->decimal('lag_threshold_seconds', 5, 2)->default(1.0);
                $table->decimal('current_lag_seconds', 5, 2)->nullable();
                $table->timestamp('last_health_check')->nullable();
                $table->enum('health_status', ['healthy', 'warning', 'critical', 'offline'])->default('healthy');
                $table->json('performance_metrics')->nullable();
                $table->timestamps();

                $table->index(['connection_type', 'is_active', 'weight']);
                $table->index(['health_status', 'is_active']);
                $table->index('last_health_check');
            });
        }

        // Create query routing rules table
        if (! Schema::hasTable('query_routing_rules')) {
            Schema::create('query_routing_rules', function (Blueprint $table): void {
                $table->id();
                $table->string('rule_name', 100)->unique();
                $table->text('query_pattern'); // Regex or SQL pattern
                $table->enum('route_to', ['master', 'read_replica', 'analytics'])->default('read_replica');
                $table->integer('priority')->default(100); // Lower number = higher priority
                $table->boolean('is_active')->default(TRUE);
                $table->json('conditions')->nullable(); // Additional routing conditions
                $table->text('description')->nullable();
                $table->timestamps();

                $table->index(['is_active', 'priority']);
                $table->index('route_to');
            });
        }

        // Insert default routing rules
        DB::table('query_routing_rules')->insertOrIgnore([
            [
                'rule_name'     => 'analytics_queries',
                'query_pattern' => '%(mv_|_analytics|_stats|_summary)%',
                'route_to'      => 'analytics',
                'priority'      => 10,
                'is_active'     => TRUE,
                'description'   => 'Route analytics and reporting queries to analytics database',
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'rule_name'     => 'read_only_selects',
                'query_pattern' => '^SELECT%',
                'route_to'      => 'read_replica',
                'priority'      => 50,
                'is_active'     => TRUE,
                'description'   => 'Route SELECT queries to read replicas',
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'rule_name'     => 'write_operations',
                'query_pattern' => '^(INSERT|UPDATE|DELETE)%',
                'route_to'      => 'master',
                'priority'      => 1,
                'is_active'     => TRUE,
                'description'   => 'Route write operations to master database',
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
        ]);
    }

    /**
     * Populate initial materialized views
     */
    private function populateMaterializedViews(): void
    {
        if (DB::getDriverName() === 'mysql' && Schema::hasTable('mv_daily_platform_stats')) {
            // Initial population for last 30 days
            DB::statement('
                INSERT INTO mv_daily_platform_stats (
                    stats_date, platform, total_tickets_scraped, available_tickets,
                    successful_scrapes, failed_scrapes, success_rate, avg_response_time_ms,
                    avg_min_price, avg_max_price, unique_events
                )
                SELECT 
                    DATE(ss.created_at) as stats_date,
                    ss.platform,
                    COUNT(*) as total_tickets_scraped,
                    SUM(CASE WHEN ss.status = "success" THEN ss.results_count ELSE 0 END) as available_tickets,
                    SUM(CASE WHEN ss.status = "success" THEN 1 ELSE 0 END) as successful_scrapes,
                    SUM(CASE WHEN ss.status = "failed" THEN 1 ELSE 0 END) as failed_scrapes,
                    ROUND((SUM(CASE WHEN ss.status = "success" THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as success_rate,
                    AVG(ss.response_time_ms) as avg_response_time_ms,
                    0.00 as avg_min_price,
                    0.00 as avg_max_price,
                    0 as unique_events
                FROM scraping_stats ss
                WHERE ss.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                GROUP BY DATE(ss.created_at), ss.platform
                ON DUPLICATE KEY UPDATE
                    total_tickets_scraped = VALUES(total_tickets_scraped),
                    available_tickets = VALUES(available_tickets),
                    successful_scrapes = VALUES(successful_scrapes),
                    failed_scrapes = VALUES(failed_scrapes),
                    success_rate = VALUES(success_rate),
                    avg_response_time_ms = VALUES(avg_response_time_ms),
                    avg_min_price = VALUES(avg_min_price),
                    avg_max_price = VALUES(avg_max_price),
                    unique_events = VALUES(unique_events),
                    last_updated = NOW()
            ');
        }
    }
};
