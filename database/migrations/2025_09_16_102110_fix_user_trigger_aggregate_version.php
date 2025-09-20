<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if we're using SQLite and skip this migration
        if (config('database.default') === 'sqlite') {
            // SQLite doesn't support complex triggers like MySQL
            // We'll handle event logging differently for SQLite
            return;
        }

        // Drop the existing trigger
        DB::statement('DROP TRIGGER IF EXISTS log_user_changes');

        // Create a new trigger that properly calculates the aggregate version
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
                UUID(), 
                "User", 
                NEW.id, 
                (
                    SELECT COALESCE(MAX(aggregate_version), 0) + 1 
                    FROM domain_events 
                    WHERE aggregate_type = "User" 
                    AND aggregate_id = NEW.id
                ),
                "UserUpdated", 
                "User profile updated",
                JSON_OBJECT(
                    "old_values", JSON_OBJECT("name", OLD.name, "email", OLD.email, "login_count", OLD.login_count),
                    "new_values", JSON_OBJECT("name", NEW.name, "email", NEW.email, "login_count", NEW.login_count)
                ),
                NEW.id,
                NOW()
            )
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Check if we're using SQLite and skip this migration
        if (config('database.default') === 'sqlite') {
            // SQLite doesn't support complex triggers like MySQL
            return;
        }

        // Drop the fixed trigger
        DB::statement('DROP TRIGGER IF EXISTS log_user_changes');

        // Restore the original trigger
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
    }
};
