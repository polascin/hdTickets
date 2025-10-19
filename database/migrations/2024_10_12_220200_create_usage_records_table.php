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
        Schema::create('usage_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('subscription_id')->nullable()->constrained()->onDelete('set null');
            $table->string('resource_type'); // api_requests, events_monitored, price_alerts, etc.
            $table->integer('quantity');
            $table->decimal('unit_price', 8, 4)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->timestamp('billing_period_start');
            $table->timestamp('billing_period_end');
            $table->timestamp('recorded_at');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'resource_type']);
            $table->index(['user_id', 'billing_period_start', 'billing_period_end'], 'usage_records_user_billing_period_idx');
            $table->index('subscription_id');
            $table->index('resource_type');
            $table->index('recorded_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usage_records');
    }
};
