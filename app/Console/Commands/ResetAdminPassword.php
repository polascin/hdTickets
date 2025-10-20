<?php declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class ResetAdminPassword extends Command
{
    /** The name and signature of the console command. */
    protected $signature = 'admin:reset-password {email} {password}';

    /** The console command description. */
    protected $description = 'Reset admin user password';

    /**
     * Execute the console command.
     */
    /**
     * Handle
     */
    public function handle(): int
    {
        $email = (string) $this->argument('email');
        $password = (string) $this->argument('password');

        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error("User with email {$email} not found.");

            return Command::FAILURE;
        }

        $user->password = Hash::make($password);
        $user->save();

        $this->info("Password reset successfully for {$email}");
        $this->info("New password: {$password}");
        $this->info("Role: {$user->role}");
        $this->info('Is Admin: ' . ($user->isAdmin() ? 'Yes' : 'No'));

        return Command::SUCCESS;
    }
}
