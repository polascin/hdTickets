<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
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
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration only optimizes existing tables, no schema changes to reverse
    }

    /**
     * Add missing indexes only (MySQL only)
     */
    private function addMissingIndexes(): void
    {
        // Only run on MySQL connections
        if (config('database.default') !== 'mysql') {
            return;
        }

        // Check existing indexes to avoid duplicates
        $existingIndexes = collect(DB::select('SHOW INDEXES FROM users'))
            ->pluck('Key_name')
            ->toArray();

        // Add search index using raw SQL to handle TEXT column
        if (! in_array('users_search_index', $existingIndexes, TRUE)) {
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
     * Optimize table engines for performance (MySQL only)
     */
    private function optimizeTableEngines(): void
    {
        // Only run optimization on MySQL connections
        if (config('database.default') !== 'mysql') {
            return;
        }

        $tables = [
            'users', 'scraped_tickets', 'ticket_alerts', 'sessions',
            'cache', 'telescope_entries', 'migrations',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                try {
                    DB::statement("ALTER TABLE {$table} ENGINE=InnoDB");
                    DB::statement("OPTIMIZE TABLE {$table}");
                } catch (Exception $e) {
                    // Log the error but continue with migration
                    Log::info("Could not optimize table {$table}: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Configure Laravel for development mode optimizations
     */
    private function configureDevelopmentMode(): void
    {
        // Clear all caches for development
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');

        // Enable query logging in development
        if (config('app.env') === 'local') {
            DB::enableQueryLog();
        }
    }
};
