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
        Schema::create('payment_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 8, 2);
            $table->enum('billing_cycle', ['monthly', 'yearly'])->default('monthly');
            $table->json('features')->nullable();
            $table->integer('max_tickets_per_month')->default(0);
            $table->integer('max_concurrent_purchases')->default(1);
            $table->integer('max_platforms')->default(1);
            $table->boolean('priority_support')->default(FALSE);
            $table->boolean('advanced_analytics')->default(FALSE);
            $table->boolean('automated_purchasing')->default(FALSE);
            $table->boolean('is_active')->default(TRUE);
            $table->integer('sort_order')->default(0);
            $table->string('stripe_price_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_plans');
    }
};
