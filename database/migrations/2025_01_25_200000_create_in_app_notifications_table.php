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
        Schema::create('in_app_notifications', function (Blueprint $table) {
            $table->string('id', 50)->primary();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type', 50)->index();
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable();
            $table->tinyInteger('priority')->default(2)->index();
            $table->boolean('is_read')->default(false)->index();
            $table->boolean('is_dismissed')->default(false)->index();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('dismissed_at')->nullable();
            $table->timestamp('created_at');
            $table->timestamp('expires_at')->index();
            
            // Add indexes for common queries
            $table->index(['user_id', 'is_read', 'created_at']);
            $table->index(['user_id', 'type', 'created_at']);
            $table->index(['user_id', 'priority', 'created_at']);
            $table->index(['expires_at', 'is_dismissed']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('in_app_notifications');
    }
};
