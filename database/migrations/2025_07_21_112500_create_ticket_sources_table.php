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
        Schema::create('ticket_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('external_id')->nullable()->index();
            $table->enum('platform', [
                'official',
                'ticketmaster', 
                'stubhub',
                'viagogo',
                'seatgeek',
                'tickpick',
                'fanzone',
                'other'
            ]);
            $table->string('event_name');
            $table->datetime('event_date');
            $table->string('venue');
            $table->decimal('price_min', 10, 2)->nullable();
            $table->decimal('price_max', 10, 2)->nullable();
            $table->enum('availability_status', [
                'available',
                'low_inventory',
                'sold_out',
                'not_on_sale',
                'unknown'
            ])->default('unknown');
            $table->text('url')->nullable();
            $table->text('description')->nullable();
            $table->datetime('last_checked')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['platform', 'availability_status']);
            $table->index(['event_date']);
            $table->index(['is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_sources');
    }
};
