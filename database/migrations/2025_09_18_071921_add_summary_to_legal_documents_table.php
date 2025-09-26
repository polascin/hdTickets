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
        if (Schema::hasTable('legal_documents')) {
            Schema::table('legal_documents', function (Blueprint $table): void {
                if (! Schema::hasColumn('legal_documents', 'summary')) {
                    $table->text('summary')->nullable()->after('content');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('legal_documents') && Schema::hasColumn('legal_documents', 'summary')) {
            Schema::table('legal_documents', function (Blueprint $table): void {
                $table->dropColumn('summary');
            });
        }
    }
};
