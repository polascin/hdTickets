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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('current_subscription_id')->nullable()->constrained('user_subscriptions')->onDelete('set null');
            $table->boolean('has_trial_used')->default(false);
            $table->json('billing_address')->nullable();
            $table->string('stripe_customer_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['current_subscription_id']);
            $table->dropColumn(['current_subscription_id', 'has_trial_used', 'billing_address', 'stripe_customer_id']);
        });
    }
};
