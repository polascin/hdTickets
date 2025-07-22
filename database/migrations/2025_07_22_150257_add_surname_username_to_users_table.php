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
        // Add columns if they don't exist
        if (!Schema::hasColumn('users', 'surname')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('surname')->nullable()->after('name');
            });
        }
        
        if (!Schema::hasColumn('users', 'username')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('username')->nullable()->after('email');
            });
        }
        
        // Generate unique usernames for users that don't have one
        $usersWithoutUsernames = \App\Models\User::where('username', '')->orWhereNull('username')->get();
        foreach ($usersWithoutUsernames as $user) {
            $baseUsername = strtolower(str_replace(' ', '', $user->name));
            $baseUsername = preg_replace('/[^a-z0-9]/', '', $baseUsername);
            
            // Handle empty names
            if (empty($baseUsername)) {
                $baseUsername = 'user' . $user->id;
            }
            
            $username = $baseUsername;
            $counter = 1;
            
            // Ensure username is unique
            while (\App\Models\User::where('username', $username)->where('id', '!=', $user->id)->exists()) {
                $username = $baseUsername . $counter;
                $counter++;
            }
            
            $user->update(['username' => $username]);
        }
        
        // Add unique constraint and index if they don't exist
        $indexes = collect(\DB::select("SHOW INDEXES FROM users WHERE Column_name = 'username'"));
        $hasUniqueIndex = $indexes->contains(function ($index) {
            return $index->Non_unique == 0; // Non_unique = 0 means it's a unique index
        });
        
        if (!$hasUniqueIndex) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('username')->unique()->change();
            });
        }
        
        // Add regular index if it doesn't exist
        $hasIndex = $indexes->isNotEmpty();
        if (!$hasIndex) {
            Schema::table('users', function (Blueprint $table) {
                $table->index('username');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['username']);
            $table->dropColumn(['surname', 'username']);
        });
    }
};
