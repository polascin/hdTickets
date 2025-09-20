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
            // SQLite doesn't support stored functions like MySQL
            return;
        }

        // Drop the existing problematic trigger
        DB::statement('DROP TRIGGER IF EXISTS log_user_changes');

        // Ensure function is dropped then create a stored function to get next aggregate version
        DB::statement('DROP FUNCTION IF EXISTS GetNextAggregateVersion');
        DB::statement('
            CREATE FUNCTION GetNextAggregateVersion(p_aggregate_type VARCHAR(100), p_aggregate_id VARCHAR(100))
            RETURNS BIGINT UNSIGNED
            READS SQL DATA
            DETERMINISTIC
            BEGIN
                DECLARE next_version BIGINT UNSIGNED DEFAULT 1;
                
                SELECT COALESCE(MAX(aggregate_version), 0) + 1 INTO next_version
                FROM domain_events 
                WHERE aggregate_type = p_aggregate_type 
                AND aggregate_id = p_aggregate_id;
                
                RETURN next_version;
            END
        ');

        // Create a new trigger using the function
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
                GetNextAggregateVersion("User", NEW.id),
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
            // SQLite doesn't support stored functions like MySQL
            return;
        }

        // Drop the fixed trigger and function
        DB::statement('DROP TRIGGER IF EXISTS log_user_changes');
        DB::statement('DROP FUNCTION IF EXISTS GetNextAggregateVersion');

        // Restore the original broken trigger (for rollback purposes)
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
