<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('event_group_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_group_id');
            $table->unsignedBigInteger('event_id');
            $table->timestamp('added_at');
            $table->integer('priority')->default(5);
            $table->json('custom_settings')->nullable();
            $table->boolean('is_active')->default(TRUE);
            $table->timestamps();

            $table->unique(['event_group_id', 'event_id']);
            $table->index(['event_group_id', 'is_active']);
            $table->index(['priority']);
        });

        // Add foreign keys only if referenced tables exist (useful in testing when loading partial schema dumps)
        Schema::table('event_group_events', function (Blueprint $table) {
            if (Schema::hasTable('event_groups')) {
                $table->foreign('event_group_id')->references('id')->on('event_groups')->cascadeOnDelete();
            }
            if (Schema::hasTable('events')) {
                $table->foreign('event_id')->references('id')->on('events')->cascadeOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_group_events');
    }
};
