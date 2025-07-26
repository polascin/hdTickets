<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update the role enum to include the new 'scraper' role
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
        
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'agent', 'customer', 'scraper'])->default('customer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert the role enum to the original values
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
        
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'agent', 'customer'])->default('customer');
        });
    }
};
