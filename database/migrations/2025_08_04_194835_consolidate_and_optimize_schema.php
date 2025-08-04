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
        // Clean up any orphaned records and optimize table structure
        $this->cleanupOrphanedRecords();
        
        // Update table engine to InnoDB if not already (for better performance)
        $this->optimizeTableEngines();
        
        // Configure Laravel for development mode optimizations
        $this->configureDevelopmentMode();
    }

    /**
     * Add missing indexes only
     */
    private function addMissingIndexes(): void
    {
        // Check existing indexes to avoid duplicates
        $existingIndexes = collect(DB::select('SHOW INDEXES FROM users'))
            ->pluck('Key_name')
            ->toArray();
        
        // Add search index using raw SQL to handle TEXT column
        if (!in_array('users_search_index', $existingIndexes)) {
            DB::statement('CREATE INDEX users_search_index ON users (name, email(255), username)');
        }
    }

    /**
     * Clean up orphaned records
     */
    private function cleanupOrphanedRecords(): void
    {
        // Clean up old sessions (older than 7 days)
        if (Schema::hasTable('sessions')) {
            DB::table('sessions')
                ->where('last_activity', '<', now()->subDays(7)->timestamp)
                ->delete();
        }

        // Clean up old cache entries
        if (Schema::hasTable('cache')) {
            DB::table('cache')
                ->where('expiration', '<', now()->timestamp)
                ->delete();
        }

        // Clean up old telescope entries (keep only last 30 days in development)
        if (Schema::hasTable('telescope_entries')) {
            DB::table('telescope_entries')
                ->where('created_at', '<', now()->subDays(30))
                ->delete();
        }
    }

    /**
     * Optimize table engines for better performance
     */
    private function optimizeTableEngines(): void
    {
        $tables = [
            'users', 'scraped_tickets', 'ticket_alerts', 'sessions', 
            'cache', 'telescope_entries', 'migrations'
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::statement("ALTER TABLE {$table} ENGINE=InnoDB");
                DB::statement("OPTIMIZE TABLE {$table}");
            }
        }
    }

    /**
     * Configure Laravel for development mode optimizations
     */
    private function configureDevelopmentMode(): void
    {
        // Clear all caches for development
        \Artisan::call('config:clear');
        \Artisan::call('route:clear');
        \Artisan::call('view:clear');
        
        // Enable query logging in development
        if (config('app.env') === 'local') {
            DB::enableQueryLog();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the performance indexes we added
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_role_active_index');
            $table->dropIndex('users_verified_active_index');
            $table->dropIndex('users_created_role_index');
            $table->dropIndex('users_activity_active_index');
        });

        if (Schema::hasTable('scraped_tickets')) {
            Schema::table('scraped_tickets', function (Blueprint $table) {
                $table->dropIndex('scraped_tickets_created_platform_index');
                $table->dropIndex('scraped_tickets_event_platform_index');
                $table->dropIndex('scraped_tickets_price_availability_index');
            });
        }

        if (Schema::hasTable('ticket_alerts')) {
            Schema::table('ticket_alerts', function (Blueprint $table) {
                $table->dropIndex('ticket_alerts_user_active_index');
                $table->dropIndex('ticket_alerts_price_active_index');
            });
        }

        if (Schema::hasTable('sessions')) {
            Schema::table('sessions', function (Blueprint $table) {
                $table->dropIndex('sessions_last_activity_index');
            });
        }
    }
};
