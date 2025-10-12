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
        Schema::create('campaign_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('marketing_campaigns')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users');
            $table->enum('action', ['sent', 'delivered', 'opened', 'clicked', 'converted', 'unsubscribed', 'bounced', 'failed']);
            $table->timestamp('timestamp');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['campaign_id', 'action']);
            $table->index(['user_id', 'action']);
            $table->index(['timestamp']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_interactions');
    }
};