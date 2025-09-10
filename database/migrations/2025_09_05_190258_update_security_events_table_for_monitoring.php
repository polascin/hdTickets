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
        Schema::table('security_events', function (Blueprint $table): void {
            // Add new columns for enhanced monitoring
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->after('event_type');
            $table->json('location')->nullable()->after('user_agent');
            $table->json('request_data')->nullable()->after('data');
            $table->string('session_id')->nullable()->after('request_data');
            $table->integer('threat_score')->nullable()->after('session_id');
            $table->unsignedBigInteger('incident_id')->nullable()->after('threat_score');

            // Add indexes for performance
            $table->index(['severity']);
            $table->index(['threat_score']);
            $table->index(['incident_id']);
            $table->index(['session_id']);
            $table->index(['ip_address', 'occurred_at']);

            // Add foreign key for incident
            $table->foreign('incident_id')->references('id')->on('security_incidents')->onDelete('set null');
        });

        // Rename 'data' to 'event_data' in separate statement
        Schema::table('security_events', function (Blueprint $table): void {
            $table->renameColumn('data', 'event_data');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('security_events', function (Blueprint $table): void {
            // Rename back to 'data'
            $table->renameColumn('event_data', 'data');
        });

        Schema::table('security_events', function (Blueprint $table): void {
            // Drop foreign key and indexes
            $table->dropForeign(['incident_id']);
            $table->dropIndex(['severity']);
            $table->dropIndex(['threat_score']);
            $table->dropIndex(['incident_id']);
            $table->dropIndex(['session_id']);
            $table->dropIndex(['ip_address', 'occurred_at']);

            // Drop new columns
            $table->dropColumn([
                'severity',
                'location',
                'request_data',
                'session_id',
                'threat_score',
                'incident_id',
            ]);
        });
    }
};
