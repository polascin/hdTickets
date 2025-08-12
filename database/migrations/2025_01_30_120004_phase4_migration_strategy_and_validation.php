<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations - Phase 4 Migration Strategy and Data Validation
     */
    public function up(): void
    {
        // 1. Create migration tracking and validation tables
        $this->createMigrationTrackingTables();

        // 2. Setup data validation framework
        $this->setupDataValidationFramework();

        // 3. Create rollback procedures
        $this->createRollbackProcedures();

        // 4. Implement zero-downtime migration helpers
        $this->implementZeroDowntimeMigrationHelpers();

        // 5. Run comprehensive data integrity checks
        $this->runDataIntegrityChecks();

        // 6. Create migration monitoring and alerts
        $this->createMigrationMonitoring();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop monitoring views and procedures
        if (DB::getDriverName() === 'mysql') {
            DB::statement('DROP PROCEDURE IF EXISTS CheckMigrationHealth');
            DB::statement('DROP VIEW IF EXISTS v_migration_health_status');
            DB::statement('DROP PROCEDURE IF EXISTS SyncShadowTable');
            DB::statement('DROP PROCEDURE IF EXISTS CreateDataBackup');
            DB::statement('DROP PROCEDURE IF EXISTS ExecuteMigrationRollback');
        }

        // Drop migration tracking tables
        Schema::dropIfExists('shadow_table_operations');
        Schema::dropIfExists('schema_snapshots');
        Schema::dropIfExists('data_validation_results');
        Schema::dropIfExists('data_validation_rules');
        Schema::dropIfExists('migration_executions');
    }

    /**
     * Create migration tracking and validation tables
     */
    private function createMigrationTrackingTables(): void
    {
        // Migration execution log
        if (! Schema::hasTable('migration_executions')) {
            Schema::create('migration_executions', function (Blueprint $table): void {
                $table->id();
                $table->string('migration_name', 255);
                $table->string('migration_batch', 50);
                $table->enum('execution_type', ['up', 'down', 'rollback'])->default('up');
                $table->enum('execution_status', ['started', 'in_progress', 'completed', 'failed', 'rolled_back'])->default('started');
                $table->text('execution_plan')->nullable(); // JSON execution steps
                $table->timestamp('started_at');
                $table->timestamp('completed_at')->nullable();
                $table->decimal('execution_time_seconds', 10, 3)->nullable();
                $table->text('error_message')->nullable();
                $table->text('rollback_sql')->nullable(); // SQL for rollback
                $table->json('affected_tables')->nullable();
                $table->bigInteger('affected_rows')->default(0);
                $table->json('performance_metrics')->nullable();
                $table->unsignedBigInteger('executed_by_user_id')->nullable();
                $table->string('environment', 50)->default('production');
                $table->timestamps();

                $table->index(['migration_name', 'execution_status']);
                $table->index(['execution_status', 'started_at']);
                $table->index(['migration_batch', 'execution_status']);
                $table->foreign('executed_by_user_id')->references('id')->on('users')->onDelete('set null');
            });
        }

        // Data validation results
        if (! Schema::hasTable('data_validation_results')) {
            Schema::create('data_validation_results', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('migration_execution_id')->nullable();
                $table->string('validation_name', 200);
                $table->string('validation_type', 100); // integrity, constraint, business_rule, etc.
                $table->string('table_name', 100);
                $table->text('validation_query');
                $table->bigInteger('expected_count')->nullable();
                $table->bigInteger('actual_count')->nullable();
                $table->enum('validation_status', ['passed', 'failed', 'warning', 'skipped'])->default('passed');
                $table->text('validation_message')->nullable();
                $table->json('validation_details')->nullable(); // Detailed results
                $table->decimal('execution_time_ms', 10, 2);
                $table->timestamp('validated_at');
                $table->timestamps();

                $table->foreign('migration_execution_id')->references('id')->on('migration_executions')->onDelete('cascade');
                $table->index(['validation_status', 'validated_at']);
                $table->index(['table_name', 'validation_type']);
                $table->index('validated_at');
            });
        }

        // Schema snapshots for rollback capability
        if (! Schema::hasTable('schema_snapshots')) {
            Schema::create('schema_snapshots', function (Blueprint $table): void {
                $table->id();
                $table->string('snapshot_name', 255);
                $table->string('migration_batch', 50);
                $table->enum('snapshot_type', ['pre_migration', 'post_migration', 'manual'])->default('pre_migration');
                $table->longText('schema_sql'); // Full schema CREATE statements
                $table->json('table_counts')->nullable(); // Row counts for each table
                $table->json('index_definitions')->nullable(); // Index definitions
                $table->json('foreign_keys')->nullable(); // Foreign key constraints
                $table->json('triggers')->nullable(); // Trigger definitions
                $table->bigInteger('total_database_size_bytes')->nullable();
                $table->timestamp('created_at');

                $table->index(['migration_batch', 'snapshot_type']);
                $table->index('created_at');
            });
        }
    }

    /**
     * Setup data validation framework
     */
    private function setupDataValidationFramework(): void
    {
        // Create validation rules configuration
        if (! Schema::hasTable('data_validation_rules')) {
            Schema::create('data_validation_rules', function (Blueprint $table): void {
                $table->id();
                $table->string('rule_name', 200)->unique();
                $table->string('rule_category', 100); // referential_integrity, data_quality, business_rules
                $table->string('target_table', 100);
                $table->text('validation_sql');
                $table->text('description');
                $table->enum('severity', ['critical', 'high', 'medium', 'low'])->default('high');
                $table->boolean('is_active')->default(TRUE);
                $table->boolean('run_pre_migration')->default(TRUE);
                $table->boolean('run_post_migration')->default(TRUE);
                $table->integer('timeout_seconds')->default(300);
                $table->json('expected_result')->nullable(); // What constitutes a pass
                $table->timestamps();

                $table->index(['rule_category', 'is_active']);
                $table->index(['target_table', 'is_active']);
                $table->index('severity');
            });
        }

        // Insert default validation rules
        $this->insertDefaultValidationRules();
    }

    /**
     * Insert default data validation rules
     */
    private function insertDefaultValidationRules(): void
    {
        $rules = [
            // Referential integrity checks
            [
                'rule_name'       => 'users_without_orphaned_alerts',
                'rule_category'   => 'referential_integrity',
                'target_table'    => 'ticket_alerts',
                'validation_sql'  => 'SELECT COUNT(*) FROM ticket_alerts ta LEFT JOIN users u ON ta.user_id = u.id WHERE u.id IS NULL',
                'description'     => 'Check for ticket alerts with missing user references',
                'severity'        => 'critical',
                'expected_result' => json_encode(['count' => 0]),
            ],
            [
                'rule_name'       => 'scraped_tickets_without_orphaned_references',
                'rule_category'   => 'referential_integrity',
                'target_table'    => 'scraped_tickets',
                'validation_sql'  => 'SELECT COUNT(*) FROM scraped_tickets st LEFT JOIN sports_venues sv ON st.venue_id = sv.id WHERE st.venue_id IS NOT NULL AND sv.id IS NULL',
                'description'     => 'Check for scraped tickets with invalid venue references',
                'severity'        => 'high',
                'expected_result' => json_encode(['count' => 0]),
            ],

            // Data quality checks
            [
                'rule_name'       => 'users_with_valid_emails',
                'rule_category'   => 'data_quality',
                'target_table'    => 'users',
                'validation_sql'  => 'SELECT COUNT(*) FROM users WHERE email NOT REGEXP "^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$"',
                'description'     => 'Check for users with invalid email formats',
                'severity'        => 'medium',
                'expected_result' => json_encode(['count' => 0]),
            ],
            [
                'rule_name'       => 'scraped_tickets_with_valid_prices',
                'rule_category'   => 'data_quality',
                'target_table'    => 'scraped_tickets',
                'validation_sql'  => 'SELECT COUNT(*) FROM scraped_tickets WHERE (min_price < 0 OR max_price < 0 OR (max_price IS NOT NULL AND min_price IS NOT NULL AND max_price < min_price))',
                'description'     => 'Check for scraped tickets with invalid price data',
                'severity'        => 'high',
                'expected_result' => json_encode(['count' => 0]),
            ],

            // Business rules validation
            [
                'rule_name'       => 'active_alerts_have_future_events',
                'rule_category'   => 'business_rules',
                'target_table'    => 'ticket_alerts',
                'validation_sql'  => 'SELECT COUNT(*) FROM ticket_alerts ta JOIN sports_events se ON ta.sports_event_id = se.id WHERE ta.status = "active" AND se.event_date < CURDATE()',
                'description'     => 'Check for active alerts on past events',
                'severity'        => 'medium',
                'expected_result' => json_encode(['count' => 0]),
            ],
            [
                'rule_name'       => 'purchase_attempts_have_valid_prices',
                'rule_category'   => 'business_rules',
                'target_table'    => 'purchase_attempts',
                'validation_sql'  => 'SELECT COUNT(*) FROM purchase_attempts WHERE status = "success" AND (final_price IS NULL OR final_price <= 0)',
                'description'     => 'Check for successful purchases without valid prices',
                'severity'        => 'critical',
                'expected_result' => json_encode(['count' => 0]),
            ],

            // Performance and system checks
            [
                'rule_name'       => 'no_duplicate_scraped_tickets',
                'rule_category'   => 'data_quality',
                'target_table'    => 'scraped_tickets',
                'validation_sql'  => 'SELECT COUNT(*) - COUNT(DISTINCT platform, external_id, event_date) FROM scraped_tickets WHERE external_id IS NOT NULL',
                'description'     => 'Check for duplicate scraped tickets',
                'severity'        => 'medium',
                'expected_result' => json_encode(['count' => 0]),
            ],
            [
                'rule_name'       => 'users_unique_email_constraint',
                'rule_category'   => 'referential_integrity',
                'target_table'    => 'users',
                'validation_sql'  => 'SELECT COUNT(*) - COUNT(DISTINCT email) FROM users',
                'description'     => 'Check for duplicate user emails',
                'severity'        => 'critical',
                'expected_result' => json_encode(['count' => 0]),
            ],
        ];

        foreach ($rules as $rule) {
            DB::table('data_validation_rules')->insertOrIgnore(array_merge($rule, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    /**
     * Create rollback procedures
     */
    private function createRollbackProcedures(): void
    {
        if (DB::getDriverName() === 'mysql') {
            // Create comprehensive rollback procedure
            DB::statement('DROP PROCEDURE IF EXISTS ExecuteMigrationRollback');
            DB::statement('
                CREATE PROCEDURE ExecuteMigrationRollback(
                    IN p_migration_batch VARCHAR(50),
                    IN p_user_id BIGINT UNSIGNED,
                    OUT p_result VARCHAR(255)
                )
                BEGIN
                    DECLARE v_rollback_sql TEXT;
                    DECLARE v_migration_name VARCHAR(255);
                    DECLARE v_execution_id BIGINT UNSIGNED;
                    DECLARE done INT DEFAULT FALSE;
                    DECLARE rollback_cursor CURSOR FOR 
                        SELECT id, migration_name, rollback_sql 
                        FROM migration_executions 
                        WHERE migration_batch = p_migration_batch 
                          AND execution_status = "completed"
                        ORDER BY id DESC;
                    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
                    DECLARE EXIT HANDLER FOR SQLEXCEPTION 
                    BEGIN
                        GET DIAGNOSTICS CONDITION 1 @p1 = MESSAGE_TEXT;
                        SET p_result = CONCAT("Rollback failed: ", @p1);
                        ROLLBACK;
                        RESIGNAL;
                    END;
                    
                    START TRANSACTION;
                    
                    -- Create rollback execution record
                    INSERT INTO migration_executions (
                        migration_name, migration_batch, execution_type, execution_status,
                        started_at, executed_by_user_id, environment
                    ) VALUES (
                        CONCAT("ROLLBACK_", p_migration_batch), p_migration_batch, "rollback", "started",
                        NOW(), p_user_id, "production"
                    );
                    
                    SET v_execution_id = LAST_INSERT_ID();
                    
                    -- Execute rollback for each migration in reverse order
                    OPEN rollback_cursor;
                    rollback_loop: LOOP
                        FETCH rollback_cursor INTO v_execution_id, v_migration_name, v_rollback_sql;
                        IF done THEN
                            LEAVE rollback_loop;
                        END IF;
                        
                        -- Execute rollback SQL if available
                        IF v_rollback_sql IS NOT NULL AND LENGTH(v_rollback_sql) > 0 THEN
                            SET @sql = v_rollback_sql;
                            PREPARE stmt FROM @sql;
                            EXECUTE stmt;
                            DEALLOCATE PREPARE stmt;
                        END IF;
                        
                        -- Mark original migration as rolled back
                        UPDATE migration_executions 
                        SET execution_status = "rolled_back", completed_at = NOW() 
                        WHERE id = v_execution_id;
                        
                    END LOOP;
                    CLOSE rollback_cursor;
                    
                    -- Update rollback execution status
                    UPDATE migration_executions 
                    SET execution_status = "completed", completed_at = NOW(),
                        execution_time_seconds = TIMESTAMPDIFF(MICROSECOND, started_at, NOW()) / 1000000
                    WHERE id = v_execution_id;
                    
                    COMMIT;
                    SET p_result = "Rollback completed successfully";
                    
                END
            ');

            // Create data backup procedure
            DB::statement('DROP PROCEDURE IF EXISTS CreateDataBackup');
            DB::statement('
                CREATE PROCEDURE CreateDataBackup(
                    IN p_backup_name VARCHAR(255),
                    IN p_table_names TEXT,
                    OUT p_result VARCHAR(255)
                )
                BEGIN
                    DECLARE v_table_name VARCHAR(100);
                    DECLARE v_backup_table VARCHAR(100);
                    DECLARE v_pos INT DEFAULT 1;
                    DECLARE v_next_pos INT;
                    
                    DECLARE EXIT HANDLER FOR SQLEXCEPTION 
                    BEGIN
                        GET DIAGNOSTICS CONDITION 1 @p1 = MESSAGE_TEXT;
                        SET p_result = CONCAT("Backup failed: ", @p1);
                        ROLLBACK;
                        RESIGNAL;
                    END;
                    
                    START TRANSACTION;
                    
                    -- Parse table names and create backups
                    table_loop: LOOP
                        SET v_next_pos = LOCATE(",", p_table_names, v_pos);
                        IF v_next_pos = 0 THEN
                            SET v_table_name = TRIM(SUBSTRING(p_table_names, v_pos));
                        ELSE
                            SET v_table_name = TRIM(SUBSTRING(p_table_names, v_pos, v_next_pos - v_pos));
                        END IF;
                        
                        IF LENGTH(v_table_name) > 0 THEN
                            SET v_backup_table = CONCAT("backup_", p_backup_name, "_", v_table_name);
                            
                            -- Drop existing backup table if exists
                            SET @sql = CONCAT("DROP TABLE IF EXISTS ", v_backup_table);
                            PREPARE stmt FROM @sql;
                            EXECUTE stmt;
                            DEALLOCATE PREPARE stmt;
                            
                            -- Create backup table
                            SET @sql = CONCAT("CREATE TABLE ", v_backup_table, " AS SELECT * FROM ", v_table_name);
                            PREPARE stmt FROM @sql;
                            EXECUTE stmt;
                            DEALLOCATE PREPARE stmt;
                        END IF;
                        
                        IF v_next_pos = 0 THEN
                            LEAVE table_loop;
                        END IF;
                        
                        SET v_pos = v_next_pos + 1;
                    END LOOP;
                    
                    COMMIT;
                    SET p_result = "Backup completed successfully";
                    
                END
            ');
        }
    }

    /**
     * Implement zero-downtime migration helpers
     */
    private function implementZeroDowntimeMigrationHelpers(): void
    {
        // Create shadow table management
        if (! Schema::hasTable('shadow_table_operations')) {
            Schema::create('shadow_table_operations', function (Blueprint $table): void {
                $table->id();
                $table->string('operation_name', 200);
                $table->string('source_table', 100);
                $table->string('shadow_table', 100);
                $table->enum('operation_type', ['create', 'sync', 'swap', 'cleanup'])->default('create');
                $table->enum('operation_status', ['pending', 'running', 'completed', 'failed'])->default('pending');
                $table->text('operation_sql')->nullable();
                $table->bigInteger('rows_processed')->default(0);
                $table->bigInteger('total_rows')->nullable();
                $table->decimal('progress_percentage', 5, 2)->default(0);
                $table->timestamp('started_at');
                $table->timestamp('completed_at')->nullable();
                $table->text('error_message')->nullable();
                $table->timestamps();

                $table->index(['source_table', 'operation_status']);
                $table->index(['operation_status', 'started_at']);
            });
        }

        if (DB::getDriverName() === 'mysql') {
            // Create shadow table sync procedure
            DB::statement('DROP PROCEDURE IF EXISTS SyncShadowTable');
            DB::statement('
                CREATE PROCEDURE SyncShadowTable(
                    IN p_source_table VARCHAR(100),
                    IN p_shadow_table VARCHAR(100),
                    IN p_batch_size INT DEFAULT 1000,
                    OUT p_result VARCHAR(255)
                )
                BEGIN
                    DECLARE v_last_id BIGINT DEFAULT 0;
                    DECLARE v_processed_rows BIGINT DEFAULT 0;
                    DECLARE v_batch_count INT DEFAULT 0;
                    
                    DECLARE EXIT HANDLER FOR SQLEXCEPTION 
                    BEGIN
                        GET DIAGNOSTICS CONDITION 1 @p1 = MESSAGE_TEXT;
                        SET p_result = CONCAT("Sync failed: ", @p1);
                        ROLLBACK;
                        RESIGNAL;
                    END;
                    
                    -- Create operation record
                    INSERT INTO shadow_table_operations (
                        operation_name, source_table, shadow_table, operation_type,
                        operation_status, started_at
                    ) VALUES (
                        CONCAT("SYNC_", p_source_table, "_TO_", p_shadow_table),
                        p_source_table, p_shadow_table, "sync", "running", NOW()
                    );
                    
                    SET @operation_id = LAST_INSERT_ID();
                    
                    -- Batch sync loop
                    sync_loop: LOOP
                        -- Dynamic SQL to sync batch
                        SET @sql = CONCAT(
                            "INSERT INTO ", p_shadow_table, " ",
                            "SELECT * FROM ", p_source_table, " ",
                            "WHERE id > ", v_last_id, " ",
                            "ORDER BY id LIMIT ", p_batch_size,
                            " ON DUPLICATE KEY UPDATE id = VALUES(id)"
                        );
                        
                        PREPARE stmt FROM @sql;
                        EXECUTE stmt;
                        SET v_batch_count = ROW_COUNT();
                        DEALLOCATE PREPARE stmt;
                        
                        IF v_batch_count = 0 THEN
                            LEAVE sync_loop;
                        END IF;
                        
                        SET v_processed_rows = v_processed_rows + v_batch_count;
                        SET v_last_id = v_last_id + p_batch_size;
                        
                        -- Update progress
                        UPDATE shadow_table_operations 
                        SET rows_processed = v_processed_rows 
                        WHERE id = @operation_id;
                        
                        -- Small delay to reduce system load
                        SELECT SLEEP(0.01);
                        
                    END LOOP;
                    
                    -- Mark operation as completed
                    UPDATE shadow_table_operations 
                    SET operation_status = "completed", completed_at = NOW(),
                        rows_processed = v_processed_rows
                    WHERE id = @operation_id;
                    
                    SET p_result = CONCAT("Sync completed. Processed ", v_processed_rows, " rows.");
                    
                END
            ');
        }
    }

    /**
     * Run comprehensive data integrity checks
     */
    private function runDataIntegrityChecks(): void
    {
        $migrationExecutionId = $this->createMigrationExecutionRecord();

        // Run all active validation rules
        $validationRules = DB::table('data_validation_rules')
            ->where('is_active', TRUE)
            ->where('run_post_migration', TRUE)
            ->orderBy('severity', 'desc')
            ->get();

        foreach ($validationRules as $rule) {
            $this->executeValidationRule($rule, $migrationExecutionId);
        }

        $this->completeMigrationExecutionRecord($migrationExecutionId);
    }

    /**
     * Execute a single validation rule
     *
     * @param mixed $rule
     * @param mixed $migrationExecutionId
     */
    private function executeValidationRule($rule, $migrationExecutionId): void
    {
        $startTime = microtime(TRUE);

        try {
            // Execute the validation query
            $result = DB::select($rule->validation_sql);
            $actualCount = $result[0]->{'COUNT(*)'} ?? 0;

            // Determine validation status
            $expectedResult = json_decode($rule->expected_result, TRUE);
            $expectedCount = $expectedResult['count'] ?? 0;
            $status = ($actualCount === $expectedCount) ? 'passed' : 'failed';

            $endTime = microtime(TRUE);
            $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

            // Log validation result
            DB::table('data_validation_results')->insert([
                'migration_execution_id' => $migrationExecutionId,
                'validation_name'        => $rule->rule_name,
                'validation_type'        => $rule->rule_category,
                'table_name'             => $rule->target_table,
                'validation_query'       => $rule->validation_sql,
                'expected_count'         => $expectedCount,
                'actual_count'           => $actualCount,
                'validation_status'      => $status,
                'validation_message'     => $status === 'passed' ? 'Validation passed' : "Expected {$expectedCount}, got {$actualCount}",
                'execution_time_ms'      => $executionTime,
                'validated_at'           => now(),
                'created_at'             => now(),
                'updated_at'             => now(),
            ]);

            if ($status === 'failed' && $rule->severity === 'critical') {
                Log::error("Critical validation failed: {$rule->rule_name}", [
                    'expected' => $expectedCount,
                    'actual'   => $actualCount,
                    'table'    => $rule->target_table,
                ]);
            }
        } catch (Exception $e) {
            $endTime = microtime(TRUE);
            $executionTime = ($endTime - $startTime) * 1000;

            DB::table('data_validation_results')->insert([
                'migration_execution_id' => $migrationExecutionId,
                'validation_name'        => $rule->rule_name,
                'validation_type'        => $rule->rule_category,
                'table_name'             => $rule->target_table,
                'validation_query'       => $rule->validation_sql,
                'validation_status'      => 'failed',
                'validation_message'     => 'Query execution failed: ' . $e->getMessage(),
                'execution_time_ms'      => $executionTime,
                'validated_at'           => now(),
                'created_at'             => now(),
                'updated_at'             => now(),
            ]);

            Log::error("Validation query failed: {$rule->rule_name}", [
                'error' => $e->getMessage(),
                'query' => $rule->validation_sql,
            ]);
        }
    }

    /**
     * Create migration execution record
     */
    private function createMigrationExecutionRecord(): int
    {
        return DB::table('migration_executions')->insertGetId([
            'migration_name'   => 'Phase4_DataValidation',
            'migration_batch'  => 'phase4_optimization',
            'execution_type'   => 'up',
            'execution_status' => 'started',
            'started_at'       => now(),
            'environment'      => config('app.env', 'production'),
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);
    }

    /**
     * Complete migration execution record
     *
     * @param mixed $executionId
     */
    private function completeMigrationExecutionRecord($executionId): void
    {
        $completedAt = now();
        $startedAt = DB::table('migration_executions')
            ->where('id', $executionId)
            ->value('started_at');

        $executionTime = $completedAt->diffInSeconds($startedAt, TRUE);

        // Count validation results
        $validationResults = DB::table('data_validation_results')
            ->where('migration_execution_id', $executionId)
            ->selectRaw('validation_status, COUNT(*) as count')
            ->groupBy('validation_status')
            ->pluck('count', 'validation_status');

        $failedCount = $validationResults['failed'] ?? 0;
        $status = $failedCount > 0 ? 'failed' : 'completed';

        DB::table('migration_executions')
            ->where('id', $executionId)
            ->update([
                'execution_status'       => $status,
                'completed_at'           => $completedAt,
                'execution_time_seconds' => $executionTime,
                'performance_metrics'    => json_encode([
                    'validation_results' => $validationResults,
                    'total_validations'  => $validationResults->sum(),
                ]),
                'updated_at' => now(),
            ]);
    }

    /**
     * Create migration monitoring and alerts
     */
    private function createMigrationMonitoring(): void
    {
        // Create migration health check view
        if (DB::getDriverName() === 'mysql') {
            DB::statement('DROP VIEW IF EXISTS v_migration_health_status');
            DB::statement('
                CREATE VIEW v_migration_health_status AS
                SELECT 
                    me.migration_batch,
                    COUNT(*) as total_migrations,
                    SUM(CASE WHEN me.execution_status = "completed" THEN 1 ELSE 0 END) as completed_count,
                    SUM(CASE WHEN me.execution_status = "failed" THEN 1 ELSE 0 END) as failed_count,
                    SUM(CASE WHEN me.execution_status IN ("started", "in_progress") THEN 1 ELSE 0 END) as running_count,
                    AVG(me.execution_time_seconds) as avg_execution_time,
                    MAX(me.started_at) as latest_migration,
                    (SELECT COUNT(*) FROM data_validation_results dvr 
                     JOIN migration_executions me2 ON dvr.migration_execution_id = me2.id 
                     WHERE me2.migration_batch = me.migration_batch AND dvr.validation_status = "failed") as failed_validations
                FROM migration_executions me
                GROUP BY me.migration_batch
                ORDER BY latest_migration DESC
            ');

            // Create procedure to check migration health
            DB::statement('DROP PROCEDURE IF EXISTS CheckMigrationHealth');
            DB::statement('
                CREATE PROCEDURE CheckMigrationHealth()
                BEGIN
                    DECLARE v_failed_migrations INT DEFAULT 0;
                    DECLARE v_failed_validations INT DEFAULT 0;
                    DECLARE v_running_too_long INT DEFAULT 0;
                    
                    -- Check for failed migrations in last 24 hours
                    SELECT COUNT(*) INTO v_failed_migrations
                    FROM migration_executions 
                    WHERE execution_status = "failed" 
                      AND started_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR);
                    
                    -- Check for failed validations in last 24 hours
                    SELECT COUNT(*) INTO v_failed_validations
                    FROM data_validation_results 
                    WHERE validation_status = "failed" 
                      AND validated_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR);
                    
                    -- Check for migrations running too long (over 1 hour)
                    SELECT COUNT(*) INTO v_running_too_long
                    FROM migration_executions 
                    WHERE execution_status IN ("started", "in_progress")
                      AND started_at < DATE_SUB(NOW(), INTERVAL 1 HOUR);
                    
                    -- Create alerts if needed
                    IF v_failed_migrations > 0 OR v_failed_validations > 0 OR v_running_too_long > 0 THEN
                        INSERT INTO system_alerts (
                            alert_id, alert_type, alert_category, severity, title, message,
                            alert_data, source_component, triggered_at, created_at, updated_at
                        ) VALUES (
                            UUID(), "migration_health", "data_integrity", 
                            CASE 
                                WHEN v_failed_migrations > 0 OR v_running_too_long > 0 THEN "critical"
                                WHEN v_failed_validations > 0 THEN "warning"
                                ELSE "info"
                            END,
                            "Database Migration Health Alert",
                            CONCAT("Failed migrations: ", v_failed_migrations, 
                                   ", Failed validations: ", v_failed_validations,
                                   ", Long-running migrations: ", v_running_too_long),
                            JSON_OBJECT(
                                "failed_migrations", v_failed_migrations,
                                "failed_validations", v_failed_validations,
                                "long_running", v_running_too_long
                            ),
                            "migration_system",
                            NOW(), NOW(), NOW()
                        );
                    END IF;
                    
                END
            ');
        }
    }
};
