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
        Schema::create('scheduled_reports', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['daily', 'weekly', 'monthly', 'custom'])->default('weekly');
            $table->enum('format', ['pdf', 'xlsx', 'csv', 'json'])->default('pdf');
            $table->string('schedule')->nullable(); // Cron expression for custom schedules
            $table->json('sections'); // Array of sections to include
            $table->json('filters')->nullable(); // Array of filters to apply
            $table->json('recipients'); // Array of email addresses
            $table->json('options')->nullable(); // Additional options
            $table->json('statistics')->nullable(); // Execution statistics
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['type', 'is_active']);
            $table->index(['created_by']);
            $table->index(['is_active', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduled_reports');
    }
};
