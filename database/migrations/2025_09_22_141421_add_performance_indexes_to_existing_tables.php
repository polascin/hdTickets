<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add indexes to scraped_tickets table for dashboard queries
        Schema::table('scraped_tickets', function (Blueprint $table) {
            $table->index(['status', 'event_date'], 'idx_scraped_tickets_status_event_date');
            $table->index(['sport', 'status'], 'idx_scraped_tickets_sport_status');
            $table->index(['platform', 'status'], 'idx_scraped_tickets_platform_status');
            $table->index(['scraped_at', 'status'], 'idx_scraped_tickets_scraped_at_status');
            $table->index(['min_price', 'status'], 'idx_scraped_tickets_price_status');
            $table->index(['event_date', 'sport', 'status'], 'idx_scraped_tickets_composite');
        });

        // Add indexes to ticket_alerts table for user lookups
        Schema::table('ticket_alerts', function (Blueprint $table) {
            $table->index(['user_id', 'status'], 'idx_ticket_alerts_user_status');
            $table->index(['user_id', 'alert_type'], 'idx_ticket_alerts_user_type');
            $table->index(['status', 'last_triggered_at'], 'idx_ticket_alerts_status_triggered');
            $table->index(['user_id', 'created_at'], 'idx_ticket_alerts_user_created');
        });

        // Add indexes to users table for dashboard queries
        Schema::table('users', function (Blueprint $table) {
            $table->index(['role', 'created_at'], 'idx_users_role_created');
            $table->index(['email_verified_at', 'role'], 'idx_users_verified_role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove indexes from scraped_tickets table
        Schema::table('scraped_tickets', function (Blueprint $table) {
            $table->dropIndex('idx_scraped_tickets_status_event_date');
            $table->dropIndex('idx_scraped_tickets_sport_status');
            $table->dropIndex('idx_scraped_tickets_platform_status');
            $table->dropIndex('idx_scraped_tickets_scraped_at_status');
            $table->dropIndex('idx_scraped_tickets_price_status');
            $table->dropIndex('idx_scraped_tickets_composite');
        });

        // Remove indexes from ticket_alerts table
        Schema::table('ticket_alerts', function (Blueprint $table) {
            $table->dropIndex('idx_ticket_alerts_user_status');
            $table->dropIndex('idx_ticket_alerts_user_type');
            $table->dropIndex('idx_ticket_alerts_status_triggered');
            $table->dropIndex('idx_ticket_alerts_user_created');
        });

        // Remove indexes from users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_role_created');
            $table->dropIndex('idx_users_verified_role');
        });
    }
};
