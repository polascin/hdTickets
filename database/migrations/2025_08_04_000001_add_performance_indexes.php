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
        // Enhanced indexes for users table
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasIndex('users', 'idx_users_role_active')) {
                $table->index(['role', 'is_active'], 'idx_users_role_active');
            }
            if (!Schema::hasIndex('users', 'idx_users_last_login_active')) {
                $table->index(['last_login_at', 'is_active'], 'idx_users_last_login_active');
            }
        });

        // Enhanced indexes for ticket_alerts table
        if (Schema::hasTable('ticket_alerts')) {
            Schema::table('ticket_alerts', function (Blueprint $table) {
                if (!Schema::hasIndex('ticket_alerts', 'idx_alerts_user_status')) {
                    $table->index(['user_id', 'status'], 'idx_alerts_user_status');
                }
                if (!Schema::hasIndex('ticket_alerts', 'idx_alerts_created_status')) {
                    $table->index(['created_at', 'status'], 'idx_alerts_created_status');
                }
                if (!Schema::hasIndex('ticket_alerts', 'idx_alerts_price_range')) {
                    $table->index(['min_price', 'max_price'], 'idx_alerts_price_range');
                }
            });
        }

        // Enhanced indexes for purchase_attempts table
        if (Schema::hasTable('purchase_attempts')) {
            Schema::table('purchase_attempts', function (Blueprint $table) {
                if (!Schema::hasIndex('purchase_attempts', 'idx_purchase_status_created')) {
                    $table->index(['status', 'created_at'], 'idx_purchase_status_created');
                }
                if (!Schema::hasIndex('purchase_attempts', 'idx_purchase_platform_status')) {
                    $table->index(['platform', 'status'], 'idx_purchase_platform_status');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scraped_tickets', function (Blueprint $table) {
            $table->dropIndex('idx_platform_available');
            $table->dropIndex('idx_high_demand_available');
            $table->dropIndex('idx_event_date_available');
            $table->dropIndex('idx_platform_event_date');
            $table->dropIndex('idx_available_min_price');
            $table->dropIndex('idx_sport_available');
            $table->dropIndex('idx_status_updated');
            $table->dropIndex('idx_created_platform');
            $table->dropIndex('idx_platform_analytics');
            $table->dropIndex('idx_event_pricing');
        });

        // Drop full-text index if MySQL
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE scraped_tickets DROP INDEX search_idx');
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_role_active');
            $table->dropIndex('idx_users_last_login');
            $table->dropIndex('idx_users_created_role');
        });

        if (Schema::hasTable('ticket_alerts')) {
            Schema::table('ticket_alerts', function (Blueprint $table) {
                $table->dropIndex('idx_alerts_user_active');
                $table->dropIndex('idx_alerts_created_active');
                $table->dropIndex('idx_alerts_price_range');
            });
        }

        if (Schema::hasTable('purchase_attempts')) {
            Schema::table('purchase_attempts', function (Blueprint $table) {
                $table->dropIndex('idx_purchase_status_created');
                $table->dropIndex('idx_purchase_user_status');
            });
        }

        if (Schema::hasTable('ticket_price_history')) {
            Schema::table('ticket_price_history', function (Blueprint $table) {
                $table->dropIndex('idx_price_history_ticket_date');
                $table->dropIndex('idx_price_history_date_price');
            });
        }

        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
    }
};
