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
        Schema::table('scraped_tickets', function (Blueprint $table) {
            $table->enum('status', [
                'active',
                'sold_out', 
                'expired',
                'cancelled',
                'pending_verification',
                'invalid'
            ])->default('active')->after('is_high_demand');
            
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scraped_tickets', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropColumn('status');
        });
    }
};
