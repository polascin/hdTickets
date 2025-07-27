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
        Schema::table('users', function (Blueprint $table) {
            // Check and add columns only if they don't exist
            if (!Schema::hasColumn('users', 'profile_picture')) {
                $table->string('profile_picture')->nullable()->after('password');
            }
            if (!Schema::hasColumn('users', 'bio')) {
                $table->text('bio')->nullable()->after('profile_picture');
            }
            if (!Schema::hasColumn('users', 'timezone')) {
                $table->string('timezone')->default('UTC')->after('bio');
            }
            if (!Schema::hasColumn('users', 'language')) {
                $table->string('language')->default('en')->after('timezone');
            }
            
            // System metadata
            if (!Schema::hasColumn('users', 'created_by_type')) {
                $table->string('created_by_type')->default('self')->after('language');
            }
            if (!Schema::hasColumn('users', 'created_by_id')) {
                $table->unsignedBigInteger('created_by_id')->nullable()->after('created_by_type');
            }
            if (!Schema::hasColumn('users', 'last_activity_at')) {
                $table->timestamp('last_activity_at')->nullable()->after('created_by_id');
            }
            
            // Additional permission flags
            if (!Schema::hasColumn('users', 'custom_permissions')) {
                $table->json('custom_permissions')->nullable()->after('last_activity_at');
            }
            if (!Schema::hasColumn('users', 'email_notifications')) {
                $table->boolean('email_notifications')->default(true)->after('custom_permissions');
            }
            if (!Schema::hasColumn('users', 'push_notifications')) {
                $table->boolean('push_notifications')->default(true)->after('email_notifications');
            }
        });
        
        // Add foreign key and indexes separately
        Schema::table('users', function (Blueprint $table) {
            // Add foreign key for created_by_id if it doesn't exist
            if (Schema::hasColumn('users', 'created_by_id')) {
                try {
                    $table->foreign('created_by_id')->references('id')->on('users')->onDelete('set null');
                } catch (Exception $e) {
                    // Foreign key might already exist
                }
            }
            
            // Add indexes if they don't exist
            try {
                if (Schema::hasColumn('users', 'last_login_at')) {
                    $table->index('last_login_at');
                }
                if (Schema::hasColumn('users', 'last_activity_at')) {
                    $table->index('last_activity_at');
                }
                if (Schema::hasColumn('users', 'registration_source')) {
                    $table->index('registration_source');
                }
                if (Schema::hasColumn('users', 'activity_score')) {
                    $table->index('activity_score');
                }
            } catch (Exception $e) {
                // Indexes might already exist
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['created_by_id']);
            $table->dropIndex(['last_login_at']);
            $table->dropIndex(['last_activity_at']);
            $table->dropIndex(['registration_source']);
            $table->dropIndex(['activity_score']);
            
            $table->dropColumn([
                'last_login_at',
                'last_login_ip', 
                'last_login_user_agent',
                'registration_source',
                'login_count',
                'activity_score',
                'profile_picture',
                'bio',
                'timezone',
                'language',
                'created_by_type',
                'created_by_id',
                'last_activity_at',
                'custom_permissions',
                'email_notifications',
                'push_notifications'
            ]);
        });
    }
};
