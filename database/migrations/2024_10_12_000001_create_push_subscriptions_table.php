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
        Schema::create('push_subscriptions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('endpoint');
            $table->string('p256dh_key');
            $table->string('auth_key');
            $table->text('user_agent')->nullable();
            $table->string('ip_address')->nullable();
            $table->integer('successful_notifications')->default(0);
            $table->integer('failed_notifications')->default(0);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            // Index for performance, uniqueness will be handled at application level
            $table->index(['user_id']);
            $table->index(['user_id', 'last_used_at']);
            $table->index('last_used_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('push_subscriptions');
    }
};
