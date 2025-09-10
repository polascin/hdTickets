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
        if (Schema::hasTable('user_favorite_teams')) {
            return; // Already created by earlier sports event preferences migration
        }
        Schema::create('user_favorite_teams', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->integer('priority')->default(1)->comment('User priority rating 1-5');
            $table->json('notification_settings')->nullable()->comment('Team-specific notification preferences');
            $table->timestamps();

            // Prevent duplicate team favorites per user
            $table->unique(['user_id', 'team_id'], 'user_team_unique');

            // Index for efficient queries
            $table->index(['user_id', 'priority'], 'user_priority_index');
            $table->index('team_id', 'team_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_favorite_teams');
    }
};
