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
        Schema::create('resource_access', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('resource_type', 100);
            $table->string('resource_id', 100);
            $table->string('action', 100);
            $table->timestamp('granted_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->unsignedBigInteger('granted_by')->nullable();
            $table->json('context')->nullable();
            $table->timestamps();

            $table->index(['user_id']);
            $table->index(['resource_type']);
            $table->index(['resource_id']);
            $table->index(['action']);
            $table->index(['expires_at']);
            $table->index(['user_id', 'resource_type', 'resource_id', 'action'], 'user_resource_action_index');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('granted_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resource_access');
    }
};
