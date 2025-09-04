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
        Schema::create('alert_triggers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ticket_alert_id')->constrained()->onDelete('cascade');
            $table->foreignId('scraped_ticket_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamp('triggered_at')->default(now());
            $table->decimal('match_score', 5, 2)->default(0.00);
            $table->string('trigger_reason')->nullable();
            $table->boolean('notification_sent')->default(FALSE);
            $table->boolean('user_acknowledged')->default(FALSE);
            $table->timestamps();

            $table->index(['ticket_alert_id', 'triggered_at']);
            $table->index(['triggered_at']);
            $table->index(['notification_sent']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alert_triggers');
    }
};
