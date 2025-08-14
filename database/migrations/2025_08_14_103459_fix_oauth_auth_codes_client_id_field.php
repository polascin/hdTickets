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
        Schema::table('oauth_auth_codes', function (Blueprint $table): void {
            // Change client_id from bigint to varchar(36) to match oauth_clients.id
            $table->string('client_id', 36)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('oauth_auth_codes', function (Blueprint $table): void {
            // Change client_id back to bigint unsigned
            $table->unsignedBigInteger('client_id')->change();
        });
    }
};
