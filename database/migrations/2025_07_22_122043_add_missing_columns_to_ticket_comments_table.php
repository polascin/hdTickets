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
        Schema::table('ticket_comments', function (Blueprint $table) {
            $table->string('uuid')->nullable()->after('id');
            $table->renameColumn('comment', 'content');
            $table->string('type')->default('comment')->after('content');
            $table->boolean('is_solution')->default(false)->after('is_internal');
            $table->json('metadata')->nullable()->after('is_solution');
            $table->timestamp('edited_at')->nullable()->after('metadata');
            $table->foreignId('edited_by')->nullable()->constrained('users')->onDelete('set null')->after('edited_at');
            $table->softDeletes();
            
            $table->index('uuid');
            $table->index('type');
            $table->index('is_solution');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ticket_comments', function (Blueprint $table) {
            $table->renameColumn('content', 'comment');
            $table->dropColumn(['uuid', 'type', 'is_solution', 'metadata', 'edited_at', 'edited_by']);
            $table->dropSoftDeletes();
        });
    }
};
