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
            // Change availability column from integer to string
            if (Schema::hasColumn('scraped_tickets', 'availability')) {
                $table->string('availability', 50)->default('unknown')->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scraped_tickets', function (Blueprint $table): void {
            // Revert back to integer if needed
            if (Schema::hasColumn('scraped_tickets', 'availability')) {
                $table->integer('availability')->default(0)->change();
            }
        });
    }
};
