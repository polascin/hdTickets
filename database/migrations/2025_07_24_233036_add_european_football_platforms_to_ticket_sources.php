<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get current enum values
        $currentEnums = [];
        $result = DB::select("SHOW COLUMNS FROM ticket_sources LIKE 'platform'");
        if (!empty($result)) {
            $enumString = $result[0]->Type;
            preg_match('/enum\((.*)\)/', $enumString, $matches);
            if (isset($matches[1])) {
                $currentEnums = array_map(function($value) {
                    return trim($value, "'\"");
                }, explode(',', $matches[1]));
            }
        }

        // New European football and ticketing platforms to add
        $newPlatforms = [
            // European Football Clubs
            'manchester_city',
            'real_madrid',
            'barcelona', 
            'atletico_madrid',
            'bayern_munich',
            'borussia_dortmund',
            'juventus',
            'ac_milan',
            'inter_milan', 
            'psg',
            'newcastle_united',
            // European Ticketing Platforms
            'eventim',
            'fnac_spectacles',
            'vivaticket',
            'entradas',
        ];

        // Merge with existing enums and remove duplicates
        $allEnums = array_unique(array_merge($currentEnums, $newPlatforms));
        sort($allEnums);

        // Build new enum string
        $enumValues = implode(',', array_map(function($value) {
            return "'" . addslashes($value) . "'";
        }, $allEnums));

        // Update the enum column
        DB::statement("ALTER TABLE ticket_sources MODIFY COLUMN platform ENUM($enumValues)");

        // Add currency column if it doesn't exist
        if (!Schema::hasColumn('ticket_sources', 'currency')) {
            Schema::table('ticket_sources', function (Blueprint $table) {
                $table->string('currency', 3)->default('GBP')->after('price_max');
                $table->index('currency');
            });
        }

        // Add language column if it doesn't exist
        if (!Schema::hasColumn('ticket_sources', 'language')) {
            Schema::table('ticket_sources', function (Blueprint $table) {
                $table->string('language', 5)->default('en-GB')->after('currency');
                $table->index('language');
            });
        }

        // Add country column if it doesn't exist
        if (!Schema::hasColumn('ticket_sources', 'country')) {
            Schema::table('ticket_sources', function (Blueprint $table) {
                $table->string('country', 2)->default('GB')->after('language');
                $table->index('country');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Get current enum values
        $currentEnums = [];
        $result = DB::select("SHOW COLUMNS FROM ticket_sources LIKE 'platform'");
        if (!empty($result)) {
            $enumString = $result[0]->Type;
            preg_match('/enum\((.*)\)/', $enumString, $matches);
            if (isset($matches[1])) {
                $currentEnums = array_map(function($value) {
                    return trim($value, "'\"");
                }, explode(',', $matches[1]));
            }
        }

        // Platforms to remove
        $platformsToRemove = [
            'manchester_city',
            'real_madrid', 
            'barcelona',
            'atletico_madrid',
            'bayern_munich',
            'borussia_dortmund',
            'juventus',
            'ac_milan',
            'inter_milan',
            'psg',
            'newcastle_united',
            'eventim',
            'fnac_spectacles',
            'vivaticket',
            'entradas',
        ];

        // Remove the new platforms
        $remainingEnums = array_diff($currentEnums, $platformsToRemove);
        sort($remainingEnums);

        // Build enum string
        $enumValues = implode(',', array_map(function($value) {
            return "'" . addslashes($value) . "'";
        }, $remainingEnums));

        // Update the enum column
        DB::statement("ALTER TABLE ticket_sources MODIFY COLUMN platform ENUM($enumValues)");

        // Remove added columns
        Schema::table('ticket_sources', function (Blueprint $table) {
            $table->dropIndex(['currency']);
            $table->dropIndex(['language']);
            $table->dropIndex(['country']);
            $table->dropColumn(['currency', 'language', 'country']);
        });
    }
};
