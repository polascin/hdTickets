<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_attempts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('purchase_queue_id')->constrained()->onDelete('cascade');
            $table->foreignId('scraped_ticket_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['pending', 'in_progress', 'success', 'failed', 'cancelled'])->default('pending');
            $table->string('platform'); // ticketmaster, stubhub, etc.
            $table->decimal('attempted_price', 10, 2)->nullable();
            $table->integer('attempted_quantity')->default(1);
            $table->string('transaction_id')->nullable();
            $table->string('confirmation_number')->nullable();
            $table->decimal('final_price', 10, 2)->nullable();
            $table->decimal('fees', 10, 2)->nullable();
            $table->decimal('total_paid', 10, 2)->nullable();
            $table->json('purchase_details')->nullable(); // Store payment method, seat details, etc.
            $table->text('error_message')->nullable();
            $table->text('failure_reason')->nullable();
            $table->json('response_data')->nullable(); // Raw response from platform
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamp('next_retry_at')->nullable();
            $table->timestamps();
            
            $table->index(['purchase_queue_id', 'status']);
            $table->index(['platform', 'status']);
            $table->index(['transaction_id']);
            $table->index(['confirmation_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_attempts');
    }
};
