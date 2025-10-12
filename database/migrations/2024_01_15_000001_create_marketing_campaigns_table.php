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
        Schema::create('marketing_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['email', 'push', 'in_app', 'sms']);
            $table->enum('status', ['draft', 'scheduled', 'active', 'completed', 'paused', 'cancelled'])
                ->default('draft');
            $table->string('target_audience')->default('all');
            $table->enum('schedule_type', ['immediate', 'scheduled', 'recurring'])
                ->default('immediate');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('launched_at')->nullable();
            $table->json('content');
            $table->json('settings')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index(['status', 'type']);
            $table->index(['scheduled_at']);
            $table->index(['created_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketing_campaigns');
    }
};