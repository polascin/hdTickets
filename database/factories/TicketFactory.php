<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\Category;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid'              => Str::uuid(),
            'requester_id'      => User::factory(),
            'assignee_id'       => User::factory(),
            'category_id'       => Category::factory(),
            'title'             => fake()->sentence,
            'description'       => fake()->paragraph,
            'status'            => fake()->randomElement(['open', 'in_progress', 'pending', 'resolved', 'closed']),
            'priority'          => fake()->randomElement(['low', 'medium', 'high', 'urgent']),
            'due_date'          => fake()->dateTimeBetween('+1 week', '+1 month'),
            'last_activity_at'  => now(),
            'platform'          => fake()->word,
            'external_id'       => fake()->unique()->numerify('EXT#####'),
            'price'             => fake()->randomFloat(2, 20, 300),
            'currency'          => fake()->currencyCode,
            'location'          => fake()->city,
            'venue'             => fake()->company,
            'event_date'        => fake()->dateTimeBetween('now', '+1 year'),
            'event_type'        => fake()->word,
            'performer_artist'  => fake()->name,
            'seat_details'      => fake()->sentence,
            'is_available'      => fake()->boolean,
            'ticket_url'        => fake()->url,
            'scraping_metadata' => json_encode(['source' => fake()->word, 'type' => fake()->word]),
        ];
    }
}
