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
        if (Schema::hasTable('ticket_alerts')) {
            Schema::table('ticket_alerts', function (Blueprint $table): void {
                if (! Schema::hasColumn('ticket_alerts', 'matches_found')) {
                    $table->integer('matches_found')->default(0)->after('status');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('ticket_alerts') && Schema::hasColumn('ticket_alerts', 'matches_found')) {
            Schema::table('ticket_alerts', function (Blueprint $table): void {
                $table->dropColumn('matches_found');
            });
        }
    }
};
