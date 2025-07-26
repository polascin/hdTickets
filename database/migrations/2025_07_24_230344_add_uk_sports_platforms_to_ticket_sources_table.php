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
        // Add UK sports platforms to the platform enum
        DB::statement("ALTER TABLE ticket_sources MODIFY COLUMN platform ENUM(
            'official',
            'ticketmaster', 
            'stubhub',
            'viagogo',
            'seatgeek',
            'tickpick',
            'eventbrite',
            'bandsintown',
            'axs',
            'manchester_united',
            'wimbledon',
            'liverpoolfc',
            'wembley',
            'ticketek_uk',
            'arsenal',
            'twickenham',
            'lords_cricket',
            'other'
        )");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to previous enum values
        DB::statement("ALTER TABLE ticket_sources MODIFY COLUMN platform ENUM(
            'official',
            'ticketmaster', 
            'stubhub',
            'viagogo',
            'seatgeek',
            'tickpick',
            'eventbrite',
            'bandsintown',
            'axs',
            'other'
        )");
    }
};
