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
        Schema::create('campaign_emails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('marketing_campaigns')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users');
            $table->string('subject');
            $table->longText('body');
            $table->enum('status', ['pending', 'sent', 'delivered', 'opened', 'clicked', 'failed', 'bounced', 'unsubscribed'])
                ->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->json('tracking_data')->nullable();
            $table->timestamps();

            $table->index(['campaign_id', 'user_id']);
            $table->index(['status']);
            $table->index(['sent_at']);
            $table->index(['opened_at']);
            $table->index(['clicked_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_emails');
    }
};