<?php

namespace Database\Factories;

use App\Models\TicketAlert;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TicketAlert>
 */
class TicketAlertFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TicketAlert::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'name' => $this->faker->sentence,
            'keywords' => $this->faker->words(3, true),
            'platform' => $this->faker->randomElement(['stubhub', 'ticketmaster', 'viagogo']),
            'max_price' => $this->faker->randomFloat(2, 50, 500),
            'currency' => 'USD',
            'filters' => json_encode(['criteria' => 'example']),
            'is_active' => true,
            'email_notifications' => true,
            'sms_notifications' => false,
            'matches_found' => $this->faker->numberBetween(0, 50),
            'last_triggered_at' => now()->subDays($this->faker->numberBetween(0, 30)),
        ];
    }
}
