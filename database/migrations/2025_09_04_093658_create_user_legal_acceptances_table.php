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
        // If the table already exists, skip to avoid duplicate table errors.
        if (Schema::hasTable('user_legal_acceptances')) {
            return;
        }

        Schema::create('user_legal_acceptances', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('legal_document_id')->constrained()->onDelete('cascade');
            $table->string('document_version', 20);
            $table->string('acceptance_method', 50)->default('explicit'); // registration, explicit, update
            $table->string('ip_address', 45)->nullable(); // IPv6 support
            $table->text('user_agent')->nullable();
            $table->timestamp('accepted_at');
            $table->timestamps();

            // Index for efficient queries
            $table->index(['user_id', 'accepted_at']);
            $table->index(['legal_document_id', 'accepted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_legal_acceptances');
    }
};
