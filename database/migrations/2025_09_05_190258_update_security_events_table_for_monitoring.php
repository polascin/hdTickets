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
        if (! Schema::hasTable('security_events')) {
            return; // Table not present in test schema snapshot
        }
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
        if (Schema::hasTable('security_events')) {
            Schema::table('security_events', function (Blueprint $table): void {
                if (Schema::hasColumn('security_events', 'data') && ! Schema::hasColumn('security_events', 'event_data')) {
                    $table->renameColumn('data', 'event_data');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('security_events')) {
            return;
        }
        Schema::table('security_events', function (Blueprint $table): void {
            if (Schema::hasColumn('security_events', 'event_data') && ! Schema::hasColumn('security_events', 'data')) {
                $table->renameColumn('event_data', 'data');
            }
        });

        Schema::table('security_events', function (Blueprint $table): void {
            // Drop foreign key and indexes
            try {
                $table->dropForeign(['incident_id']);
            } catch (Throwable $e) { // ignore
            }
            foreach (['severity', 'threat_score', 'incident_id', 'session_id'] as $idx) {
                try {
                    $table->dropIndex([$idx]);
                } catch (Throwable $e) { // ignore
                }
            }

            try {
                $table->dropIndex(['ip_address', 'occurred_at']);
            } catch (Throwable $e) { // ignore
            }

            // Drop new columns
            foreach (['severity', 'location', 'request_data', 'session_id', 'threat_score', 'incident_id'] as $col) {
                if (Schema::hasColumn('security_events', $col)) {
                    try {
                        $table->dropColumn($col);
                    } catch (Throwable $e) { // ignore
                    }
                }
            }
        });
    }
};
