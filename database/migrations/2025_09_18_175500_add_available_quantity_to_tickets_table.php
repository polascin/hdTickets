<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tickets')) {
            return;
        }
        if (!Schema::hasColumn('tickets', 'available_quantity')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->unsignedInteger('available_quantity')->nullable()->after('currency');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tickets') && Schema::hasColumn('tickets', 'available_quantity')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->dropColumn('available_quantity');
            });
        }
    }
};