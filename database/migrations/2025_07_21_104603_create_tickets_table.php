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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            
            // Traditional support ticket fields
            $table->enum('status', ['open', 'in_progress', 'pending', 'resolved', 'closed'])->default('open');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->foreignId('requester_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('assignee_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');
            $table->timestamp('due_date')->nullable();
            $table->timestamp('resolved_at')->nullable();
            
            // Event/Concert ticket scraping fields
            $table->string('platform', 50)->nullable(); // ticketmaster, stubhub, etc.
            $table->string('external_id', 255)->nullable(); // External platform ticket ID
            $table->decimal('price', 10, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->string('location', 255)->nullable();
            $table->string('venue', 255)->nullable();
            $table->datetime('event_date')->nullable();
            $table->string('event_type', 100)->nullable(); // concert, sports, theater, etc.
            $table->string('performer_artist', 255)->nullable();
            $table->json('seat_details')->nullable(); // section, row, seat numbers
            $table->boolean('is_available')->default(true);
            $table->string('ticket_url', 500)->nullable();
            $table->json('scraping_metadata')->nullable(); // source info, selectors used, etc.
            
            $table->timestamps();
            
            // Traditional support ticket indexes
            $table->index(['status', 'priority']);
            $table->index('due_date');
            
            // Event ticket indexes
            $table->index(['platform', 'is_available']);
            $table->index(['event_date', 'platform']);
            $table->index(['location', 'event_date']);
            $table->unique(['external_id', 'platform'], 'unique_external_platform');
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
