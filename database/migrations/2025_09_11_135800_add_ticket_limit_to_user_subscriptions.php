<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_subscriptions', function (Blueprint $table): void {
            if (! Schema::hasColumn('user_subscriptions', 'ticket_limit')) {
                $table->unsignedInteger('ticket_limit')->nullable()->after('payment_plan_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_subscriptions', function (Blueprint $table): void {
            if (Schema::hasColumn('user_subscriptions', 'ticket_limit')) {
                $table->dropColumn('ticket_limit');
            }
        });
    }
};

