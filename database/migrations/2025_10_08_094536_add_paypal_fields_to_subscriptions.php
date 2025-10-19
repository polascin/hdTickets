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
        if (Schema::hasTable('user_subscriptions')) {
            Schema::table('user_subscriptions', function (Blueprint $table) {
                // PayPal subscription fields
                $table->string('paypal_subscription_id')->nullable()->after('stripe_customer_id');
                $table->string('paypal_payer_id')->nullable()->after('paypal_subscription_id');
                $table->string('paypal_plan_id')->nullable()->after('paypal_payer_id');
                $table->timestamp('next_billing_at')->nullable()->after('paypal_plan_id');

                // Add indexes for PayPal fields
                $table->index(['paypal_subscription_id'], 'idx_paypal_subscription_id');
            });
        }

        if (Schema::hasTable('purchase_attempts')) {
            Schema::table('purchase_attempts', function (Blueprint $table) {
                // PayPal order fields
                $table->string('paypal_order_id')->nullable()->after('transaction_id');
                $table->string('paypal_capture_id')->nullable()->after('paypal_order_id');

                // Add indexes for PayPal fields
                $table->index(['paypal_order_id'], 'idx_paypal_order_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('user_subscriptions')) {
            Schema::table('user_subscriptions', function (Blueprint $table) {
                // Drop PayPal subscription fields
                $table->dropIndex('idx_paypal_subscription_id');
                $table->dropColumn([
                    'paypal_subscription_id',
                    'paypal_payer_id',
                    'paypal_plan_id',
                    'next_billing_at',
                ]);
            });
        }

        if (Schema::hasTable('purchase_attempts')) {
            Schema::table('purchase_attempts', function (Blueprint $table) {
                // Drop PayPal order fields
                $table->dropIndex('idx_paypal_order_id');
                $table->dropColumn([
                    'paypal_order_id',
                    'paypal_capture_id',
                ]);
            });
        }
    }
};
