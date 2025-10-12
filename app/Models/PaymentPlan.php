<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'billing_cycle',
        'features',
        'max_tickets_per_month',
        'max_concurrent_purchases',
        'max_platforms',
        'priority_support',
        'advanced_analytics',
        'automated_purchasing',
        'is_active',
        'sort_order',
        'stripe_price_id',
    ];

    /**
     * Get all subscriptions for this plan
     */
    /**
     * Subscriptions
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class);
    }

    /**
     * Get active subscriptions for this plan
     */
    /**
     * ActiveSubscriptions
     */
    public function activeSubscriptions(): HasMany
    {
        return $this->subscriptions()->where('status', 'active');
    }

    /**
     * Get only active plans
     *
     * @param mixed $query
     */
    /**
     * ScopeActive
     *
     * @param mixed $query
     */
    public function scopeActive($query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Order plans by sort order
     *
     * @param mixed $query
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('price');
    }

    /**
     * Check if plan has unlimited tickets
     */
    /**
     * Check if has  unlimited tickets
     */
    public function hasUnlimitedTickets(): bool
    {
        return $this->max_tickets_per_month === 0;
    }

    /**
     * Default payment plans seeder data
     */
    /**
     * Get  default plans
     */
    public static function getDefaultPlans(): array
    {
        return [
            [
                'name'          => 'Free Trial',
                'slug'          => 'free-trial',
                'description'   => 'Try HDTickets for free with limited features',
                'price'         => 0.00,
                'billing_cycle' => 'monthly',
                'features'      => [
                    'Monitor up to 5 events',
                    '1 platform access',
                    'Basic notifications',
                    'Community support',
                ],
                'max_tickets_per_month'    => 5,
                'max_concurrent_purchases' => 1,
                'max_platforms'            => 1,
                'priority_support'         => false,
                'advanced_analytics'       => false,
                'automated_purchasing'     => false,
                'sort_order'               => 1,
            ],
            [
                'name'          => 'Basic',
                'slug'          => 'basic',
                'description'   => 'Perfect for occasional ticket buyers',
                'price'         => 19.99,
                'billing_cycle' => 'monthly',
                'features'      => [
                    'Monitor up to 25 events',
                    '2 platform access',
                    'Email & push notifications',
                    'Basic analytics',
                    'Email support',
                ],
                'max_tickets_per_month'    => 25,
                'max_concurrent_purchases' => 2,
                'max_platforms'            => 2,
                'priority_support'         => false,
                'advanced_analytics'       => false,
                'automated_purchasing'     => false,
                'sort_order'               => 2,
            ],
            [
                'name'          => 'Pro',
                'slug'          => 'pro',
                'description'   => 'Best for regular ticket buyers and resellers',
                'price'         => 49.99,
                'billing_cycle' => 'monthly',
                'features'      => [
                    'Monitor unlimited events',
                    'All platforms access',
                    'Advanced notifications',
                    'Advanced analytics & reporting',
                    'Automated purchasing',
                    'Priority support',
                    'Custom alerts',
                ],
                'max_tickets_per_month'    => 0, // unlimited
                'max_concurrent_purchases' => 5,
                'max_platforms'            => 0, // unlimited
                'priority_support'         => true,
                'advanced_analytics'       => true,
                'automated_purchasing'     => true,
                'sort_order'               => 3,
            ],
            [
                'name'          => 'Enterprise',
                'slug'          => 'enterprise',
                'description'   => 'For teams and high-volume users',
                'price'         => 199.99,
                'billing_cycle' => 'monthly',
                'features'      => [
                    'Everything in Pro',
                    'Unlimited concurrent purchases',
                    'API access',
                    'Custom integrations',
                    'Dedicated account manager',
                    'White-label options',
                    'SLA guarantee',
                ],
                'max_tickets_per_month'    => 0, // unlimited
                'max_concurrent_purchases' => 0, // unlimited
                'max_platforms'            => 0, // unlimited
                'priority_support'         => true,
                'advanced_analytics'       => true,
                'automated_purchasing'     => true,
                'sort_order'               => 4,
            ],
        ];
    }

    /**
     * Get  formatted price attribute
     */
    protected function formattedPrice(): Attribute
    {
        return Attribute::make(get: function (): string {
            if ($this->price === 0) {
                return 'Free';
            }
            $currency = '$';
            // You can make this configurable
            $price = number_format((float) $this->price, 2);

            return match ($this->billing_cycle) {
                'monthly'  => "{$currency}{$price}/month",
                'yearly'   => "{$currency}{$price}/year",
                'lifetime' => "{$currency}{$price} one-time",
                default    => "{$currency}{$price}",
            };
        });
    }

    /**
     * Get  monthly equivalent attribute
     */
    protected function monthlyEquivalent(): Attribute
    {
        return Attribute::make(get: fn () => match ($this->billing_cycle) {
            'monthly'  => $this->price,
            'yearly'   => $this->price / 12,
            'lifetime' => $this->price / 60,
            default    => $this->price,
        });
    }

    /**
     * Get  features list attribute
     */
    protected function featuresList(): Attribute
    {
        return Attribute::make(get: function (): string {
            if (empty($this->features)) {
                return '';
            }
            $features = collect($this->features)->map(fn ($feature): string => "<li class='flex items-center'><svg class='w-4 h-4 text-green-500 mr-2' fill='currentColor' viewBox='0 0 20 20'><path fill-rule='evenodd' d='M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z' clip-rule='evenodd'></path></svg>{$feature}</li>")->join('');

            return "<ul class='space-y-2'>{$features}</ul>";
        });
    }

    protected function casts(): array
    {
        return [
            'features'             => 'array',
            'price'                => 'decimal:2',
            'priority_support'     => 'boolean',
            'advanced_analytics'   => 'boolean',
            'automated_purchasing' => 'boolean',
            'is_active'            => 'boolean',
        ];
    }
}
