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
        // Real-time analytics cache table
        if (! Schema::hasTable('analytics_cache')) {
            Schema::create('analytics_cache', function (Blueprint $table): void {
                $table->id();
                $table->string('cache_key')->unique();
                $table->string('analytics_type'); // price_trends, demand_patterns, etc.
                $table->json('data');
                $table->json('filters_applied')->nullable();
                $table->timestamp('generated_at');
                $table->timestamp('expires_at');
                $table->boolean('is_real_time')->default(FALSE);
                $table->integer('access_count')->default(0);
                $table->timestamp('last_accessed_at')->nullable();
                $table->timestamps();

                $table->index(['analytics_type', 'expires_at']);
                $table->index(['cache_key']);
                $table->index(['generated_at']);
                $table->index(['is_real_time']);
            });
        }

        // User dashboard configurations
        if (! Schema::hasTable('user_dashboard_configs')) {
            Schema::create('user_dashboard_configs', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('dashboard_name');
                $table->json('widget_configuration'); // Layout, enabled widgets, etc.
                $table->json('default_filters')->nullable();
                $table->boolean('is_default')->default(FALSE);
                $table->boolean('is_shared')->default(FALSE);
                $table->json('access_permissions')->nullable(); // Who can view shared dashboards
                $table->integer('usage_count')->default(0);
                $table->timestamp('last_used_at')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'is_default']);
                $table->index(['is_shared']);
            });
        }

        // Analytics insights and recommendations
        if (! Schema::hasTable('analytics_insights')) {
            Schema::create('analytics_insights', function (Blueprint $table): void {
                $table->id();
                $table->uuid('insight_uuid')->unique();
                $table->string('insight_type'); // predictive, behavioral, market, optimization
                $table->string('category'); // price, demand, user_behavior, platform_performance
                $table->string('title');
                $table->text('description');
                $table->json('data_points'); // Supporting data
                $table->enum('priority', ['low', 'medium', 'high', 'critical']);
                $table->enum('status', ['active', 'acknowledged', 'resolved', 'dismissed']);
                $table->decimal('confidence_score', 5, 4)->nullable(); // ML confidence
                $table->json('recommended_actions')->nullable();
                $table->timestamp('valid_until')->nullable();
                $table->foreignId('generated_by_user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();

                $table->index(['insight_type', 'status']);
                $table->index(['category', 'priority']);
                $table->index(['valid_until']);
                $table->index(['confidence_score']);
            });
        }

        // Real-time market data tracking
        if (! Schema::hasTable('market_data_snapshots')) {
            Schema::create('market_data_snapshots', function (Blueprint $table): void {
                $table->id();
                $table->timestamp('snapshot_time');
                $table->json('platform_metrics'); // Tickets count, average prices per platform
                $table->json('category_metrics'); // Sports categories performance
                $table->json('demand_indicators'); // Search volume, alert creation rate
                $table->json('price_indicators'); // Average prices, volatility indices
                $table->json('user_activity_metrics'); // Active users, engagement rates
                $table->decimal('market_health_score', 5, 2)->default(100.00);
                $table->json('anomalies_detected')->nullable();
                $table->timestamps();

                $table->index(['snapshot_time']);
                $table->index(['market_health_score']);
            });
        }

        // Predictive model performance tracking
        if (! Schema::hasTable('prediction_model_metrics')) {
            Schema::create('prediction_model_metrics', function (Blueprint $table): void {
                $table->id();
                $table->string('model_name');
                $table->string('model_version');
                $table->string('prediction_type'); // price, demand, availability
                $table->json('performance_metrics'); // Accuracy, precision, recall, F1-score
                $table->json('training_data_info');
                $table->timestamp('last_trained_at');
                $table->timestamp('next_training_at')->nullable();
                $table->boolean('is_active')->default(TRUE);
                $table->integer('predictions_made')->default(0);
                $table->decimal('average_accuracy', 5, 4)->nullable();
                $table->json('feature_importance')->nullable();
                $table->timestamps();

                $table->unique(['model_name', 'model_version']);
                $table->index(['prediction_type', 'is_active']);
                $table->index(['average_accuracy']);
            });
        }

        // Custom analytics queries and reports
        if (! Schema::hasTable('custom_analytics_queries')) {
            Schema::create('custom_analytics_queries', function (Blueprint $table): void {
                $table->id();
                $table->uuid('query_uuid')->unique();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('query_name');
                $table->text('description')->nullable();
                $table->json('query_configuration'); // Filters, groupings, calculations
                $table->json('visualization_config'); // Chart type, styling, etc.
                $table->enum('execution_frequency', ['manual', 'hourly', 'daily', 'weekly', 'monthly']);
                $table->timestamp('last_executed_at')->nullable();
                $table->timestamp('next_execution_at')->nullable();
                $table->boolean('is_active')->default(TRUE);
                $table->boolean('is_shared')->default(FALSE);
                $table->integer('execution_count')->default(0);
                $table->json('performance_stats')->nullable(); // Execution time, data size
                $table->timestamps();

                $table->index(['user_id', 'is_active']);
                $table->index(['execution_frequency', 'next_execution_at'], 'custom_queries_execution_idx');
                $table->index(['is_shared']);
            });
        }

        // A/B testing for analytics features
        if (! Schema::hasTable('analytics_ab_tests')) {
            Schema::create('analytics_ab_tests', function (Blueprint $table): void {
                $table->id();
                $table->string('test_name');
                $table->string('feature_name'); // Which analytics feature is being tested
                $table->json('variant_configurations'); // Different versions being tested
                $table->decimal('traffic_split', 5, 4)->default(0.5000); // 50/50 split
                $table->timestamp('started_at');
                $table->timestamp('ends_at')->nullable();
                $table->enum('status', ['draft', 'running', 'paused', 'completed', 'cancelled']);
                $table->json('success_metrics'); // What metrics define success
                $table->json('current_results')->nullable();
                $table->boolean('auto_promote_winner')->default(FALSE);
                $table->timestamps();

                $table->index(['feature_name', 'status']);
                $table->index(['started_at', 'ends_at']);
            });
        }

        // Analytics user interactions tracking
        if (! Schema::hasTable('analytics_user_interactions')) {
            Schema::create('analytics_user_interactions', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('interaction_type'); // view, click, export, filter, etc.
                $table->string('dashboard_section'); // Which part of analytics was used
                $table->json('interaction_details'); // Specific actions, filters used, etc.
                $table->timestamp('interaction_time');
                $table->string('session_id')->nullable();
                $table->json('user_context')->nullable(); // Device, location, etc.
                $table->decimal('time_spent_seconds', 8, 2)->nullable();
                $table->timestamps();

                $table->index(['user_id', 'interaction_time']);
                $table->index(['interaction_type']);
                $table->index(['dashboard_section']);
                $table->index(['session_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics_user_interactions');
        Schema::dropIfExists('analytics_ab_tests');
        Schema::dropIfExists('custom_analytics_queries');
        Schema::dropIfExists('prediction_model_metrics');
        Schema::dropIfExists('market_data_snapshots');
        Schema::dropIfExists('analytics_insights');
        Schema::dropIfExists('user_dashboard_configs');
        Schema::dropIfExists('analytics_cache');
    }
};
