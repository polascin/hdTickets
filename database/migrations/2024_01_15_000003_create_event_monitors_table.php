<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('event_monitors', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('event_group_id')->nullable();
            $table->boolean('is_active')->default(TRUE);
            $table->integer('priority')->default(5);
            $table->integer('check_interval')->default(300); // seconds
            $table->json('platforms');
            $table->json('notification_preferences');
            $table->json('custom_settings')->nullable();
            $table->timestamp('last_check_at')->nullable();
            $table->decimal('last_response_time', 8, 2)->nullable();
            $table->integer('success_count')->default(0);
            $table->integer('failure_count')->default(0);
            $table->integer('total_checks')->default(0);
            $table->text('last_error')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id', 'event_id']);
            $table->index(['user_id', 'is_active']);
            $table->index(['event_group_id']);
            $table->index(['priority']);
            $table->index(['last_check_at']);
        });

        Schema::table('event_monitors', function (Blueprint $table): void {
            if (Schema::hasTable('users')) {
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            }
            if (Schema::hasTable('events')) {
                $table->foreign('event_id')->references('id')->on('events')->cascadeOnDelete();
            }
            if (Schema::hasTable('event_groups')) {
                $table->foreign('event_group_id')->references('id')->on('event_groups')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_monitors');
    }
};
