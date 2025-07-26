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
        Schema::create('user_notification_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('channel', ['slack', 'discord', 'telegram', 'webhook']);
            $table->boolean('is_enabled')->default(true);
            
            // Channel-specific settings
            $table->string('webhook_url')->nullable();
            $table->string('channel_name')->nullable(); // For Slack channel name
            $table->string('slack_user_id')->nullable();
            $table->string('ping_role_id')->nullable(); // For Discord/Slack role mentions
            $table->string('discord_user_id')->nullable();
            $table->string('chat_id')->nullable(); // For Telegram
            
            // Authentication settings
            $table->enum('auth_type', ['none', 'bearer', 'api_key', 'basic'])->default('none');
            $table->text('auth_token')->nullable();
            $table->string('api_key')->nullable();
            $table->string('basic_username')->nullable();
            $table->string('basic_password')->nullable();
            $table->string('webhook_secret')->nullable();
            
            // Configuration
            $table->json('custom_headers')->nullable();
            $table->integer('max_retries')->default(3);
            $table->integer('retry_delay')->default(1); // seconds
            $table->json('settings')->nullable(); // Additional channel-specific settings
            
            $table->timestamps();

            // Unique constraint to prevent duplicate channel settings per user
            $table->unique(['user_id', 'channel']);
            
            // Indexes
            $table->index(['user_id', 'is_enabled']);
            $table->index('channel');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_notification_settings');
    }
};
