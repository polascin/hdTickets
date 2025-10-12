<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_monitors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_group_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(5);
            $table->integer('check_interval')->default(300); // seconds
            $table->json('platforms')->default('["ticketmaster"]');
            $table->json('notification_preferences')->default('["email"]');
            $table->json('custom_settings')->nullable();
            $table->timestamp('last_check_at')->nullable();
            $table->decimal('last_response_time', 8, 2)->nullable();
            $table->integer('success_count')->default(0);
            $table->integer('failure_count')->default(0);
            $table->integer('total_checks')->default(0);
            $table->text('last_error')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id', 'event_id']);
            $table->index(['user_id', 'is_active']);
            $table->index(['event_group_id']);
            $table->index(['priority']);
            $table->index(['last_check_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_monitors');
    }
};