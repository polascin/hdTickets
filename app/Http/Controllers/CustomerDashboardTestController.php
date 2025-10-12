<?php declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\View\View;

/**
 * Test Controller for Customer Dashboard
 *
 * Provides a simple test route to verify the new dashboard works
 * with basic mock data.
 */
class CustomerDashboardTestController extends Controller
{
    /**
     * Display the test customer dashboard
     */
    public function index(): View
    {
        // Mock data for testing the dashboard
        $mockData = [
            'statistics' => [
                'available_tickets' => 1250,
                'new_today'         => 45,
                'unique_events'     => 89,
                'trend_indicators'  => [
                    'tickets_trend'  => 1,
                    'tickets_change' => 12.5,
                ],
            ],
            'recent_tickets' => [
                [
                    'id'         => 1,
                    'title'      => 'Lakers vs Warriors',
                    'venue'      => 'Staples Center',
                    'event_date' => now()->addDays(15)->toISOString(),
                    'price'      => 299.99,
                    'platform'   => 'StubHub',
                ],
                [
                    'id'         => 2,
                    'title'      => 'Manchester United vs Chelsea',
                    'venue'      => 'Old Trafford',
                    'event_date' => now()->addDays(8)->toISOString(),
                    'price'      => 185.50,
                    'platform'   => 'Ticketmaster',
                ],
                [
                    'id'         => 3,
                    'title'      => 'Super Bowl LVIII',
                    'venue'      => 'Las Vegas Stadium',
                    'event_date' => now()->addDays(30)->toISOString(),
                    'price'      => 2999.00,
                    'platform'   => 'NFL',
                ],
            ],
            'recommendations' => [
                [
                    'id'     => 1,
                    'title'  => 'Yankees vs Red Sox',
                    'reason' => 'Based on your viewing history',
                    'price'  => 125.00,
                ],
                [
                    'id'     => 2,
                    'title'  => 'NBA Finals Game 4',
                    'reason' => 'Price drop detected',
                    'price'  => 450.00,
                ],
            ],
            'alerts_data' => [
                'total_active'    => 5,
                'triggered_today' => 2,
                'price_drops'     => 3,
            ],
            'system_status' => [
                'scraping_active' => TRUE,
                'last_update'     => now()->subMinutes(5)->toISOString(),
            ],
            'notifications' => [
                'unread_count' => 3,
                'items'        => [
                    [
                        'id'         => 1,
                        'title'      => 'Price Alert Triggered',
                        'message'    => 'Lakers tickets dropped below $300',
                        'created_at' => now()->subHours(2)->toISOString(),
                    ],
                    [
                        'id'         => 2,
                        'title'      => 'New Event Added',
                        'message'    => 'Super Bowl tickets now available',
                        'created_at' => now()->subHours(4)->toISOString(),
                    ],
                ],
            ],
        ];

        return view('dashboard.customer-unified', $mockData);
    }
}
