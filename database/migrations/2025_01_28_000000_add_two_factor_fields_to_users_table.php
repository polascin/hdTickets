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
        // Only proceed if users table exists
        if (! Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table): void {
            // Check each column before adding
            if (! Schema::hasColumn('users', 'two_factor_enabled')) {
                $table->boolean('two_factor_enabled')->default(FALSE)->after('password');
            }
            if (! Schema::hasColumn('users', 'two_factor_secret')) {
                $table->text('two_factor_secret')->nullable()->after('two_factor_enabled');
            }
            if (! Schema::hasColumn('users', 'two_factor_confirmed_at')) {
                $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_secret');
            }
            if (! Schema::hasColumn('users', 'two_factor_recovery_codes')) {
                $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_confirmed_at');
            }

            if (! Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('two_factor_recovery_codes');
            }
            if (! Schema::hasColumn('users', 'last_login_ip')) {
                $table->string('last_login_ip')->nullable()->after('last_login_at');
            }
            if (! Schema::hasColumn('users', 'last_login_user_agent')) {
                $table->text('last_login_user_agent')->nullable()->after('last_login_ip');
            }
            if (! Schema::hasColumn('users', 'login_count')) {
                $table->integer('login_count')->default(0)->after('last_login_user_agent');
            }
            if (! Schema::hasColumn('users', 'failed_login_attempts')) {
                $table->integer('failed_login_attempts')->default(0)->after('login_count');
            }
            if (! Schema::hasColumn('users', 'locked_until')) {
                $table->timestamp('locked_until')->nullable()->after('failed_login_attempts');
            }

            if (! Schema::hasColumn('users', 'require_2fa')) {
                $table->boolean('require_2fa')->default(FALSE)->after('locked_until');
            }
            if (! Schema::hasColumn('users', 'trusted_devices')) {
                $table->json('trusted_devices')->nullable()->after('require_2fa');
            }
            if (! Schema::hasColumn('users', 'password_changed_at')) {
                $table->timestamp('password_changed_at')->nullable()->after('trusted_devices');
            }

            $table->index(['two_factor_enabled']);
            $table->index(['last_login_at']);
            $table->index(['failed_login_attempts']);
            $table->index(['locked_until']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'two_factor_enabled',
                'two_factor_secret',
                'two_factor_confirmed_at',
                'two_factor_recovery_codes',
                'last_login_at',
                'last_login_ip',
                'last_login_user_agent',
                'login_count',
                'failed_login_attempts',
                'locked_until',
                'require_2fa',
                'trusted_devices',
                'password_changed_at',
            ]);
        });
    }
};
