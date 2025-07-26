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
        Schema::create('ticket_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('sports_event_id')->constrained()->onDelete('cascade');
            $table->string('alert_name');
            $table->decimal('max_price', 10, 2)->nullable();
            $table->decimal('min_price', 10, 2)->nullable();
            $table->integer('min_quantity')->default(1);
            $table->json('preferred_sections')->nullable();
            $table->json('platforms')->nullable(); // Ticketmaster, StubHub, etc.
            $table->enum('status', ['active', 'paused', 'triggered', 'expired'])->default('active');
            $table->boolean('email_notifications')->default(true);
            $table->boolean('sms_notifications')->default(false);
            $table->boolean('auto_purchase')->default(false);
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamp('triggered_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_alerts');
    }
};
