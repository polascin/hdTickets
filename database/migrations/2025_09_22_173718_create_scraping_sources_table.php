<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('scraping_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('base_url');
            $table->integer('rate_limit')->default(60);
            $table->enum('priority', ['high', 'medium', 'low'])->default('medium');
            $table->boolean('enabled')->default(TRUE);
            $table->enum('status', ['online', 'offline', 'testing', 'error'])->default('offline');
            $table->json('headers')->nullable();
            $table->json('config')->nullable();
            $table->timestamps();

            $table->index(['enabled', 'status']);
            $table->index('priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scraping_sources');
    }
};
