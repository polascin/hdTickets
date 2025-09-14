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
        // If the table already exists (e.g., created manually or via a prior targeted run), skip.
        if (Schema::hasTable('legal_documents')) {
            return;
        }

        Schema::create('legal_documents', function (Blueprint $table): void {
            $table->id();
            $table->string('type', 50)->index(); // terms_of_service, privacy_policy, etc.
            $table->string('title', 255);
            $table->string('slug', 255)->unique();
            $table->longText('content');
            $table->string('version', 20)->default('1.0');
            $table->boolean('is_active')->default(TRUE)->index();
            $table->boolean('requires_acceptance')->default(FALSE);
            $table->timestamp('effective_date')->index();
            $table->timestamps();

            // Unique constraint for active documents of the same type
            $table->unique(['type', 'is_active'], 'legal_documents_type_active_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('legal_documents');
    }
};
