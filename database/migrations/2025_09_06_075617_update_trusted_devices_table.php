<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('trusted_devices', function (Blueprint $table): void {
            // Add trust token
            $table->string('trust_token')->after('device_fingerprint');

            // Add security fields
            $table->timestamp('first_seen_at')->nullable()->after('location_data');
            $table->integer('usage_count')->default(0)->after('expires_at');
            $table->integer('trust_score')->default(25)->after('usage_count');
            $table->boolean('is_active')->default(TRUE)->after('trust_score');

            // Add location data as JSON
            $table->json('location_data')->nullable()->after('user_agent');

            // Add soft deletes
            $table->softDeletes();

            // Add indexes for performance
            $table->index('trust_token');
            $table->index('is_active');
            $table->index(['user_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trusted_devices', function (Blueprint $table): void {
            // Drop new columns
            $table->dropColumn([
                'trust_token',
                'first_seen_at',
                'usage_count',
                'trust_score',
                'is_active',
                'location_data',
                'deleted_at',
            ]);

            // Drop indexes
            $table->dropIndex(['trust_token']);
            $table->dropIndex(['is_active']);
            $table->dropIndex(['user_id', 'is_active']);
        });
    }
};
