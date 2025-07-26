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
        // Add Tier 3 UK sports platforms to the platform enum
        $platforms = [
            'official', 'ticketmaster', 'stubhub', 'viagogo', 'seatgeek', 'tickpick',
            'eventbrite', 'bandsintown', 'axs', 'manchester_united', 'wimbledon',
            'liverpoolfc', 'wembley', 'ticketek_uk', 'arsenal', 'twickenham',
            'lords_cricket', 'seetickets_uk', 'chelsea', 'tottenham',
            'england_cricket', 'silverstone_f1', 'celtic', 'other'
        ];
        
        $enumValues = "'" . implode("', '", $platforms) . "'";
        DB::statement("ALTER TABLE ticket_sources MODIFY COLUMN platform ENUM($enumValues)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to previous enum values (without Tier 3)
        $platforms = [
            'official', 'ticketmaster', 'stubhub', 'viagogo', 'seatgeek', 'tickpick',
            'eventbrite', 'bandsintown', 'axs', 'manchester_united', 'wimbledon',
            'liverpoolfc', 'wembley', 'ticketek_uk', 'arsenal', 'twickenham',
            'lords_cricket', 'other'
        ];
        
        $enumValues = "'" . implode("', '", $platforms) . "'";
        DB::statement("ALTER TABLE ticket_sources MODIFY COLUMN platform ENUM($enumValues)");
    }
};
