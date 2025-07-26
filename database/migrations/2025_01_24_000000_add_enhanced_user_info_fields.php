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
            // Activity tracking
            $table->timestamp('last_login_at')->nullable()->after('email_verified_at');
            $table->string('last_login_ip')->nullable()->after('last_login_at');
            $table->string('last_login_user_agent')->nullable()->after('last_login_ip');
            
            // Account creation source and stats
            $table->string('registration_source')->default('web')->after('password'); // web, api, admin, import
            $table->integer('login_count')->default(0)->after('registration_source');
            $table->integer('activity_score')->default(0)->after('login_count'); // Based on interactions
            
            // Profile information
            $table->string('profile_picture')->nullable()->after('phone');
            $table->text('bio')->nullable()->after('profile_picture');
            $table->string('timezone')->default('UTC')->after('bio');
            $table->string('language')->default('en')->after('timezone');
            
            // System metadata
            $table->string('created_by_type')->default('self')->after('language'); // self, admin, system
            $table->unsignedBigInteger('created_by_id')->nullable()->after('created_by_type');
            $table->timestamp('last_activity_at')->nullable()->after('created_by_id');
            
            // Additional permission flags
            $table->json('custom_permissions')->nullable()->after('last_activity_at');
            $table->boolean('email_notifications')->default(true)->after('custom_permissions');
            $table->boolean('push_notifications')->default(true)->after('email_notifications');
            
            // Add foreign key for created_by_id
            $table->foreign('created_by_id')->references('id')->on('users')->onDelete('set null');
            
            // Add indexes for performance
            $table->index('last_login_at');
            $table->index('last_activity_at');
            $table->index('registration_source');
            $table->index('activity_score');
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
