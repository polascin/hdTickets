<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Drop the existing problematic trigger
        DB::statement('DROP TRIGGER IF EXISTS log_user_changes');
        
        // Step 2: Drop the function if it exists (clean slate)
        DB::statement('DROP FUNCTION IF EXISTS GetNextAggregateVersion');
        
        // Step 3: Create a simpler stored function for getting next aggregate version
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
        
        // Step 4: Create a new trigger with proper aggregate versioning
        DB::statement('
            CREATE TRIGGER log_user_changes
            AFTER UPDATE ON users 
            FOR EACH ROW
            BEGIN
                INSERT IGNORE INTO domain_events (
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
                        "old_values", JSON_OBJECT(
                            "name", OLD.name, 
                            "email", OLD.email, 
                            "login_count", OLD.login_count,
                            "last_login_at", OLD.last_login_at
                        ),
                        "new_values", JSON_OBJECT(
                            "name", NEW.name, 
                            "email", NEW.email, 
                            "login_count", NEW.login_count,
                            "last_login_at", NEW.last_login_at
                        ),
                        "changed_fields", JSON_ARRAY(
                            CASE WHEN OLD.name != NEW.name THEN "name" ELSE NULL END,
                            CASE WHEN OLD.email != NEW.email THEN "email" ELSE NULL END,
                            CASE WHEN OLD.login_count != NEW.login_count THEN "login_count" ELSE NULL END,
                            CASE WHEN OLD.last_login_at != NEW.last_login_at THEN "last_login_at" ELSE NULL END
                        )
                    ),
                    NEW.id,
                    NOW()
                );
            END
        ');
        
        // Step 5: Create error_log table if it doesn\'t exist for logging retry failures
        if (!Schema::hasTable('error_log')) {
            Schema::create('error_log', function (Blueprint $table) {
                $table->id();
                $table->text('error_message');
                $table->string('error_type')->default('domain_event_retry_failure');
                $table->json('error_context')->nullable();
                $table->timestamp('occurred_at');
                $table->index(['error_type', 'occurred_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the enhanced trigger and function
        DB::statement('DROP TRIGGER IF EXISTS log_user_changes');
        DB::statement('DROP FUNCTION IF EXISTS GetNextAggregateVersion');
        
        // Restore the original broken trigger (for rollback purposes only)
        // NOTE: This will have the same constraint violation issue
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
        
        // Drop the error log table if we created it
        Schema::dropIfExists('error_log');
    }
};