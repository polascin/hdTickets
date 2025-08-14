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
        Schema::table('users', function (Blueprint $table): void {
            // Add missing columns if they don't exist
            if (! Schema::hasColumn('users', 'is_scraper_account')) {
                $table->boolean('is_scraper_account')->default(FALSE)->after('is_active');
            }
            if (! Schema::hasColumn('users', 'preferences')) {
                $table->json('preferences')->nullable()->after('remember_token');
            }
            if (! Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('email_verified_at');
            }
            if (! Schema::hasColumn('users', 'surname')) {
                $table->string('surname')->nullable()->after('name');
            }
            if (! Schema::hasColumn('users', 'username')) {
                $table->string('username')->unique()->nullable()->after('surname');
            }

            // Handle email column - drop unique constraint first if it exists
            // Note: We'll keep it as string but make it longer
            $table->string('email', 500)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'is_scraper_account',
                'preferences',
                'last_login_at',
                'surname',
                'username',
            ]);
        });
    }
};
