<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create Ticket Alerts Table
 * 
 * Creates the comprehensive ticket monitoring and alerts system database structure
 * for HD Tickets sports events monitoring platform.
 * 
 * This migration creates tables for:
 * - ticket_alerts: Main alerts configuration and status
 * - alert_history: Historical price and availability tracking
 * - alert_notifications: Notification delivery tracking
 * 
 * @version 1.0.0
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Main ticket alerts table
        Schema::create('ticket_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('event_identifier', 100)->index(); // Reference to event/ticket
            $table->enum('alert_type', ['price_drop', 'availability'])->default('price_drop');
            $table->decimal('target_price', 10, 2)->nullable(); // Target price for price_drop alerts
            $table->string('section_preferences')->nullable(); // Preferred sections/seat types
            $table->json('notification_methods')->nullable(); // ['email', 'sms', 'browser']
            $table->enum('status', ['active', 'paused', 'triggered', 'expired', 'deleted'])->default('active');
            $table->string('duration_type', 50)->default('1_week'); // 1_day, 3_days, 1_week, 1_month, until_event
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamp('last_triggered_at')->nullable();
            $table->decimal('last_known_price', 10, 2)->nullable();
            $table->string('last_availability_status', 50)->nullable();
            $table->integer('trigger_count')->default(0);
            $table->text('notes')->nullable(); // User notes or system messages
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['user_id', 'status']);
            $table->index(['event_identifier', 'alert_type']);
            $table->index(['status', 'expires_at']);
            $table->index('last_checked_at');
        });

        // Alert history table for tracking price and availability changes
        Schema::create('alert_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alert_id')->constrained('ticket_alerts')->onDelete('cascade');
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('previous_price', 10, 2)->nullable();
            $table->string('availability_status', 50)->nullable(); // high, medium, low, sold_out
            $table->string('platform_source', 100)->nullable(); // StubHub, Ticketmaster, etc.
            $table->boolean('triggered_alert')->default(false);
            $table->text('change_details')->nullable(); // JSON with detailed change info
            $table->string('status', 50)->default('checked'); // checked, triggered, error
            $table->text('message')->nullable(); // Status message or error details
            $table->timestamp('checked_at');
            $table->timestamps();
            
            // Indexes
            $table->index(['alert_id', 'checked_at']);
            $table->index(['triggered_alert', 'checked_at']);
        });

        // Alert notifications tracking table
        Schema::create('alert_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alert_id')->constrained('ticket_alerts')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('notification_type', ['email', 'sms', 'browser', 'webhook'])->default('email');
            $table->enum('trigger_reason', ['price_drop', 'availability_change', 'alert_expired', 'manual'])->default('price_drop');
            $table->enum('status', ['pending', 'sent', 'delivered', 'failed', 'bounced'])->default('pending');
            $table->string('recipient')->nullable(); // Email address, phone number, etc.
            $table->text('content')->nullable(); // Notification content/message
            $table->json('metadata')->nullable(); // Additional delivery metadata
            $table->text('error_message')->nullable(); // Error details if failed
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamp('next_retry_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['alert_id', 'notification_type']);
            $table->index(['user_id', 'status']);
            $table->index(['status', 'next_retry_at']);
        });

        // Alert statistics summary table (for analytics and reporting)
        Schema::create('alert_statistics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alert_id')->constrained('ticket_alerts')->onDelete('cascade');
            $table->date('date');
            $table->integer('checks_count')->default(0);
            $table->integer('triggers_count')->default(0);
            $table->decimal('min_price', 10, 2)->nullable();
            $table->decimal('max_price', 10, 2)->nullable();
            $table->decimal('avg_price', 10, 2)->nullable();
            $table->decimal('price_variance', 10, 2)->nullable();
            $table->json('availability_changes')->nullable(); // Track availability transitions
            $table->integer('notifications_sent')->default(0);
            $table->timestamps();
            
            // Unique constraint and indexes
            $table->unique(['alert_id', 'date']);
            $table->index('date');
        });

        // User alert preferences table
        Schema::create('user_alert_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->json('notification_methods')->default('["email"]'); // Default notification methods
            $table->time('quiet_hours_start')->nullable(); // No notifications during quiet hours
            $table->time('quiet_hours_end')->nullable();
            $table->json('quiet_days')->nullable(); // Days of week for quiet periods [0,6] = Sunday,Saturday
            $table->string('timezone', 50)->default('UTC');
            $table->boolean('email_notifications')->default(true);
            $table->boolean('sms_notifications')->default(false);
            $table->boolean('browser_notifications')->default(true);
            $table->boolean('daily_summary')->default(true);
            $table->boolean('weekly_report')->default(false);
            $table->integer('max_daily_notifications')->default(50); // Rate limiting
            $table->integer('price_drop_threshold_percent')->default(10); // Minimum % drop to trigger
            $table->decimal('price_drop_threshold_amount', 8, 2)->default(20.00); // Minimum $ drop to trigger
            $table->json('favorite_sports')->nullable(); // Preferred sports for priority alerts
            $table->json('favorite_venues')->nullable(); // Preferred venues for priority alerts
            $table->json('blocked_platforms')->nullable(); // Platforms to exclude from alerts
            $table->timestamps();
            
            // Unique constraint
            $table->unique('user_id');
        });

        // Platform monitoring configuration
        Schema::create('monitoring_platforms', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique(); // StubHub, Ticketmaster, SeatGeek, etc.
            $table->string('identifier', 50)->unique(); // Internal platform identifier
            $table->boolean('is_active')->default(true);
            $table->integer('check_interval_minutes')->default(15); // How often to check this platform
            $table->integer('rate_limit_per_minute')->default(10); // API rate limits
            $table->json('supported_sports')->nullable(); // Sports available on this platform
            $table->json('supported_regions')->nullable(); // Geographic regions covered
            $table->decimal('reliability_score', 3, 2)->default(100.00); // Platform reliability %
            $table->timestamp('last_successful_check')->nullable();
            $table->timestamp('last_failed_check')->nullable();
            $table->integer('consecutive_failures')->default(0);
            $table->text('failure_reason')->nullable();
            $table->json('configuration')->nullable(); // Platform-specific config
            $table->timestamps();
        });

        // Global monitoring settings
        Schema::create('monitoring_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value');
            $table->string('type', 50)->default('string'); // string, integer, boolean, json
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false); // Can be displayed to users
            $table->timestamps();
        });

        // Insert default monitoring settings
        DB::table('monitoring_settings')->insert([
            [
                'key' => 'default_check_interval',
                'value' => '15',
                'type' => 'integer',
                'description' => 'Default check interval in minutes for new alerts',
                'is_public' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'max_alerts_per_user',
                'value' => '50',
                'type' => 'integer',
                'description' => 'Maximum alerts per user (premium users)',
                'is_public' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'free_alerts_limit',
                'value' => '3',
                'type' => 'integer',
                'description' => 'Maximum alerts for free users',
                'is_public' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'price_drop_threshold',
                'value' => '5.00',
                'type' => 'decimal',
                'description' => 'Minimum price drop amount to trigger alerts',
                'is_public' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'notification_rate_limit',
                'value' => '100',
                'type' => 'integer',
                'description' => 'Maximum notifications per user per day',
                'is_public' => false,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);

        // Insert default platform configurations
        DB::table('monitoring_platforms')->insert([
            [
                'name' => 'StubHub',
                'identifier' => 'stubhub',
                'is_active' => true,
                'check_interval_minutes' => 10,
                'rate_limit_per_minute' => 30,
                'supported_sports' => json_encode(['NFL', 'NBA', 'MLB', 'NHL', 'Soccer', 'Tennis']),
                'supported_regions' => json_encode(['US', 'CA', 'UK']),
                'reliability_score' => 95.5,
                'configuration' => json_encode(['api_version' => 'v3', 'requires_auth' => true]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Ticketmaster',
                'identifier' => 'ticketmaster',
                'is_active' => true,
                'check_interval_minutes' => 15,
                'rate_limit_per_minute' => 20,
                'supported_sports' => json_encode(['NFL', 'NBA', 'MLB', 'NHL', 'Soccer', 'Tennis', 'MLS']),
                'supported_regions' => json_encode(['US', 'CA', 'MX']),
                'reliability_score' => 92.3,
                'configuration' => json_encode(['api_version' => 'discovery', 'requires_auth' => true]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'SeatGeek',
                'identifier' => 'seatgeek',
                'is_active' => true,
                'check_interval_minutes' => 12,
                'rate_limit_per_minute' => 25,
                'supported_sports' => json_encode(['NFL', 'NBA', 'MLB', 'NHL', 'MLS', 'Tennis']),
                'supported_regions' => json_encode(['US', 'CA']),
                'reliability_score' => 88.7,
                'configuration' => json_encode(['api_version' => 'v2', 'requires_auth' => true]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Vivid Seats',
                'identifier' => 'vivid_seats',
                'is_active' => true,
                'check_interval_minutes' => 20,
                'rate_limit_per_minute' => 15,
                'supported_sports' => json_encode(['NFL', 'NBA', 'MLB', 'NHL']),
                'supported_regions' => json_encode(['US']),
                'reliability_score' => 85.2,
                'configuration' => json_encode(['scraping_based' => true, 'requires_proxy' => true]),
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monitoring_settings');
        Schema::dropIfExists('monitoring_platforms');
        Schema::dropIfExists('user_alert_preferences');
        Schema::dropIfExists('alert_statistics');
        Schema::dropIfExists('alert_notifications');
        Schema::dropIfExists('alert_history');
        Schema::dropIfExists('ticket_alerts');
    }
};
