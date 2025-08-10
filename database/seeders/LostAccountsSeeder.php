<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class LostAccountsSeeder extends Seeder
{
    /**
     * Run the database seeds to restore lost accounts.
     */
    public function run(): void
    {
        $this->command->info('Creating lost user accounts...');

        // 1. Admin Account
        $admin = User::firstOrCreate(
            ['email' => 'admin@hdtickets.com'],
            [
                'name' => 'HD Tickets',
                'surname' => 'Administrator',
                'username' => 'admin',
                'email' => 'admin@hdtickets.com',
                'password' => Hash::make('HDTickets2025!'),
                'role' => User::ROLE_ADMIN,
                'is_active' => true,
                'email_verified_at' => Carbon::now(),
                'timezone' => 'UTC',
                'language' => 'en',
                'created_by_type' => 'system',
                'email_notifications' => true,
                'push_notifications' => true,
                'require_2fa' => false,
                'trusted_devices' => json_encode([]),
                'custom_permissions' => json_encode([]),
                'password_changed_at' => Carbon::now(),
                'login_count' => 0,
                'failed_login_attempts' => 0,
                'bio' => 'System Administrator for HD Tickets platform',
                'theme_preference' => 'auto',
                'display_density' => 'comfortable',
                'sidebar_collapsed' => false,
                'dashboard_auto_refresh' => true,
                'dashboard_refresh_interval' => 300,
                'currency_preference' => 'USD',
                'is_scraper_account' => false,
                'deletion_protection_enabled' => true,
                'deletion_attempt_count' => 0,
                'has_trial_used' => false,
            ]
        );

        if ($admin->wasRecentlyCreated) {
            $this->command->info('✓ Created admin@hdtickets.com');
        } else {
            $this->command->info('✓ admin@hdtickets.com already exists');
        }

        // 2. Agent Account
        $agent = User::firstOrCreate(
            ['email' => 'agent@hdtickets.com'],
            [
                'name' => 'HD Tickets',
                'surname' => 'Agent',
                'username' => 'agent',
                'email' => 'agent@hdtickets.com',
                'password' => Hash::make('HDAgent2025!'),
                'role' => User::ROLE_AGENT,
                'is_active' => true,
                'email_verified_at' => Carbon::now(),
                'timezone' => 'UTC',
                'language' => 'en',
                'created_by_type' => 'system',
                'email_notifications' => true,
                'push_notifications' => true,
                'require_2fa' => false,
                'trusted_devices' => json_encode([]),
                'custom_permissions' => json_encode([]),
                'password_changed_at' => Carbon::now(),
                'login_count' => 0,
                'failed_login_attempts' => 0,
                'bio' => 'Ticket selection and purchasing agent',
                'theme_preference' => 'auto',
                'display_density' => 'comfortable',
                'sidebar_collapsed' => false,
                'dashboard_auto_refresh' => true,
                'dashboard_refresh_interval' => 300,
                'currency_preference' => 'USD',
                'is_scraper_account' => false,
                'deletion_protection_enabled' => true,
                'deletion_attempt_count' => 0,
                'has_trial_used' => false,
            ]
        );

        if ($agent->wasRecentlyCreated) {
            $this->command->info('✓ Created agent@hdtickets.com');
        } else {
            $this->command->info('✓ agent@hdtickets.com already exists');
        }

        // 3. Customer Account  
        $customer = User::firstOrCreate(
            ['email' => 'customer@hdtickets.com'],
            [
                'name' => 'HD Tickets',
                'surname' => 'Customer',
                'username' => 'customer',
                'email' => 'customer@hdtickets.com',
                'password' => Hash::make('HDCustomer2025!'),
                'role' => User::ROLE_CUSTOMER,
                'is_active' => true,
                'email_verified_at' => Carbon::now(),
                'timezone' => 'UTC',
                'language' => 'en',
                'created_by_type' => 'system',
                'email_notifications' => true,
                'push_notifications' => false, // Customer has push notifications disabled by default
                'require_2fa' => false,
                'trusted_devices' => json_encode([]),
                'custom_permissions' => json_encode([]),
                'password_changed_at' => Carbon::now(),
                'login_count' => 0,
                'failed_login_attempts' => 0,
                'bio' => 'Customer account for HD Tickets platform',
                'theme_preference' => 'light', // Customer uses light theme by default
                'display_density' => 'comfortable',
                'sidebar_collapsed' => false,
                'dashboard_auto_refresh' => false,
                'dashboard_refresh_interval' => 600,
                'currency_preference' => 'USD',
                'is_scraper_account' => false,
                'deletion_protection_enabled' => true,
                'deletion_attempt_count' => 0,
                'has_trial_used' => false,
            ]
        );

        if ($customer->wasRecentlyCreated) {
            $this->command->info('✓ Created customer@hdtickets.com');
        } else {
            $this->command->info('✓ customer@hdtickets.com already exists');
        }

        $this->command->info('');
        $this->command->info('📧 Account Details:');
        $this->command->table(
            ['Email', 'Role', 'Username', 'Default Password'],
            [
                ['admin@hdtickets.com', 'Admin', 'admin', 'HDTickets2025!'],
                ['agent@hdtickets.com', 'Agent', 'agent', 'HDAgent2025!'],
                ['customer@hdtickets.com', 'Customer', 'customer', 'HDCustomer2025!'],
            ]
        );

        $this->command->info('');
        $this->command->warn('🔒 SECURITY NOTE: Please change these default passwords immediately!');
        $this->command->info('💡 TIP: You can enable 2FA for enhanced security on these accounts.');
        $this->command->info('');

        // Set up preferences using the preferences JSON column
        $this->setUserPreferences($admin, $agent, $customer);
    }

    /**
     * Set user preferences using the preferences JSON column
     */
    private function setUserPreferences(User $admin, User $agent, User $customer): void
    {
        $this->command->info('Setting up default preferences...');

        // Admin preferences - full access and monitoring
        $adminPrefs = [
            'notifications' => [
                'email_alerts' => true,
                'system_notifications' => true,
                'security_alerts' => true,
                'user_activity_reports' => true,
                'performance_reports' => true,
            ],
            'display' => [
                'theme' => 'professional',
                'dashboard_layout' => 'admin_full',
                'items_per_page' => 50,
                'show_advanced_filters' => true,
            ],
            'alerts' => [
                'system_errors' => true,
                'security_warnings' => true,
                'performance_issues' => true,
                'user_login_failures' => true,
            ]
        ];
        $admin->update(['preferences' => $adminPrefs]);

        // Agent preferences - ticket and monitoring focused
        $agentPrefs = [
            'notifications' => [
                'ticket_alerts' => true,
                'purchase_confirmations' => true,
                'monitoring_alerts' => true,
                'email_alerts' => true,
            ],
            'display' => [
                'theme' => 'agent',
                'dashboard_layout' => 'agent_tickets',
                'items_per_page' => 25,
                'show_ticket_details' => true,
            ],
            'alerts' => [
                'ticket_availability' => true,
                'price_changes' => true,
                'purchase_deadlines' => true,
            ]
        ];
        $agent->update(['preferences' => $agentPrefs]);

        // Customer preferences - basic access
        $customerPrefs = [
            'notifications' => [
                'email_updates' => true,
                'ticket_alerts' => false,
                'promotional_emails' => false,
            ],
            'display' => [
                'theme' => 'customer',
                'dashboard_layout' => 'customer_simple',
                'items_per_page' => 10,
            ],
            'alerts' => [
                'account_changes' => true,
            ]
        ];
        $customer->update(['preferences' => $customerPrefs]);

        $this->command->info('✓ Default preferences configured for all accounts');
    }
}
