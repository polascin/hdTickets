<?php declare(strict_types=1);

use App\Services\EncryptionService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $service = new EncryptionService();

        // Get all users
        $users = DB::table('users')->get();

        foreach ($users as $user) {
            $decryptedEmail = NULL;
            $originalEmail = $user->email;

            // Try multiple levels of decryption if needed
            $currentEmail = $originalEmail;
            $maxAttempts = 5; // Prevent infinite loops
            $attempts = 0;

            while ($attempts < $maxAttempts) {
                try {
                    $decrypted = $service->decrypt($currentEmail);

                    // Check if result looks like a valid email
                    if (filter_var($decrypted, FILTER_VALIDATE_EMAIL)) {
                        $decryptedEmail = $decrypted;

                        break;
                    }

                    // Otherwise, try to decrypt again (nested encryption)
                    $currentEmail = $decrypted;
                    $attempts++;
                } catch (Exception) {
                    // If decryption fails, check if current email is already valid
                    if (filter_var($currentEmail, FILTER_VALIDATE_EMAIL)) {
                        $decryptedEmail = $currentEmail;
                    }

                    break;
                }
            }

            // Update the user with decrypted email if we found one
            if ($decryptedEmail && $decryptedEmail !== $originalEmail) {
                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['email' => $decryptedEmail]);

                echo "Updated user {$user->id}: {$decryptedEmail}\n";
            } elseif (filter_var($originalEmail, FILTER_VALIDATE_EMAIL)) {
                echo "User {$user->id} email already plain text: {$originalEmail}\n";
            } else {
                echo "Could not decrypt email for user {$user->id}\n";
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot reverse email decryption
    }
};
