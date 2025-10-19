<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('event_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category')->default('general');
            $table->string('color_code', 7)->default('#3B82F6');
            $table->json('settings')->nullable();
            $table->json('monitoring_config')->nullable();
            $table->boolean('is_active')->default(TRUE);
            $table->integer('total_events')->default(0);
            $table->timestamp('last_modified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'is_active']);
            $table->index(['category']);
            $table->index(['last_modified_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_groups');
    }
};
