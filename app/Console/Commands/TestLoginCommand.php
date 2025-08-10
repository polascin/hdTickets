<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class TestLoginCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:login {email} {password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test login credentials for HD Tickets users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');

        $this->info("Testing login for: {$email}");

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error('✗ User not found!');
            return 1;
        }

        if (!Hash::check($password, $user->password)) {
            $this->error('✗ Invalid password!');
            return 1;
        }

        if (!$user->is_active) {
            $this->warn('⚠ User account is inactive!');
        }

        if (!$user->email_verified_at) {
            $this->warn('⚠ Email is not verified!');
        }

        $this->info('✓ Login successful!');
        $this->table(
            ['Field', 'Value'],
            [
                ['Name', $user->name . ' ' . $user->surname],
                ['Email', $user->email],
                ['Username', $user->username],
                ['Role', strtoupper($user->role)],
                ['Active', $user->is_active ? 'Yes' : 'No'],
                ['Email Verified', $user->email_verified_at ? 'Yes' : 'No'],
                ['2FA Enabled', $user->two_factor_enabled ? 'Yes' : 'No'],
                ['Last Login', $user->last_login_at ? $user->last_login_at->format('Y-m-d H:i:s') : 'Never'],
            ]
        );

        return 0;
    }
}
