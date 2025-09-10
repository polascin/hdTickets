<?php declare(strict_types=1);

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Re-encrypt phone numbers that are currently plain text
        $users = DB::table('users')->whereNotNull('phone')->get();

        foreach ($users as $user) {
            // Skip the user we just created which should already be encrypted
            if ($user->id >= 12) {
                continue;
            }

            try {
                // Try to decrypt - if it fails, it's probably plain text
                Crypt::decrypt($user->phone);
                echo "Phone for user ID {$user->id} is already encrypted, skipping\n";
            } catch (DecryptException) {
                // It's plain text, encrypt it
                if (! empty($user->phone)) {
                    $encrypted = Crypt::encrypt($user->phone);
                    DB::table('users')->where('id', $user->id)->update([
                        'phone' => $encrypted,
                    ]);
                    echo "Re-encrypted plain text phone for user ID: {$user->id}\n";
                }
            }
        }

        // Do the same for two_factor_secret
        $usersWithTwoFactor = DB::table('users')->whereNotNull('two_factor_secret')->get();

        foreach ($usersWithTwoFactor as $user) {
            if ($user->id >= 12) {
                continue;
            }

            try {
                Crypt::decrypt($user->two_factor_secret);
                echo "Two factor secret for user ID {$user->id} is already encrypted, skipping\n";
            } catch (DecryptException) {
                if (! empty($user->two_factor_secret)) {
                    $encrypted = Crypt::encrypt($user->two_factor_secret);
                    DB::table('users')->where('id', $user->id)->update([
                        'two_factor_secret' => $encrypted,
                    ]);
                    echo "Re-encrypted plain text two_factor_secret for user ID: {$user->id}\n";
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot reverse encryption safely
        echo "Cannot reverse encryption migration safely\n";
    }
};
