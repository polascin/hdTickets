<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table): void {
            if (! Schema::hasColumn('tickets', 'price')) {
                $table->decimal('price', 10, 2)->nullable()->after('external_id');
            }
            if (! Schema::hasColumn('tickets', 'currency')) {
                $table->string('currency', 10)->nullable()->after('price');
            }
            if (! Schema::hasColumn('tickets', 'available_quantity')) {
                $table->unsignedInteger('available_quantity')->default(0)->after('currency');
            }
            if (! Schema::hasColumn('tickets', 'is_available')) {
                $table->boolean('is_available')->default(true)->after('available_quantity');
            }
            if (! Schema::hasColumn('tickets', 'venue')) {
                $table->string('venue')->nullable()->after('location');
            }
            if (! Schema::hasColumn('tickets', 'event_date')) {
                $table->dateTime('event_date')->nullable()->after('venue');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table): void {
            if (Schema::hasColumn('tickets', 'event_date')) {
                $table->dropColumn('event_date');
            }
            if (Schema::hasColumn('tickets', 'venue')) {
                $table->dropColumn('venue');
            }
            if (Schema::hasColumn('tickets', 'is_available')) {
                $table->dropColumn('is_available');
            }
            if (Schema::hasColumn('tickets', 'available_quantity')) {
                $table->dropColumn('available_quantity');
            }
            if (Schema::hasColumn('tickets', 'currency')) {
                $table->dropColumn('currency');
            }
            if (Schema::hasColumn('tickets', 'price')) {
                $table->dropColumn('price');
            }
        });
    }
};

