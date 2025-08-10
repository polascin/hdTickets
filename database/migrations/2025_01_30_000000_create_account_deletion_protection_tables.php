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
        // Account deletion requests table
        Schema::create('account_deletion_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('confirmation_token')->unique();
            $table->string('status')->default('pending'); // pending, confirmed, cancelled, expired, completed
            $table->json('user_data_snapshot'); // Store user data snapshot
            $table->timestamp('initiated_at');
            $table->timestamp('email_confirmed_at')->nullable();
            $table->timestamp('grace_period_expires_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->json('metadata')->nullable(); // Additional data (IP, user agent, etc.)
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index(['status', 'grace_period_expires_at']);
            $table->index(['confirmation_token']);
        });

        // Soft deleted users table
        Schema::create('deleted_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('original_user_id');
            $table->json('user_data'); // Complete user data backup
            $table->json('related_data'); // Related model data (preferences, etc.)
            $table->string('deletion_reason')->nullable();
            $table->timestamp('deleted_at');
            $table->timestamp('recoverable_until'); // 30 days from deletion
            $table->boolean('is_recovered')->default(false);
            $table->timestamp('recovered_at')->nullable();
            $table->timestamps();
            
            $table->index(['original_user_id']);
            $table->index(['deleted_at', 'recoverable_until']);
            $table->index(['is_recovered']);
        });

        // Account deletion audit log
        Schema::create('account_deletion_audit_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('action'); // initiated, email_sent, confirmed, cancelled, completed, recovered
            $table->string('status_from')->nullable();
            $table->string('status_to')->nullable();
            $table->text('description');
            $table->json('context')->nullable(); // IP, user agent, additional data
            $table->timestamp('occurred_at');
            $table->timestamps();
            
            $table->index(['user_id', 'action']);
            $table->index(['action', 'occurred_at']);
        });

        // Data export requests table
        Schema::create('data_export_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('export_type')->default('full'); // full, partial
            $table->json('data_types'); // What data to export
            $table->string('format')->default('json'); // json, csv
            $table->string('status')->default('pending'); // pending, processing, completed, failed
            $table->string('file_path')->nullable();
            $table->integer('file_size')->nullable();
            $table->timestamp('expires_at'); // Download link expiration
            $table->text('error_message')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index(['status', 'expires_at']);
        });

        // Add soft delete column to users table if it doesn't exist
        if (!Schema::hasColumn('users', 'deleted_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('deleted_at')->nullable()->after('updated_at');
                $table->index(['deleted_at']);
            });
        }

        // Add deletion protection fields to users table
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('deletion_protection_enabled')->default(true)->after('is_active');
            $table->timestamp('last_deletion_attempt_at')->nullable()->after('deletion_protection_enabled');
            $table->integer('deletion_attempt_count')->default(0)->after('last_deletion_attempt_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'deleted_at', 
                'deletion_protection_enabled', 
                'last_deletion_attempt_at', 
                'deletion_attempt_count'
            ]);
        });

        Schema::dropIfExists('data_export_requests');
        Schema::dropIfExists('account_deletion_audit_log');
        Schema::dropIfExists('deleted_users');
        Schema::dropIfExists('account_deletion_requests');
    }
};
