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
        Schema::create('alert_escalations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alert_id')->constrained('ticket_alerts')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->tinyInteger('priority')->default(2);
            $table->string('strategy', 50);
            $table->timestamp('scheduled_at');
            $table->integer('attempts')->default(0);
            $table->integer('max_attempts')->default(3);
            $table->enum('status', ['scheduled', 'retrying', 'completed', 'failed', 'cancelled'])->default('scheduled');
            $table->json('alert_data');
            $table->json('escalation_config');
            $table->timestamp('last_attempted_at')->nullable();
            $table->timestamp('next_retry_at')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['status', 'scheduled_at']);
            $table->index(['user_id', 'status']);
            $table->index(['alert_id', 'status']);
            $table->index('next_retry_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alert_escalations');
    }
};
