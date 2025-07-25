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
        Schema::create('analytics_dashboards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('configuration'); // Layout, theme, etc.
            $table->json('widgets'); // Array of widget types
            $table->json('filters'); // Time range, platforms, categories, etc.
            $table->integer('refresh_interval')->default(300); // Seconds
            $table->boolean('is_public')->default(false);
            $table->boolean('is_default')->default(false);
            $table->json('shared_with')->nullable(); // Array of user IDs
            $table->timestamp('last_accessed_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'is_default']);
            $table->index(['is_public']);
            $table->index(['last_accessed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics_dashboards');
    }
};
