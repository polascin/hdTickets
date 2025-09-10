<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserSubscription>
 */
class UserSubscriptionFactory extends Factory
{
    protected $model = UserSubscription::class;

    public function definition(): array
    {
        $startsAt = now();

        return [
            'user_id'             => User::factory(),
            'payment_plan_id'     => NULL,
            'status'              => 'active',
            'starts_at'           => $startsAt,
            'ends_at'             => $startsAt->copy()->addMonth(),
            'trial_ends_at'       => NULL,
            'stripe_subscription_id' => NULL,
            'stripe_customer_id'  => NULL,
            'amount_paid'         => 29.99,
            'payment_method'      => 'card',
            'metadata'            => NULL,
        ];
    }

    /**
     * Trial state variant.
     */
    public function trial(int $days = 7): self
    {
        return $this->state(function () use ($days) {
            $startsAt = now();

            return [
                'status'        => 'trial',
                'starts_at'     => $startsAt,
                'trial_ends_at' => $startsAt->copy()->addDays($days),
                'ends_at'       => NULL,
            ];
        });
    }

    /**
     * Expired subscription variant.
     */
    public function expired(): self
    {
        return $this->state(function () {
            return [
                'status'  => 'expired',
                'ends_at' => now()->subDay(),
            ];
        });
    }
}
