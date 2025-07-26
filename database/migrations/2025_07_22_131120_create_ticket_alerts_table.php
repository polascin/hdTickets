<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ticket_alerts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name'); // Alert name like "Man Utd vs Liverpool"
            $table->text('keywords'); // Search keywords
            $table->string('platform')->nullable(); // specific platform or null for all
            $table->decimal('max_price', 8, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->json('filters')->nullable(); // Additional filters
            $table->boolean('is_active')->default(true);
            $table->boolean('email_notifications')->default(true);
            $table->boolean('sms_notifications')->default(false);
            $table->integer('matches_found')->default(0);
            $table->timestamp('last_triggered_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'is_active']);
            $table->index('last_triggered_at');
        });
        
        // Add index with database-specific syntax
        if (DB::getDriverName() === 'mysql') {
            DB::statement('CREATE INDEX ticket_alerts_keywords_is_active_index ON ticket_alerts (keywords(100), is_active)');
        } else {
            // For SQLite and other databases, create index without length limit
            DB::statement('CREATE INDEX ticket_alerts_keywords_is_active_index ON ticket_alerts (keywords, is_active)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_alerts');
    }
};
