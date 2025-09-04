<?php declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AlertTrigger>
 */
class AlertTriggerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ticket_alert_id'   => \App\Models\TicketAlert::factory(),
            'scraped_ticket_id' => \App\Models\ScrapedTicket::factory(),
            'triggered_at'      => fake()->dateTimeBetween('-30 days', 'now'),
            'match_score'       => fake()->randomFloat(2, 0, 100),
            'trigger_reason'    => fake()->randomElement([
                'price_drop',
                'new_listing',
                'quantity_available',
                'section_match',
                'keyword_match',
            ]),
            'notification_sent' => fake()->boolean(80), // 80% chance notification was sent
            'user_acknowledged' => fake()->boolean(30), // 30% chance user acknowledged
        ];
    }
}
