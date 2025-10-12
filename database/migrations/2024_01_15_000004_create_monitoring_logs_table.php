<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monitoring_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_monitor_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['success', 'failed', 'timeout', 'error'])->default('success');
            $table->decimal('response_time', 8, 2)->nullable();
            $table->text('error_message')->nullable();
            $table->string('platform')->nullable();
            $table->json('data_found')->nullable();
            $table->timestamp('checked_at');
            $table->integer('downtime_duration')->default(0); // minutes
            $table->timestamps();

            $table->index(['event_monitor_id', 'checked_at']);
            $table->index(['status']);
            $table->index(['platform']);
            $table->index(['checked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monitoring_logs');
    }
};