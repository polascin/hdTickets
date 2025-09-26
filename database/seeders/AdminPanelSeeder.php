<?php declare(strict_types=1);

namespace Database\Seeders;

use App\Models\EmailTemplate;
use App\Models\ScrapingSource;
use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

use function is_bool;

class AdminPanelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedSystemSettings();
        $this->seedScrapingSources();
        $this->seedEmailTemplates();
    }

    /**
     * Seed system settings
     */
    private function seedSystemSettings(): void
    {
        $settings = [
            // General Settings
            'general.platform_name'      => 'HD Tickets',
            'general.platform_url'       => config('app.url', 'http://localhost'),
            'general.support_email'      => 'support@hdtickets.com',
            'general.default_currency'   => 'USD',
            'general.timezone'           => 'America/New_York',
            'general.maintenance_mode'   => FALSE,
            'general.user_registration'  => TRUE,
            'general.email_verification' => TRUE,
            'general.debug_mode'         => config('app.debug', FALSE),
            'general.analytics_tracking' => TRUE,

            // API Configuration
            'api.stripe.publishable_key' => config('services.stripe.key', ''),
            'api.paypal.environment'     => 'sandbox',

            // Notification Settings
            'notifications.email.price_alerts'          => TRUE,
            'notifications.email.booking_confirmations' => TRUE,
            'notifications.email.account_updates'       => TRUE,
            'notifications.email.marketing'             => FALSE,
            'notifications.push.price_drops'            => TRUE,
            'notifications.push.new_events'             => TRUE,
            'notifications.push.booking_updates'        => TRUE,

            // Security Settings
            'security.session_timeout'       => 60,
            'security.password_min_length'   => 8,
            'security.two_factor_auth'       => FALSE,
            'security.login_attempts_limit'  => TRUE,
            'security.password_requirements' => TRUE,
            'security.api_rate_limit'        => 100,
            'security.api_key_required'      => TRUE,
            'security.ssl_required'          => TRUE,
        ];

        foreach ($settings as $key => $value) {
            $processedValue = $value;

            // Handle different value types
            if (is_bool($value)) {
                $processedValue = json_encode($value);
            } elseif (NULL === $value || $value === '') {
                $processedValue = '';
            }

            SystemSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $processedValue],
            );
        }

        $this->command->info('System settings seeded successfully.');
    }

    /**
     * Seed scraping sources
     */
    private function seedScrapingSources(): void
    {
        $sources = [
            [
                'name'       => 'StubHub',
                'base_url'   => 'https://www.stubhub.com',
                'rate_limit' => 60,
                'priority'   => 'high',
                'enabled'    => TRUE,
                'status'     => 'online',
                'config'     => [
                    'timeout'        => 30,
                    'retry_attempts' => 3,
                    'user_agent'     => 'HDTickets-Bot/1.0',
                ],
            ],
            [
                'name'       => 'Vivid Seats',
                'base_url'   => 'https://www.vividseats.com',
                'rate_limit' => 120,
                'priority'   => 'high',
                'enabled'    => TRUE,
                'status'     => 'online',
                'config'     => [
                    'timeout'        => 25,
                    'retry_attempts' => 2,
                    'user_agent'     => 'HDTickets-Bot/1.0',
                ],
            ],
            [
                'name'       => 'SeatGeek',
                'base_url'   => 'https://seatgeek.com',
                'rate_limit' => 90,
                'priority'   => 'medium',
                'enabled'    => TRUE,
                'status'     => 'online',
                'config'     => [
                    'timeout'        => 20,
                    'retry_attempts' => 3,
                    'user_agent'     => 'HDTickets-Bot/1.0',
                ],
            ],
            [
                'name'       => 'Ticketmaster',
                'base_url'   => 'https://www.ticketmaster.com',
                'rate_limit' => 30,
                'priority'   => 'medium',
                'enabled'    => FALSE,
                'status'     => 'offline',
                'config'     => [
                    'timeout'        => 35,
                    'retry_attempts' => 2,
                    'user_agent'     => 'HDTickets-Bot/1.0',
                ],
            ],
            [
                'name'       => 'TickPick',
                'base_url'   => 'https://www.tickpick.com',
                'rate_limit' => 75,
                'priority'   => 'low',
                'enabled'    => TRUE,
                'status'     => 'testing',
                'config'     => [
                    'timeout'        => 20,
                    'retry_attempts' => 1,
                    'user_agent'     => 'HDTickets-Bot/1.0',
                ],
            ],
        ];

        foreach ($sources as $sourceData) {
            ScrapingSource::updateOrCreate(
                ['name' => $sourceData['name']],
                $sourceData,
            );
        }

        $this->command->info('Scraping sources seeded successfully.');
    }

    /**
     * Seed email templates
     */
    private function seedEmailTemplates(): void
    {
        $templates = [
            [
                'key'     => 'welcome',
                'name'    => 'Welcome Email',
                'subject' => 'Welcome to {{platform_name}}!',
                'content' => '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
                    <h1 style="color: #10B981; text-align: center;">Welcome to {{platform_name}}!</h1>
                    <p>Hi {{user_name}},</p>
                    <p>Thank you for joining {{platform_name}}, your premier destination for sports event tickets!</p>
                    <p>With {{platform_name}}, you can:</p>
                    <ul>
                        <li>Monitor ticket prices in real-time</li>
                        <li>Get alerts when prices drop</li>
                        <li>Discover amazing sports events</li>
                        <li>Make secure purchases</li>
                    </ul>
                    <p>Start exploring now: <a href="{{platform_url}}/dashboard" style="color: #10B981;">Visit Your Dashboard</a></p>
                    <p>Best regards,<br>The {{platform_name}} Team</p>
                </div>',
                'variables' => [
                    'event_name' => 'Name of the sports event',
                    'venue_name' => 'Event venue name',
                    'event_date' => 'Event date and time',
                ],
                'active' => TRUE,
            ],
            [
                'key'     => 'price_alert',
                'name'    => 'Price Drop Alert',
                'subject' => 'Price Drop Alert: {{event_name}}',
                'content' => '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
                    <h1 style="color: #EF4444; text-align: center;">🚨 Price Drop Alert!</h1>
                    <p>Hi {{user_name}},</p>
                    <p>Great news! The ticket price for <strong>{{event_name}}</strong> has dropped!</p>
                    <div style="background: #FEF2F2; padding: 15px; border-radius: 8px; margin: 20px 0;">
                        <h2 style="color: #EF4444; margin: 0;">{{event_name}}</h2>
                        <p style="margin: 5px 0;"><strong>Venue:</strong> {{venue_name}}</p>
                        <p style="margin: 5px 0;"><strong>Date:</strong> {{event_date}}</p>
                        <p style="margin: 5px 0;"><strong>New Price:</strong> <span style="color: #10B981; font-size: 1.2em; font-weight: bold;">{{ticket_price}}</span></p>
                    </div>
                    <p style="text-align: center;">
                        <a href="{{platform_url}}/tickets" style="background: #10B981; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;">View Tickets Now</a>
                    </p>
                    <p>Don\'t wait - tickets at this price won\'t last long!</p>
                    <p>Best regards,<br>The {{platform_name}} Team</p>
                </div>',
                'variables' => [
                    'ticket_price' => 'Current ticket price',
                    'old_price'    => 'Previous ticket price',
                    'savings'      => 'Amount saved',
                    'venue_name'   => 'Event venue name',
                    'event_date'   => 'Event date and time',
                ],
                'active' => TRUE,
            ],
            [
                'key'     => 'booking_confirmation',
                'name'    => 'Booking Confirmation',
                'subject' => 'Booking Confirmed: {{event_name}}',
                'content' => '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
                    <h1 style="color: #10B981; text-align: center;">✅ Booking Confirmed!</h1>
                    <p>Hi {{user_name}},</p>
                    <p>Your ticket booking has been confirmed! Here are your details:</p>
                    <div style="background: #F0FDF4; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #10B981;">
                        <h2 style="color: #10B981; margin: 0 0 10px 0;">{{event_name}}</h2>
                        <p style="margin: 5px 0;"><strong>Venue:</strong> {{venue_name}}</p>
                        <p style="margin: 5px 0;"><strong>Date & Time:</strong> {{event_date}}</p>
                        <p style="margin: 5px 0;"><strong>Total Paid:</strong> {{ticket_price}}</p>
                        <p style="margin: 5px 0;"><strong>Booking Reference:</strong> {{booking_reference}}</p>
                    </div>
                    <p><strong>What\'s Next?</strong></p>
                    <ul>
                        <li>Your tickets will be delivered to your email</li>
                        <li>Arrive at the venue 30 minutes before the event</li>
                        <li>Bring a valid ID for entry</li>
                    </ul>
                    <p style="text-align: center;">
                        <a href="{{platform_url}}/bookings" style="background: #10B981; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;">View Booking Details</a>
                    </p>
                    <p>Thank you for choosing {{platform_name}}!</p>
                    <p>Best regards,<br>The {{platform_name}} Team</p>
                </div>',
                'variables' => [
                    'booking_reference' => 'Unique booking reference number',
                    'ticket_price'      => 'Total amount paid',
                    'venue_name'        => 'Event venue name',
                    'event_date'        => 'Event date and time',
                    'seat_details'      => 'Seat or section information',
                ],
                'active' => TRUE,
            ],
        ];

        foreach ($templates as $templateData) {
            EmailTemplate::updateOrCreate(
                ['key' => $templateData['key']],
                $templateData,
            );
        }

        $this->command->info('Email templates seeded successfully.');
    }
}
