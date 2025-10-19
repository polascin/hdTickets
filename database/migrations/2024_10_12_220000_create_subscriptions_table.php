<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('plan_name'); // starter, pro, enterprise
            $table->string('stripe_subscription_id')->nullable()->unique();
            $table->string('stripe_subscription_item_id')->nullable();
            $table->string('status')->default('active'); // active, trialing, past_due, cancelled, etc.
            $table->decimal('price', 8, 2);
            $table->string('currency', 3)->default('usd');
            $table->timestamp('current_period_start')->nullable();
            $table->timestamp('current_period_end')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('cancel_at')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->timestamp('last_payment_failed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('stripe_subscription_id');
            $table->index('current_period_end');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
