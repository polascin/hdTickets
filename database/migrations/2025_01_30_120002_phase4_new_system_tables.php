<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations - Phase 4 New System Tables
     */
    public function up(): void
    {
        // 1. Create domain events table for event sourcing
        $this->createDomainEventsTable();

        // 2. Create comprehensive audit logs table
        $this->createAuditLogsTable();

        // 3. Create database-backed cache entries table
        $this->createCacheEntriesTable();

        // 4. Create enhanced job failures tracking table
        $this->createJobFailuresTable();

        // 5. Create platform configurations table (if not exists)
        $this->createPlatformConfigurationsTable();

        // 6. Create system health monitoring tables
        $this->createSystemHealthTables();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop system health tables
        Schema::dropIfExists('system_alerts');
        Schema::dropIfExists('system_metrics');

        // Drop main system tables
        Schema::dropIfExists('job_failures');
        Schema::dropIfExists('cache_entries');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('domain_events');

        // Note: platform_configurations might already exist, so we don't drop it
        // unless we created it in this migration
    }

    /**
     * Create domain events table for event sourcing
     */
    private function createDomainEventsTable(): void
    {
        if (! Schema::hasTable('domain_events')) {
            Schema::create('domain_events', function (Blueprint $table): void {
                $table->id();
                $table->uuid('event_id')->unique();
                $table->string('aggregate_type', 100); // User, Ticket, Purchase, etc.
                $table->string('aggregate_id', 100);
                $table->unsignedBigInteger('aggregate_version');
                $table->string('event_type', 150);
                $table->string('event_name', 200);
                $table->json('event_data');
                $table->json('event_metadata')->nullable();
                $table->unsignedBigInteger('caused_by_user_id')->nullable();
                $table->string('correlation_id', 100)->nullable(); // For tracking related events
                $table->string('causation_id', 100)->nullable(); // Event that caused this event
                $table->timestamp('occurred_at');
                $table->timestamp('recorded_at')->useCurrent();
                $table->boolean('is_processed')->default(FALSE);
                $table->timestamp('processed_at')->nullable();
                $table->text('processing_error')->nullable();
                $table->tinyInteger('processing_attempts')->default(0);

                // Indexes for event sourcing queries
                $table->index(['aggregate_type', 'aggregate_id', 'aggregate_version'], 'idx_aggregate_stream');
                $table->index(['event_type', 'occurred_at'], 'idx_event_type_time');
                $table->index(['correlation_id', 'occurred_at'], 'idx_correlation_time');
                $table->index(['causation_id'], 'idx_causation');
                $table->index(['is_processed', 'occurred_at'], 'idx_processing_queue');
                $table->index(['caused_by_user_id', 'occurred_at'], 'idx_user_events');
                $table->index('recorded_at');
                $table->index('occurred_at');

                // Foreign key for user who caused the event
                $table->foreign('caused_by_user_id')->references('id')->on('users')->onDelete('set null');

                // Unique constraint to prevent duplicate events
                $table->unique(['aggregate_type', 'aggregate_id', 'aggregate_version'], 'uk_aggregate_version');
            });
        }
    }

    /**
     * Create comprehensive audit logs table
     */
    private function createAuditLogsTable(): void
    {
        if (! Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table): void {
                $table->id();
                $table->uuid('audit_id')->unique();
                $table->string('auditable_type', 100); // Model class name
                $table->unsignedBigInteger('auditable_id'); // Model ID
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('event_type', 50); // created, updated, deleted, viewed, etc.
                $table->string('action_description', 255);
                $table->json('old_values')->nullable(); // Previous state
                $table->json('new_values')->nullable(); // New state
                $table->json('changed_fields')->nullable(); // Only changed fields
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->string('session_id', 100)->nullable();
                $table->string('request_id', 100)->nullable(); // Unique request identifier
                $table->string('route_name', 200)->nullable();
                $table->string('http_method', 10)->nullable();
                $table->text('request_url')->nullable();
                $table->json('request_data')->nullable(); // Sanitized request data
                $table->json('response_data')->nullable(); // Response summary
                $table->integer('response_status')->nullable();
                $table->string('severity', 20)->default('info'); // info, warning, error, critical
                $table->json('tags')->nullable(); // Searchable tags
                $table->json('context')->nullable(); // Additional context data
                $table->timestamp('created_at')->useCurrent();

                // Comprehensive indexing for audit queries
                $table->index(['auditable_type', 'auditable_id', 'created_at'], 'idx_auditable_time');
                $table->index(['user_id', 'created_at'], 'idx_user_audit_time');
                $table->index(['event_type', 'created_at'], 'idx_event_audit_time');
                $table->index(['severity', 'created_at'], 'idx_severity_time');
                $table->index(['session_id', 'created_at'], 'idx_session_audit');
                $table->index(['request_id'], 'idx_request_audit');
                $table->index(['ip_address', 'created_at'], 'idx_ip_audit_time');
                $table->index('created_at');

                // Foreign key for user
                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            });
        }
    }

    /**
     * Create enhanced database-backed cache entries table
     */
    private function createCacheEntriesTable(): void
    {
        // Rename existing cache table to cache_entries for better organization
        if (Schema::hasTable('cache') && ! Schema::hasTable('cache_entries')) {
            Schema::rename('cache', 'cache_entries_temp');
        }

        if (! Schema::hasTable('cache_entries')) {
            Schema::create('cache_entries', function (Blueprint $table): void {
                $table->id();
                $table->string('cache_key', 500)->unique();
                $table->string('cache_group', 100)->default('default'); // Group caches by type
                $table->longText('cache_value');
                $table->string('value_type', 50)->default('serialized'); // serialized, json, string
                $table->integer('ttl_seconds')->nullable(); // TTL in seconds
                $table->timestamp('expires_at')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
                $table->timestamp('last_accessed_at')->nullable();
                $table->integer('access_count')->default(0);
                $table->integer('hit_count')->default(0);
                $table->integer('miss_count')->default(0);
                $table->decimal('hit_rate', 5, 2)->storedAs('(hit_count / GREATEST(hit_count + miss_count, 1)) * 100');
                $table->bigInteger('size_bytes')->nullable(); // Size of cached data
                $table->json('tags')->nullable(); // Cache tags for group invalidation
                $table->json('metadata')->nullable(); // Additional cache metadata
                $table->boolean('is_compressed')->default(FALSE);
                $table->string('compression_type', 20)->nullable(); // gzip, brotli, etc.

                // Indexes for cache operations
                $table->index(['cache_group', 'expires_at'], 'idx_group_expiry');
                $table->index(['expires_at'], 'idx_expiry_cleanup');
                $table->index(['cache_group', 'last_accessed_at'], 'idx_group_access');
                $table->index(['hit_rate', 'cache_group'], 'idx_hit_rate_group');
                $table->index(['access_count', 'cache_group'], 'idx_access_count_group');
                $table->index(['size_bytes'], 'idx_size_analysis');
                $table->index(['created_at', 'cache_group'], 'idx_created_group');
            });

            // Migrate data from old cache table if it exists
            if (Schema::hasTable('cache_entries_temp')) {
                DB::statement('
                    INSERT INTO cache_entries (cache_key, cache_value, expires_at, created_at)
                    SELECT `key`, `value`, FROM_UNIXTIME(expiration), NOW()
                    FROM cache_entries_temp
                ');
                Schema::drop('cache_entries_temp');
            }
        }
    }

    /**
     * Create enhanced job failures tracking table
     */
    private function createJobFailuresTable(): void
    {
        // Enhance existing failed_jobs table or create new enhanced version
        if (! Schema::hasTable('job_failures')) {
            Schema::create('job_failures', function (Blueprint $table): void {
                $table->id();
                $table->uuid('failure_id')->unique();
                $table->string('job_class', 255);
                $table->string('job_method', 100)->nullable();
                $table->string('queue_name', 100);
                $table->string('connection_name', 100);
                $table->longText('job_payload');
                $table->longText('exception_message');
                $table->longText('exception_trace');
                $table->string('exception_class', 255);
                $table->string('exception_file', 500)->nullable();
                $table->integer('exception_line')->nullable();
                $table->json('job_data')->nullable(); // Extracted job data for analysis
                $table->json('context_data')->nullable(); // Environment context when job failed
                $table->integer('attempt_number')->default(1);
                $table->integer('max_attempts')->default(3);
                $table->timestamp('failed_at')->useCurrent();
                $table->timestamp('next_retry_at')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->enum('resolution_status', ['pending', 'retried', 'resolved', 'discarded'])->default('pending');
                $table->text('resolution_notes')->nullable();
                $table->unsignedBigInteger('resolved_by_user_id')->nullable();
                $table->string('severity', 20)->default('error'); // warning, error, critical
                $table->boolean('is_business_critical')->default(FALSE);
                $table->json('tags')->nullable(); // For categorization and filtering
                $table->decimal('execution_time_ms', 10, 2)->nullable();
                $table->integer('memory_usage_mb')->nullable();
                $table->json('system_metrics')->nullable(); // CPU, memory, etc. at time of failure

                // Indexes for job failure analysis
                $table->index(['job_class', 'failed_at'], 'idx_job_class_time');
                $table->index(['queue_name', 'failed_at'], 'idx_queue_time');
                $table->index(['resolution_status', 'failed_at'], 'idx_resolution_time');
                $table->index(['severity', 'failed_at'], 'idx_severity_time');
                $table->index(['is_business_critical', 'failed_at'], 'idx_critical_time');
                $table->index(['next_retry_at'], 'idx_retry_queue');
                $table->index(['exception_class', 'failed_at'], 'idx_exception_time');
                $table->index(['attempt_number', 'max_attempts'], 'idx_retry_attempts');
                $table->index('failed_at');

                // Foreign key for resolver
                $table->foreign('resolved_by_user_id')->references('id')->on('users')->onDelete('set null');
            });
        }
    }

    /**
     * Create platform configurations table (if not exists)
     */
    private function createPlatformConfigurationsTable(): void
    {
        if (! Schema::hasTable('platform_configurations')) {
            Schema::create('platform_configurations', function (Blueprint $table): void {
                $table->id();
                $table->string('platform', 100);
                $table->string('config_group', 100)->default('general'); // Group configs by type
                $table->string('config_key', 200);
                $table->json('config_value');
                $table->enum('value_type', ['string', 'integer', 'decimal', 'boolean', 'json', 'encrypted'])->default('json');
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(TRUE);
                $table->boolean('is_sensitive')->default(FALSE); // Contains sensitive data
                $table->boolean('requires_restart')->default(FALSE); // Config change requires restart
                $table->string('environment', 50)->default('production'); // production, staging, development
                $table->json('validation_rules')->nullable(); // JSON schema for validation
                $table->string('config_version', 20)->default('1.0');
                $table->timestamp('last_applied_at')->nullable();
                $table->unsignedBigInteger('created_by_user_id')->nullable();
                $table->unsignedBigInteger('updated_by_user_id')->nullable();
                $table->timestamps();

                // Indexes for platform configuration queries
                $table->unique(['platform', 'config_group', 'config_key', 'environment'], 'uk_platform_config');
                $table->index(['platform', 'is_active'], 'idx_platform_active');
                $table->index(['config_group', 'is_active'], 'idx_group_active');
                $table->index(['is_sensitive'], 'idx_sensitive_configs');
                $table->index(['requires_restart'], 'idx_restart_required');
                $table->index(['environment', 'is_active'], 'idx_env_active');
                $table->index(['last_applied_at'], 'idx_last_applied');

                // Foreign keys
                $table->foreign('created_by_user_id')->references('id')->on('users')->onDelete('set null');
                $table->foreign('updated_by_user_id')->references('id')->on('users')->onDelete('set null');
            });
        }
    }

    /**
     * Create system health monitoring tables
     */
    private function createSystemHealthTables(): void
    {
        // System metrics table
        if (! Schema::hasTable('system_metrics')) {
            Schema::create('system_metrics', function (Blueprint $table): void {
                $table->id();
                $table->string('metric_name', 100);
                $table->string('metric_group', 50)->default('system'); // system, database, application, scraper
                $table->decimal('metric_value', 15, 4);
                $table->string('metric_unit', 20)->nullable(); // MB, %, ms, etc.
                $table->string('instance_id', 100)->default('default');
                $table->json('dimensions')->nullable(); // Additional metric dimensions
                $table->timestamp('recorded_at');
                $table->timestamps();

                $table->index(['metric_name', 'recorded_at'], 'idx_metric_time');
                $table->index(['metric_group', 'recorded_at'], 'idx_group_time');
                $table->index(['instance_id', 'recorded_at'], 'idx_instance_time');
            });
        }

        // System alerts table
        if (! Schema::hasTable('system_alerts')) {
            Schema::create('system_alerts', function (Blueprint $table): void {
                $table->id();
                $table->uuid('alert_id')->unique();
                $table->string('alert_type', 100); // high_cpu, low_memory, scraper_down, etc.
                $table->string('alert_category', 50); // performance, availability, error
                $table->enum('severity', ['info', 'warning', 'error', 'critical']);
                $table->string('title', 255);
                $table->text('message');
                $table->json('alert_data')->nullable(); // Metric values, thresholds, etc.
                $table->string('source_component', 100)->nullable(); // Component that triggered alert
                $table->string('instance_id', 100)->default('default');
                $table->enum('status', ['active', 'acknowledged', 'resolved', 'suppressed'])->default('active');
                $table->timestamp('triggered_at');
                $table->timestamp('acknowledged_at')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->unsignedBigInteger('acknowledged_by_user_id')->nullable();
                $table->unsignedBigInteger('resolved_by_user_id')->nullable();
                $table->text('resolution_notes')->nullable();
                $table->boolean('auto_resolved')->default(FALSE);
                $table->timestamps();

                $table->index(['alert_type', 'status', 'triggered_at'], 'idx_alert_status_time');
                $table->index(['severity', 'status'], 'idx_severity_status');
                $table->index(['status', 'triggered_at'], 'idx_status_time');
                $table->index(['instance_id', 'status'], 'idx_instance_status');

                $table->foreign('acknowledged_by_user_id')->references('id')->on('users')->onDelete('set null');
                $table->foreign('resolved_by_user_id')->references('id')->on('users')->onDelete('set null');
            });
        }
    }
};
