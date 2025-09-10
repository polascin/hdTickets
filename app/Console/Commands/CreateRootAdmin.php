<?php declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateRootAdmin extends Command
{
    /** The name and signature of the console command. */
    protected $signature = 'hdtickets:create-root-admin 
                            {--name=ticketmaster : Admin first name}
                            {--surname=admin : Admin surname}
                            {--username=ticketmaster : Admin username}
                            {--email=ticketmaster@hdtickets.admin : Admin email}
                            {--password=SecureAdminPass123! : Admin password}';

    /** The console command description. */
    protected $description = 'Create a root administrative user with full system access';

    /**
     * Execute the console command.
     */
    /**
     * Handle
     */
    public function handle(): int
    {
        $name = (string) $this->option('name');
        $surname = (string) $this->option('surname');
        $username = (string) $this->option('username');
        $email = (string) $this->option('email');
        $password = (string) $this->option('password');

        $this->info('🔐 HDTickets Root Admin Creator');
        $this->info('==================================');

        // Check if user already exists by email or username
        $existingUser = User::where('email', $email)->orWhere('username', $username)->first();
        if ($existingUser) {
            $this->warn("⚠️  User with email '{$email}' or username '{$username}' already exists!");

            if ($this->confirm('Do you want to update the existing user?')) {
                $existingUser->update([
                    'name'              => $name,
                    'surname'           => $surname,
                    'username'          => $username,
                    'password'          => Hash::make($password),
                    'role'              => 'admin',
                    'is_active'         => TRUE,
                    'email_verified_at' => now(),
                ]);

                $this->info("✅ Root admin user '{$name}' updated successfully!");
                $this->displayUserInfo($existingUser);

                return 0;
            }
            $this->info('Operation cancelled.');

            return 1;
        }

        // Create new root admin user
        try {
            $user = User::create([
                'name'              => $name,
                'surname'           => $surname,
                'username'          => $username,
                'email'             => $email,
                'password'          => Hash::make($password),
                'role'              => 'admin',
                'is_active'         => TRUE,
                'email_verified_at' => now(),
            ]);

            $this->info("✅ Root admin user '{$name}' created successfully!");
            $this->displayUserInfo($user);

            // Display login instructions
            $this->newLine();
            $this->info('📋 LOGIN CREDENTIALS:');
            $this->line("Email: {$email}");
            $this->line("Password: {$password}");
            $this->newLine();
            $this->warn('⚠️  SECURITY NOTICE: Please change the default password after first login!');

            return 0;
        } catch (Exception $e) {
            $this->error('❌ Failed to create root admin user: ' . $e->getMessage());

            return 1;
        }
    }

    /**
     * Display user information in a formatted table.
     */
    /**
     * DisplayUserInfo
     */
    private function displayUserInfo(User $user): void
    {
        $this->newLine();
        $this->info('👤 ADMIN USER DETAILS:');
        $this->table(
            ['Property', 'Value'],
            [
                ['ID', $user->id],
                ['Name', $user->name],
                ['Surname', $user->surname ?? 'N/A'],
                ['Username', $user->username ?? 'N/A'],
                ['Full Name', $user->full_name],
                ['Email', $user->email],
                ['Role', strtoupper($user->role)],
                ['Status', $user->is_active ? 'ACTIVE' : 'INACTIVE'],
                ['Verified', $user->email_verified_at ? 'YES' : 'NO'],
                ['Created', $user->created_at->format('Y-m-d H:i:s')],
            ],
        );

        $this->newLine();
        $this->info('🎯 ADMIN PRIVILEGES:');
        $this->line('• Full system access');
        $this->line('• User management');
        $this->line('• Ticket management');
        $this->line('• Scraping operations control');
        $this->line('• System configuration');
        $this->line('• Performance monitoring');
        $this->line('• Platform administration');
    }
}
