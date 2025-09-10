<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\PaymentPlan;
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

        $plan = PaymentPlan::query()->first() ?? PaymentPlan::factory()->create([
            'name'                     => 'Test Plan',
            'slug'                     => 'test-plan',
            'description'              => 'Test plan for factories',
            'price'                    => 29.99,
            'billing_cycle'            => 'monthly',
            'features'                 => json_encode(['feature a', 'feature b']),
            'max_tickets_per_month'    => 100,
            'max_concurrent_purchases' => 2,
            'max_platforms'            => 5,
            'priority_support'         => FALSE,
            'advanced_analytics'       => FALSE,
            'automated_purchasing'     => FALSE,
            'is_active'                => TRUE,
            'sort_order'               => 1,
        ]);

        return [
            'user_id'                => User::factory(),
            'payment_plan_id'        => $plan->id,
            'status'                 => 'active',
            'starts_at'              => $startsAt,
            'ends_at'                => $startsAt->copy()->addMonth(),
            'trial_ends_at'          => NULL,
            'stripe_subscription_id' => NULL,
            'stripe_customer_id'     => NULL,
            'amount_paid'            => 29.99,
            'payment_method'         => 'card',
            'metadata'               => NULL,
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
