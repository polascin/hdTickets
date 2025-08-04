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
        Schema::table('users', function (Blueprint $table) {
            // Drop the unique constraint on email first (encrypted emails can't be unique)
            $table->dropUnique(['email']);
        });
        
        Schema::table('users', function (Blueprint $table) {
            // Change email column to TEXT to accommodate encrypted data
            $table->text('email')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Revert back to string(500)
            $table->string('email', 500)->change();
        });
    }
};
