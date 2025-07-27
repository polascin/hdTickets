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
            $table->string('type'); // Report type
            $table->json('parameters')->nullable(); // Report parameters
            $table->json('recipients')->nullable(); // Email recipients
            $table->enum('frequency', ['daily', 'weekly', 'monthly'])->default('weekly');
            $table->enum('format', ['pdf', 'xlsx', 'csv'])->default('pdf');
            $table->datetime('next_run');
            $table->datetime('last_run')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['is_active', 'next_run']);
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
