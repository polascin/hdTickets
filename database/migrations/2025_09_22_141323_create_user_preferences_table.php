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
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->json('favorite_sports')->nullable();
            $table->json('favorite_teams')->nullable();
            $table->json('preferred_venues')->nullable();
            $table->json('preferred_platforms')->nullable();
            $table->json('price_range')->nullable(); // {"min": 0, "max": 1000}
            $table->json('notification_preferences')->nullable();
            $table->json('dashboard_settings')->nullable();
            $table->string('timezone', 50)->default('UTC');
            $table->string('currency', 3)->default('USD');
            $table->boolean('email_notifications')->default(TRUE);
            $table->boolean('sms_notifications')->default(FALSE);
            $table->boolean('push_notifications')->default(TRUE);
            $table->timestamps();

            $table->unique('user_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_preferences');
    }
};
