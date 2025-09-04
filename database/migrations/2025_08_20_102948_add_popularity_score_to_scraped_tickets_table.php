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
        Schema::table('scraped_tickets', function (Blueprint $table): void {
            // Add popularity_score column as decimal with default value
            $table->decimal('popularity_score', 5, 2)->default(50.0)->after('predicted_demand');

            // Add index for performance since it's used in WHERE clauses
            $table->index('popularity_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scraped_tickets', function (Blueprint $table): void {
            // Drop the index first, then the column
            $table->dropIndex(['popularity_score']);
            $table->dropColumn('popularity_score');
        });
    }
};
