<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\Category;
use App\Models\ScrapedTicket;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ScrapedTicket>
 */
class ScrapedTicketFactory extends Factory
{
    protected $model = ScrapedTicket::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $platforms = ['stubhub', 'ticketmaster', 'viagogo', 'funzone', 'seatgeek', 'vivid_seats'];
        $currencies = ['USD', 'GBP', 'EUR', 'CZK', 'SKK'];
        $sports = ['football', 'basketball', 'tennis', 'hockey', 'baseball'];
        $availability = ['high', 'medium', 'low', 'sold_out'];

        // Generate realistic team names
        $teams = [
            'Manchester United', 'Liverpool', 'Arsenal', 'Chelsea', 'Manchester City',
            'Tottenham', 'Barcelona', 'Real Madrid', 'Bayern Munich', 'Juventus',
            'Slovan Bratislava', 'Sparta Praha', 'AC Milan', 'Inter Milan', 'PSG',
        ];

        $venues = [
            'Old Trafford', 'Anfield', 'Emirates Stadium', 'Stamford Bridge', 'Etihad Stadium',
            'Wembley Stadium', 'Camp Nou', 'Santiago Bernabéu', 'Allianz Arena', 'Juventus Stadium',
            'Tehelné pole', 'Letná Stadium', 'San Siro', 'Parc des Princes',
        ];

        $cities = [
            'Manchester', 'Liverpool', 'London', 'Barcelona', 'Madrid', 'Munich', 'Turin',
            'Bratislava', 'Praha', 'Milan', 'Paris', 'Rome', 'Amsterdam', 'Berlin',
        ];

        $team1 = fake()->randomElement($teams);
        $team2 = fake()->randomElement(array_diff($teams, [$team1]));
        $title = $team1 . ' vs ' . $team2;

        $minPrice = fake()->randomFloat(2, 25, 200);
        $maxPrice = fake()->randomFloat(2, $minPrice + 50, $minPrice * 3);

        return [
            'uuid'           => fake()->uuid(),
            'platform'       => fake()->randomElement($platforms),
            'title'          => $title,
            'venue'          => fake()->randomElement($venues),
            'location'       => fake()->randomElement($cities),
            'event_date'     => fake()->dateTimeBetween('now', '+6 months'),
            'min_price'      => $minPrice,
            'max_price'      => $maxPrice,
            'currency'       => fake()->randomElement($currencies),
            'availability'   => fake()->randomElement($availability),
            'is_available'   => fake()->boolean(80), // 80% chance of being available
            'is_high_demand' => fake()->boolean(30), // 30% chance of high demand
            'status'         => fake()->randomElement(['active', 'sold_out', 'cancelled']),
            'ticket_url'     => fake()->url(),
            'search_keyword' => strtolower($team1),
            'metadata'       => [
                'section'        => fake()->randomElement(['Lower Tier', 'Upper Tier', 'VIP', 'General Admission']),
                'row'            => fake()->numberBetween(1, 40),
                'seats'          => fake()->numberBetween(1, 4),
                'seller_type'    => fake()->randomElement(['official', 'reseller', 'individual']),
                'original_price' => fake()->randomFloat(2, 50, 300),
                'tags'           => fake()->randomElements(['premium', 'discounted', 'limited', 'popular'], fake()->numberBetween(0, 3)),
            ],
            'scraped_at'  => fake()->dateTimeBetween('-1 week', 'now'),
            'category_id' => NULL, // Will be set by relationship if needed
        ];
    }

    /**
     * Indicate that the ticket is high demand.
     */
    public function highDemand(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_high_demand' => TRUE,
            'availability'   => 'low',
            'min_price'      => fake()->randomFloat(2, 200, 500),
            'max_price'      => fake()->randomFloat(2, 500, 1000),
        ]);
    }

    /**
     * Indicate that the ticket is sold out.
     */
    public function soldOut(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_available' => FALSE,
            'availability' => 'sold_out',
            'status'       => 'sold_out',
        ]);
    }

    /**
     * Indicate that the ticket is from a specific platform.
     */
    public function fromPlatform(string $platform): static
    {
        return $this->state(fn (array $attributes) => [
            'platform' => $platform,
        ]);
    }

    /**
     * Indicate that the ticket is for a specific event.
     */
    public function forEvent(string $title, ?string $venue = NULL): static
    {
        return $this->state(fn (array $attributes) => [
            'title'          => $title,
            'venue'          => $venue ?? fake()->randomElement(['Old Trafford', 'Anfield', 'Emirates Stadium']),
            'search_keyword' => strtolower(explode(' vs ', $title)[0] ?? explode(' ', $title)[0] ?? $title),
        ]);
    }

    /**
     * Indicate that the ticket is in a specific price range.
     */
    public function priceRange(float $min, float $max): static
    {
        return $this->state(fn (array $attributes) => [
            'min_price' => $min,
            'max_price' => $max,
        ]);
    }

    /**
     * Indicate that the ticket is recent (scraped within last 24 hours).
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'scraped_at' => fake()->dateTimeBetween('-24 hours', 'now'),
        ]);
    }

    /**
     * Indicate that the ticket belongs to a category.
     */
    public function withCategory(): static
    {
        return $this->state(fn (array $attributes) => [
            'category_id' => Category::factory(),
        ]);
    }
}
