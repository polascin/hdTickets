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
        Schema::create('campaign_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('marketing_campaigns')->onDelete('cascade');
            $table->integer('total_targets')->default(0);
            $table->integer('messages_sent')->default(0);
            $table->integer('messages_failed')->default(0);
            $table->decimal('delivery_rate', 5, 2)->default(0);
            $table->integer('opens')->default(0);
            $table->integer('clicks')->default(0);
            $table->integer('conversions')->default(0);
            $table->integer('unsubscribes')->default(0);
            $table->decimal('open_rate', 5, 2)->default(0);
            $table->decimal('click_rate', 5, 2)->default(0);
            $table->decimal('conversion_rate', 5, 2)->default(0);
            $table->decimal('unsubscribe_rate', 5, 2)->default(0);
            $table->json('additional_metrics')->nullable();
            $table->timestamp('last_updated');
            $table->timestamps();

            $table->unique('campaign_id');
            $table->index(['campaign_id', 'last_updated']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_analytics');
    }
};
