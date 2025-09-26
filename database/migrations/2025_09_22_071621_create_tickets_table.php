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
        Schema::create('tickets', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('requester_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->dateTime('event_date');
            $table->string('venue');
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('sport_type')->nullable();
            $table->string('team_home')->nullable();
            $table->string('team_away')->nullable();
            $table->decimal('price', 8, 2);
            $table->string('currency', 3)->default('USD');
            $table->integer('available_quantity')->default(0);
            $table->boolean('is_available')->default(TRUE);
            $table->enum('status', ['open', 'pending', 'resolved', 'closed'])->default('open');
            $table->string('platform')->nullable();
            $table->string('source')->nullable();
            $table->text('ticket_url')->nullable();
            $table->json('scraping_metadata')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
