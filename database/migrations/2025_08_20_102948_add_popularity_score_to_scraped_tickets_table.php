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
        if (! Schema::hasColumn('scraped_tickets', 'popularity_score')) {
            Schema::table('scraped_tickets', function (Blueprint $table): void {
                $table->decimal('popularity_score', 5, 2)->default(50.0)->after('predicted_demand');
                $table->index('popularity_score');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('scraped_tickets', 'popularity_score')) {
            Schema::table('scraped_tickets', function (Blueprint $table): void {
                try {
                    $table->dropIndex(['popularity_score']);
                } catch (Throwable $e) { // ignore
                }
                $table->dropColumn('popularity_score');
            });
        }
    }
};
