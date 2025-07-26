<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_queues', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('scraped_ticket_id')->constrained()->onDelete('cascade');
            $table->foreignId('selected_by_user_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['queued', 'processing', 'completed', 'failed', 'cancelled'])->default('queued');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent', 'critical'])->default('medium');
            $table->decimal('max_price', 10, 2)->nullable();
            $table->integer('quantity')->default(1);
            $table->json('purchase_criteria')->nullable(); // Additional criteria for purchasing
            $table->text('notes')->nullable();
            $table->timestamp('scheduled_for')->nullable(); // For delayed purchases
            $table->timestamp('expires_at')->nullable(); // Queue expiration
            $table->timestamp('started_processing_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'priority', 'scheduled_for']);
            $table->index(['scraped_ticket_id', 'status']);
            $table->index(['selected_by_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_queues');
    }
};
