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
            // First drop the unique constraint on email
            $table->dropUnique(['email']);
        });
        
        Schema::table('users', function (Blueprint $table) {
            // Change email to TEXT to accommodate encrypted emails
            $table->text('email')->change();
            // Add email hash column for uniqueness
            $table->string('email_hash', 64)->nullable()->after('email');
        });
        
        Schema::table('users', function (Blueprint $table) {
            // Add unique constraint on email_hash instead
            $table->unique('email_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop unique constraint on email_hash
            $table->dropUnique(['email_hash']);
            // Drop email_hash column
            $table->dropColumn('email_hash');
        });
        
        Schema::table('users', function (Blueprint $table) {
            // Revert back to varchar(255)
            $table->string('email', 255)->change();
        });
        
        Schema::table('users', function (Blueprint $table) {
            // Re-add unique constraint on email
            $table->unique('email');
        });
    }
};
