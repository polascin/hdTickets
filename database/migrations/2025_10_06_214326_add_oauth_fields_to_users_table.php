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
            $table->string('google_id')->nullable()->after('id');
            $table->string('avatar')->nullable()->after('email_verified_at');
            $table->string('provider')->nullable()->after('avatar');
            $table->string('provider_id')->nullable()->after('provider');
            $table->timestamp('provider_verified_at')->nullable()->after('provider_id');
            
            // Make password nullable for OAuth users
            $table->string('password')->nullable()->change();
            
            // Add indexes for OAuth fields
            $table->index(['provider', 'provider_id']);
            $table->index('google_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['provider', 'provider_id']);
            $table->dropIndex(['google_id']);
            
            $table->dropColumn([
                'google_id',
                'avatar',
                'provider',
                'provider_id',
                'provider_verified_at'
            ]);
            
            // Make password required again
            $table->string('password')->nullable(false)->change();
        });
    }
};
