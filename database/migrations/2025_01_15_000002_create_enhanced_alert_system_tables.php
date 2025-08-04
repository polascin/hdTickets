<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Multi-channel notification preferences table
        if (!Schema::hasTable('user_notification_channels')) {
            Schema::create('user_notification_channels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('channel_type'); // email, sms, push, slack, discord, telegram, webhook
            $table->json('configuration'); // Channel-specific settings
            $table->boolean('is_active')->default(true);
            $table->enum('priority_level', ['low', 'medium', 'high', 'critical']);
            $table->json('delivery_schedule')->nullable(); // Time-based delivery preferences
            $table->integer('delivery_success_count')->default(0);
            $table->integer('delivery_failure_count')->default(0);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'channel_type', 'priority_level'], 'user_channel_priority_unique');
            $table->index(['user_id', 'is_active']);
            $table->index(['channel_type']);
            });
        }

        // Alert escalation rules table
        if (!Schema::hasTable('alert_escalation_rules')) {
            Schema::create('alert_escalation_rules', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('rule_name');
            $table->json('trigger_conditions'); // When to escalate
            $table->json('escalation_steps'); // What channels to use and when
            $table->integer('max_escalation_level')->default(3);
            $table->boolean('is_active')->default(true);
            $table->integer('times_triggered')->default(0);
            $table->timestamp('last_triggered_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'is_active']);
            });
        }

        // Real-time alert delivery tracking
        if (!Schema::hasTable('alert_delivery_logs')) {
            Schema::create('alert_delivery_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('alert_uuid');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('ticket_alert_id')->nullable()->constrained()->onDelete('set null');
            $table->string('channel_type');
            $table->enum('delivery_status', ['pending', 'sent', 'delivered', 'failed', 'bounced']);
            $table->json('delivery_details')->nullable(); // Response data, error messages, etc.
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->json('user_interaction')->nullable(); // Click, view, dismiss, etc.
            $table->timestamps();
            
            $table->index(['alert_uuid']);
            $table->index(['user_id', 'delivery_status']);
            $table->index(['channel_type', 'delivery_status']);
            $table->index(['sent_at']);
            });
        }

        // Smart alert learning data
        if (!Schema::hasTable('alert_learning_data')) {
            Schema::create('alert_learning_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('ticket_alert_id')->constrained()->onDelete('cascade');
            $table->json('user_behavior_patterns'); // Click rates, response times, etc.
            $table->json('optimal_timing_data'); // Best times to send alerts
            $table->json('channel_effectiveness'); // Success rates per channel
            $table->decimal('engagement_score', 5, 4)->default(0.5000);
            $table->json('prediction_accuracy')->nullable(); // ML model accuracy tracking
            $table->timestamp('last_updated_at');
            $table->timestamps();
            
            $table->unique(['user_id', 'ticket_alert_id']);
            $table->index(['engagement_score']);
            $table->index(['last_updated_at']);
            });
        }

        // Real-time system status monitoring
        if (!Schema::hasTable('system_health_metrics')) {
            Schema::create('system_health_metrics', function (Blueprint $table) {
            $table->id();
            $table->string('service_name'); // alerts, scraping, notifications, etc.
            $table->enum('status', ['healthy', 'warning', 'critical', 'down']);
            $table->json('metrics_data'); // Response times, error rates, etc.
            $table->decimal('uptime_percentage', 5, 2)->default(100.00);
            $table->integer('error_count_last_hour')->default(0);
            $table->decimal('average_response_time', 8, 2)->nullable(); // milliseconds
            $table->timestamp('last_check_at');
            $table->json('alert_thresholds'); // When to trigger system alerts
            $table->timestamps();
            
            $table->index(['service_name', 'status']);
            $table->index(['last_check_at']);
            $table->index(['status']);
            });
        }

        // Enhanced ticket alerts table modifications
        if (Schema::hasTable('ticket_alerts')) {
            Schema::table('ticket_alerts', function (Blueprint $table) {
                if (!Schema::hasColumn('ticket_alerts', 'priority_score')) {
                    $table->integer('priority_score')->default(50)->after('status');
                }
                if (!Schema::hasColumn('ticket_alerts', 'ml_prediction_data')) {
                    $table->json('ml_prediction_data')->nullable()->after('priority_score');
                }
                if (!Schema::hasColumn('ticket_alerts', 'escalation_level')) {
                    $table->integer('escalation_level')->default(0)->after('ml_prediction_data');
                }
                if (!Schema::hasColumn('ticket_alerts', 'last_escalated_at')) {
                    $table->timestamp('last_escalated_at')->nullable()->after('escalation_level');
                }
                if (!Schema::hasColumn('ticket_alerts', 'success_rate')) {
                    $table->decimal('success_rate', 5, 4)->default(0.5000)->after('last_escalated_at');
                }
                if (!Schema::hasColumn('ticket_alerts', 'channel_preferences')) {
                    $table->json('channel_preferences')->nullable()->after('success_rate');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove added columns from ticket_alerts
        if (Schema::hasTable('ticket_alerts')) {
            Schema::table('ticket_alerts', function (Blueprint $table) {
                $columns = ['channel_preferences', 'success_rate', 'last_escalated_at', 'escalation_level', 'ml_prediction_data', 'priority_score'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('ticket_alerts', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        Schema::dropIfExists('system_health_metrics');
        Schema::dropIfExists('alert_learning_data');
        Schema::dropIfExists('alert_delivery_logs');
        Schema::dropIfExists('alert_escalation_rules');
        Schema::dropIfExists('user_notification_channels');
    }
};
