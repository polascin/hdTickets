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
        Schema::create('scraping_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('platform')->index();
            $table->json('settings')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->string('sport')->nullable()->after('event_type');
            $table->json('additional_metadata')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scraping_configurations');
    }
};
