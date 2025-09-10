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
        Schema::table('users', function (Blueprint $table): void {
            // Increase phone column size to accommodate encrypted data
            // Encrypted data includes IV, value, MAC, and tag - can be 500+ characters
            $table->text('phone')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            // Revert back to string(20) - WARNING: This will truncate encrypted data!
            $table->string('phone', 20)->nullable()->change();
        });
    }
};
