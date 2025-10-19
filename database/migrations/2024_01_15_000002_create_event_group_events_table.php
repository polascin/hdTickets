<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('event_group_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->timestamp('added_at');
            $table->integer('priority')->default(5);
            $table->json('custom_settings')->nullable();
            $table->boolean('is_active')->default(TRUE);
            $table->timestamps();

            $table->unique(['event_group_id', 'event_id']);
            $table->index(['event_group_id', 'is_active']);
            $table->index(['priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_group_events');
    }
};
