<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     *
     * Smart Alerts table for TicketScoutie-inspired intelligent alert system
     */
    public function up(): void
    {
        Schema::create('smart_alerts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('alert_type', [
                'price_drop',
                'availability',
                'instant_deal',
                'price_comparison',
                'venue_alert',
                'league_alert',
                'keyword_alert',
            ]);
            $table->json('trigger_conditions'); // Flexible conditions based on alert type
            $table->json('notification_channels'); // email, sms, push, webhook
            $table->json('notification_settings')->nullable(); // Channel-specific settings
            $table->boolean('is_active')->default(TRUE);
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->integer('cooldown_minutes')->default(30); // Minimum time between triggers
            $table->integer('max_triggers_per_day')->default(10); // Daily trigger limit
            $table->integer('trigger_count')->default(0); // Total times triggered
            $table->timestamp('last_triggered_at')->nullable(); // Last trigger timestamp
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['user_id', 'is_active']);
            $table->index(['alert_type', 'is_active']);
            $table->index(['priority', 'is_active']);
            $table->index('last_triggered_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('smart_alerts');
    }
};
