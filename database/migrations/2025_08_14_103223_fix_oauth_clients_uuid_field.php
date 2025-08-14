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
        // First, drop the auto increment and primary key
        DB::statement('ALTER TABLE `oauth_clients` MODIFY `id` BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE `oauth_clients` DROP PRIMARY KEY');

        // Then change to varchar and add primary key back
        Schema::table('oauth_clients', function (Blueprint $table): void {
            $table->string('id', 36)->change();
            $table->primary('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('oauth_clients', function (Blueprint $table): void {
            // Drop the primary key constraint
            $table->dropPrimary(['id']);
            // Change the id field back to bigint unsigned with auto increment
            $table->bigIncrements('id')->change();
        });
    }
};
