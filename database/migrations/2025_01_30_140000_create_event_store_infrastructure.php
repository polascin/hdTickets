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
        // Event Store main table
        Schema::create('event_store', function (Blueprint $table): void {
            $table->id();
            $table->uuid('event_id')->unique()->index();
            $table->string('event_type', 255)->index();
            $table->string('aggregate_root_id', 255)->index();
            $table->string('aggregate_type', 100)->index();
            $table->integer('aggregate_version')->index();
            $table->json('payload');
            $table->json('metadata')->nullable();
            $table->timestamp('recorded_at')->index();
            $table->string('event_version', 10)->default('1.0');

            // Compound index for aggregate querying
            $table->index(['aggregate_root_id', 'aggregate_version']);
            $table->index(['event_type', 'recorded_at']);
            $table->index(['aggregate_type', 'recorded_at']);
        });

        // Event Streams for logical grouping
        Schema::create('event_streams', function (Blueprint $table): void {
            $table->id();
            $table->string('stream_name', 255)->unique()->index();
            $table->string('stream_type', 100)->index();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at');
            $table->timestamp('last_event_at')->nullable();
            $table->integer('version')->default(0);
        });

        // Event projections for read models
        Schema::create('event_projections', function (Blueprint $table): void {
            $table->id();
            $table->string('projection_name', 255)->unique();
            $table->string('last_processed_event_id')->nullable();
            $table->integer('position')->default(0);
            $table->json('state')->nullable();
            $table->timestamp('last_updated_at')->nullable();
            $table->boolean('is_locked')->default(FALSE);
            $table->string('locked_by')->nullable();
            $table->timestamp('locked_at')->nullable();
        });

        // Event Snapshots for performance
        Schema::create('event_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->string('aggregate_root_id', 255)->index();
            $table->string('aggregate_type', 100)->index();
            $table->integer('aggregate_version')->index();
            $table->json('aggregate_data');
            $table->timestamp('created_at');

            $table->unique(['aggregate_root_id', 'aggregate_type', 'aggregate_version']);
        });

        // Event subscriptions for read models and handlers
        Schema::create('event_subscriptions', function (Blueprint $table): void {
            $table->id();
            $table->string('subscription_name', 255)->unique();
            $table->string('handler_class', 255);
            $table->json('event_types');
            $table->string('last_processed_event_id')->nullable();
            $table->integer('position')->default(0);
            $table->boolean('is_active')->default(TRUE);
            $table->boolean('is_catch_up')->default(FALSE);
            $table->json('configuration')->nullable();
            $table->timestamp('created_at');
            $table->timestamp('last_processed_at')->nullable();
        });

        // Event processing failures for debugging
        Schema::create('event_processing_failures', function (Blueprint $table): void {
            $table->id();
            $table->string('event_id');
            $table->string('subscription_name');
            $table->string('handler_class');
            $table->string('error_type', 100);
            $table->text('error_message');
            $table->json('error_context')->nullable();
            $table->json('event_payload');
            $table->integer('retry_count')->default(0);
            $table->timestamp('failed_at');
            $table->timestamp('retry_after')->nullable();
            $table->boolean('is_resolved')->default(FALSE);
            $table->timestamp('resolved_at')->nullable();

            $table->index(['subscription_name', 'failed_at']);
            $table->index(['is_resolved', 'retry_after']);
        });

        // CQRS Read Models - Ticket aggregates
        Schema::create('ticket_read_models', function (Blueprint $table): void {
            $table->id();
            $table->string('ticket_id')->unique()->index();
            $table->string('platform_source');
            $table->string('event_name');
            $table->string('event_category');
            $table->string('venue');
            $table->datetime('event_date');
            $table->decimal('current_price', 10, 2);
            $table->decimal('original_price', 10, 2)->nullable();
            $table->string('availability_status');
            $table->integer('available_quantity')->nullable();
            $table->json('price_history');
            $table->json('availability_history');
            $table->boolean('is_high_demand')->default(FALSE);
            $table->boolean('is_sold_out')->default(FALSE);
            $table->timestamp('first_discovered_at');
            $table->timestamp('last_updated_at');
            $table->integer('version')->default(1);

            $table->index(['platform_source', 'availability_status']);
            $table->index(['event_category', 'event_date']);
            $table->index(['is_high_demand', 'current_price']);
        });

        // CQRS Read Models - Purchase aggregates
        Schema::create('purchase_read_models', function (Blueprint $table): void {
            $table->id();
            $table->string('purchase_id')->unique()->index();
            $table->string('user_id')->index();
            $table->string('ticket_id')->index();
            $table->string('status');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('GBP');
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable();
            $table->json('purchase_details');
            $table->timestamp('initiated_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->string('failure_reason')->nullable();
            $table->integer('version')->default(1);

            $table->index(['user_id', 'status']);
            $table->index(['status', 'initiated_at']);
        });

        // Event-driven monitoring metrics
        Schema::create('monitoring_read_models', function (Blueprint $table): void {
            $table->id();
            $table->string('monitor_id')->unique()->index();
            $table->string('user_id')->index();
            $table->string('platform')->index();
            $table->string('status');
            $table->json('criteria');
            $table->integer('matches_found')->default(0);
            $table->timestamp('started_at');
            $table->timestamp('stopped_at')->nullable();
            $table->timestamp('last_match_at')->nullable();
            $table->json('performance_metrics');
            $table->integer('version')->default(1);

            $table->index(['user_id', 'status']);
            $table->index(['platform', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monitoring_read_models');
        Schema::dropIfExists('purchase_read_models');
        Schema::dropIfExists('ticket_read_models');
        Schema::dropIfExists('event_processing_failures');
        Schema::dropIfExists('event_subscriptions');
        Schema::dropIfExists('event_snapshots');
        Schema::dropIfExists('event_projections');
        Schema::dropIfExists('event_streams');
        Schema::dropIfExists('event_store');
    }
};
