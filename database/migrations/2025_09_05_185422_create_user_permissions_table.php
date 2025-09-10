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
        Schema::create('user_permissions', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('permission_id');
            $table->string('resource_type', 100)->nullable();
            $table->string('resource_id', 100)->nullable();
            $table->timestamp('granted_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->unsignedBigInteger('granted_by')->nullable();
            $table->timestamps();

            $table->index(['user_id']);
            $table->index(['permission_id']);
            $table->index(['resource_type']);
            $table->index(['resource_id']);
            $table->index(['expires_at']);
            $table->index(['user_id', 'permission_id', 'resource_type']);

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
            $table->foreign('granted_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_permissions');
    }
};
