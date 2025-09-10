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
        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('display_name', 255);
            $table->text('description')->nullable();
            $table->boolean('is_system_role')->default(FALSE);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['name']);
            $table->index(['is_system_role']);
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
