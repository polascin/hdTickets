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
        Schema::create('security_incidents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->enum('severity', ['low', 'medium', 'high', 'critical']);
            $table->enum('status', ['open', 'investigating', 'in_progress', 'resolved', 'closed']);
            $table->enum('priority', ['low', 'medium', 'high', 'critical']);
            $table->unsignedBigInteger('affected_user_id')->nullable();
            $table->string('source_ip')->nullable();
            $table->string('detection_method', 100);
            $table->json('incident_data')->nullable();
            $table->timestamp('detected_at');
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->boolean('false_positive')->default(false);
            $table->timestamps();
            
            $table->index(['status']);
            $table->index(['severity']);
            $table->index(['priority']);
            $table->index(['detected_at']);
            $table->index(['source_ip']);
            $table->index(['affected_user_id']);
            $table->index(['assigned_to']);
            
            $table->foreign('affected_user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security_incidents');
    }
};
