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
        Schema::table('categories', function (Blueprint $table) {
            $table->string('uuid')->nullable()->after('id');
            $table->foreignId('parent_id')->nullable()->constrained('categories')->onDelete('cascade')->after('uuid');
            $table->string('icon')->nullable()->after('color');
            $table->integer('sort_order')->default(0)->after('is_active');
            $table->json('metadata')->nullable()->after('sort_order');
            $table->softDeletes();
            
            $table->index('uuid');
            $table->index('parent_id');
            $table->index('sort_order');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['uuid', 'parent_id', 'icon', 'sort_order', 'metadata']);
            $table->dropSoftDeletes();
        });
    }
};
