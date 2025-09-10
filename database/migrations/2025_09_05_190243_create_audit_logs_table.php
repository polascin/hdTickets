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
        Schema::create('audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action', 100);
            $table->string('resource_type', 100)->nullable();
            $table->string('resource_id', 100)->nullable();
            $table->json('changes')->nullable();
            $table->string('ip_address', 45);
            $table->text('user_agent')->nullable();
            $table->string('session_id')->nullable();
            $table->timestamp('performed_at');
            $table->timestamps();

            $table->index(['user_id']);
            $table->index(['action']);
            $table->index(['resource_type']);
            $table->index(['resource_id']);
            $table->index(['performed_at']);
            $table->index(['ip_address']);
            $table->index(['session_id']);
            $table->index(['user_id', 'action', 'performed_at']);

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
