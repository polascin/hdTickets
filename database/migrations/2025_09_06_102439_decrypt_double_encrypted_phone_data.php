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
        // Fix double-encrypted phone data
        $users = DB::table('users')->whereNotNull('phone')->get();

        foreach ($users as $user) {
            try {
                // Try to decrypt the phone number
                $decrypted = Crypt::decrypt($user->phone);

                // Check if it's still encrypted (double-encrypted)
                if (str_starts_with((string) $decrypted, 'eyJ')) { // Base64 encoded encrypted data
                    try {
                        // Decrypt again to get the original value
                        $originalPhone = Crypt::decrypt($decrypted);

                        // Update with the original value (Laravel casts will encrypt it)
                        DB::table('users')->where('id', $user->id)->update([
                            'phone' => $originalPhone,
                        ]);

                        echo "Fixed double-encrypted phone for user ID: {$user->id}\n";
                    } catch (DecryptException) {
                        echo "Could not decrypt inner phone for user ID: {$user->id}\n";
                    }
                } else {
                    // Single encrypted, update with decrypted value so Laravel casts can re-encrypt properly
                    DB::table('users')->where('id', $user->id)->update([
                        'phone' => $decrypted,
                    ]);

                    echo "Fixed single-encrypted phone for user ID: {$user->id}\n";
                }
            } catch (DecryptException) {
                // Skip invalid encrypted data
                echo "Could not decrypt phone for user ID: {$user->id}, skipping\n";
            }
        }

        // Also fix two_factor_secret if needed
        $usersWithTwoFactor = DB::table('users')->whereNotNull('two_factor_secret')->get();

        foreach ($usersWithTwoFactor as $user) {
            try {
                $decrypted = Crypt::decrypt($user->two_factor_secret);

                if (str_starts_with((string) $decrypted, 'eyJ')) {
                    try {
                        $originalSecret = Crypt::decrypt($decrypted);
                        DB::table('users')->where('id', $user->id)->update([
                            'two_factor_secret' => $originalSecret,
                        ]);
                        echo "Fixed double-encrypted two_factor_secret for user ID: {$user->id}\n";
                    } catch (DecryptException) {
                        echo "Could not decrypt inner two_factor_secret for user ID: {$user->id}\n";
                    }
                } else {
                    DB::table('users')->where('id', $user->id)->update([
                        'two_factor_secret' => $decrypted,
                    ]);
                    echo "Fixed single-encrypted two_factor_secret for user ID: {$user->id}\n";
                }
            } catch (DecryptException) {
                echo "Could not decrypt two_factor_secret for user ID: {$user->id}, skipping\n";
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot reverse this migration safely
        echo "Cannot reverse decryption migration safely\n";
    }
};
