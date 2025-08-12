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
        // Create user_preferences table if it doesn't exist
        if (!Schema::hasTable('user_preferences')) {
            Schema::create('user_preferences', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('key');
                $table->json('value');
                $table->string('type')->default('json');
                $table->string('category')->default('general');
                $table->timestamps();
                
                $table->unique(['user_id', 'key'], 'user_preference_unique');
                $table->index(['user_id', 'category']);
                $table->index(['category']);
            });
        }

        // Enhance user_notification_settings table if it exists
        if (Schema::hasTable('user_notification_settings')) {
            Schema::table('user_notification_settings', function (Blueprint $table) {
                // Add new notification preference fields
                if (!Schema::hasColumn('user_notification_settings', 'quiet_hours_enabled')) {
                    $table->boolean('quiet_hours_enabled')->default(false)->after('is_enabled');
                }
                if (!Schema::hasColumn('user_notification_settings', 'quiet_hours_start')) {
                    $table->time('quiet_hours_start')->default('23:00')->after('quiet_hours_enabled');
                }
                if (!Schema::hasColumn('user_notification_settings', 'quiet_hours_end')) {
                    $table->time('quiet_hours_end')->default('07:00')->after('quiet_hours_start');
                }
                if (!Schema::hasColumn('user_notification_settings', 'frequency')) {
                    $table->enum('frequency', ['immediate', 'hourly', 'daily'])->default('immediate')->after('quiet_hours_end');
                }
                if (!Schema::hasColumn('user_notification_settings', 'preferences')) {
                    $table->json('preferences')->nullable()->after('frequency');
                }
                if (!Schema::hasColumn('user_notification_settings', 'metadata')) {
                    $table->json('metadata')->nullable()->after('preferences');
                }
            });
        } else {
            // Create user_notification_settings table
            Schema::create('user_notification_settings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('channel'); // email, push, sms, slack, discord, telegram, webhook
                $table->boolean('is_enabled')->default(true);
                $table->boolean('quiet_hours_enabled')->default(false);
                $table->time('quiet_hours_start')->default('23:00');
                $table->time('quiet_hours_end')->default('07:00');
                $table->enum('frequency', ['immediate', 'hourly', 'daily'])->default('immediate');
                
                // Channel-specific configuration
                $table->string('webhook_url')->nullable();
                $table->string('slack_user_id')->nullable();
                $table->string('ping_role_id')->nullable();
                $table->string('discord_user_id')->nullable();
                $table->string('chat_id')->nullable();
                
                // Authentication settings
                $table->enum('auth_type', ['none', 'bearer', 'basic', 'api_key'])->default('none');
                $table->text('auth_token')->nullable();
                $table->string('api_key')->nullable();
                $table->string('basic_username')->nullable();
                $table->string('basic_password')->nullable();
                $table->string('webhook_secret')->nullable();
                
                // Advanced settings
                $table->json('custom_headers')->nullable();
                $table->integer('max_retries')->default(3);
                $table->integer('retry_delay')->default(5); // seconds
                $table->json('preferences')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
                
                $table->unique(['user_id', 'channel'], 'user_notification_channel_unique');
                $table->index(['user_id', 'is_enabled']);
                $table->index(['channel', 'is_enabled']);
            });
        }

        // Add new columns to users table for enhanced preferences
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                // Display preferences
                if (!Schema::hasColumn('users', 'theme_preference')) {
                    $table->enum('theme_preference', ['light', 'dark', 'auto'])->default('light')->after('language');
                }
                if (!Schema::hasColumn('users', 'display_density')) {
                    $table->enum('display_density', ['compact', 'comfortable', 'spacious'])->default('comfortable')->after('theme_preference');
                }
                if (!Schema::hasColumn('users', 'sidebar_collapsed')) {
                    $table->boolean('sidebar_collapsed')->default(false)->after('display_density');
                }
                
                // Dashboard preferences
                if (!Schema::hasColumn('users', 'dashboard_auto_refresh')) {
                    $table->boolean('dashboard_auto_refresh')->default(true)->after('sidebar_collapsed');
                }
                if (!Schema::hasColumn('users', 'dashboard_refresh_interval')) {
                    $table->integer('dashboard_refresh_interval')->default(30)->after('dashboard_auto_refresh');
                }
                if (!Schema::hasColumn('users', 'currency_preference')) {
                    $table->string('currency_preference', 3)->default('USD')->after('dashboard_refresh_interval');
                }
                
                // Performance preferences
                if (!Schema::hasColumn('users', 'performance_settings')) {
                    $table->json('performance_settings')->nullable()->after('currency_preference');
                }
            });
        }

        // Create user_display_preferences table for detailed display settings
        if (!Schema::hasTable('user_display_preferences')) {
            Schema::create('user_display_preferences', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                
                // Theme and appearance
                $table->enum('theme', ['light', 'dark', 'auto'])->default('light');
                $table->enum('density', ['compact', 'comfortable', 'spacious'])->default('comfortable');
                $table->boolean('high_contrast')->default(false);
                $table->boolean('animations_enabled')->default(true);
                $table->boolean('tooltips_enabled')->default(true);
                $table->boolean('sidebar_collapsed')->default(false);
                
                // Dashboard layout
                $table->json('widget_layout')->nullable(); // Dashboard widget positions and sizes
                $table->json('widget_settings')->nullable(); // Individual widget preferences
                $table->boolean('compact_mode')->default(false);
                $table->integer('items_per_page')->default(25);
                
                // Table preferences
                $table->json('column_preferences')->nullable(); // Visible/hidden columns per table
                $table->json('sort_preferences')->nullable(); // Default sorting per table
                $table->json('filter_preferences')->nullable(); // Saved filters
                
                // Advanced display options
                $table->boolean('show_price_history')->default(true);
                $table->boolean('show_availability_chart')->default(true);
                $table->boolean('show_trend_indicators')->default(true);
                $table->enum('date_format', ['Y-m-d', 'd/m/Y', 'm/d/Y', 'd-m-Y'])->default('Y-m-d');
                $table->enum('time_format', ['H:i', 'h:i A'])->default('H:i');
                
                $table->timestamps();
                
                $table->unique(['user_id']);
                $table->index(['theme']);
                $table->index(['density']);
            });
        }

        // Create user_alert_preferences table for detailed alert settings
        if (!Schema::hasTable('user_alert_preferences')) {
            Schema::create('user_alert_preferences', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                
                // Alert types
                $table->boolean('price_alerts')->default(true);
                $table->boolean('availability_alerts')->default(true);
                $table->boolean('high_demand_alerts')->default(true);
                $table->boolean('price_drop_alerts')->default(true);
                $table->boolean('new_ticket_alerts')->default(false);
                
                // Thresholds
                $table->decimal('price_drop_threshold', 5, 2)->default(10.00); // Percentage
                $table->decimal('high_demand_threshold', 8, 2)->default(500.00); // Price
                $table->integer('availability_threshold')->default(10); // Number of tickets
                
                // Timing preferences
                $table->enum('alert_frequency', ['immediate', 'every_5min', 'every_15min', 'hourly'])->default('immediate');
                $table->boolean('batch_alerts')->default(false);
                $table->integer('batch_interval')->default(60); // minutes
                
                // Escalation settings
                $table->boolean('escalation_enabled')->default(false);
                $table->integer('escalation_delay')->default(5); // minutes
                $table->json('escalation_channels')->nullable(); // Order of channels to escalate through
                $table->string('emergency_contact_email')->nullable();
                $table->string('emergency_contact_phone')->nullable();
                
                // Content preferences
                $table->boolean('include_charts')->default(true);
                $table->boolean('include_price_history')->default(true);
                $table->boolean('include_recommendations')->default(true);
                $table->enum('alert_detail_level', ['minimal', 'standard', 'detailed'])->default('standard');
                
                $table->timestamps();
                
                $table->unique(['user_id']);
                $table->index(['price_alerts']);
                $table->index(['availability_alerts']);
            });
        }

        // Create user_dashboard_preferences table
        if (!Schema::hasTable('user_dashboard_preferences')) {
            Schema::create('user_dashboard_preferences', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                
                // Auto-refresh settings
                $table->boolean('auto_refresh_enabled')->default(true);
                $table->integer('refresh_interval')->default(30); // seconds
                $table->json('refresh_widgets')->nullable(); // Which widgets to auto-refresh
                
                // Widget preferences
                $table->json('enabled_widgets')->nullable(); // List of enabled widgets
                $table->json('widget_order')->nullable(); // Order of widgets on dashboard
                $table->json('widget_sizes')->nullable(); // Size configuration per widget
                
                // Data preferences
                $table->integer('chart_data_points')->default(30); // Number of data points in charts
                $table->enum('chart_type_preference', ['line', 'bar', 'area'])->default('line');
                $table->boolean('show_trends')->default(true);
                $table->boolean('show_predictions')->default(true);
                
                // Quick filters
                $table->json('saved_filters')->nullable(); // User's saved filter combinations
                $table->json('default_filters')->nullable(); // Default filters on dashboard load
                $table->string('default_view')->default('overview'); // Default dashboard view
                
                $table->timestamps();
                
                $table->unique(['user_id']);
                $table->index(['auto_refresh_enabled']);
            });
        }

        // Create indexes for better performance only if columns exist
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                // Add composite indexes for common queries only if columns exist
                if (Schema::hasColumn('users', 'timezone') && Schema::hasColumn('users', 'language')) {
                    try {
                        $table->index(['timezone', 'language'], 'users_timezone_language_index');
                    } catch (Exception $e) {
                        // Index likely already exists, ignore
                    }
                }
                if (Schema::hasColumn('users', 'theme_preference') && Schema::hasColumn('users', 'display_density')) {
                    try {
                        $table->index(['theme_preference', 'display_density'], 'users_theme_density_index');
                    } catch (Exception $e) {
                        // Index likely already exists, ignore
                    }
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop new tables in reverse order
        Schema::dropIfExists('user_dashboard_preferences');
        Schema::dropIfExists('user_alert_preferences');
        Schema::dropIfExists('user_display_preferences');
        
        // Remove added columns from users table
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $columns = [
                    'performance_settings', 'currency_preference', 'dashboard_refresh_interval',
                    'dashboard_auto_refresh', 'sidebar_collapsed', 'display_density', 'theme_preference'
                ];
                
                foreach ($columns as $column) {
                    if (Schema::hasColumn('users', $column)) {
                        $table->dropColumn($column);
                    }
                }
                
                // Drop custom indexes - use try/catch as Laravel 12 doesn't have reliable index checking
                try {
                    $table->dropIndex('users_timezone_language_index');
                } catch (Exception $e) {
                    // Index likely doesn't exist, ignore
                }
                try {
                    $table->dropIndex('users_theme_density_index');
                } catch (Exception $e) {
                    // Index likely doesn't exist, ignore
                }
            });
        }

        // Remove added columns from user_notification_settings if it exists
        if (Schema::hasTable('user_notification_settings')) {
            Schema::table('user_notification_settings', function (Blueprint $table) {
                $columns = [
                    'metadata', 'preferences', 'frequency', 'quiet_hours_end', 
                    'quiet_hours_start', 'quiet_hours_enabled'
                ];
                
                foreach ($columns as $column) {
                    if (Schema::hasColumn('user_notification_settings', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        // Drop user_preferences table only if we created it in this migration
        // We'll keep it since it might have been created elsewhere
        // Schema::dropIfExists('user_preferences');
    }

};
