<?php

namespace App\Services;

use App\Models\User;
use App\Models\Ticket;
use App\Models\Event;
use App\Models\UserSubscription;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WelcomePageService
{
    /**
     * Get comprehensive welcome page data
     *
     * @param array $options
     * @return array
     */
    public function getWelcomePageData(array $options = [])
    {
        $data = [];
        
        // Include statistics if requested
        if ($options['include_stats'] ?? true) {
            $data['stats'] = $this->getStatistics();
        }
        
        // Include pricing information if requested
        if ($options['include_pricing'] ?? true) {
            $data['pricing'] = $this->getPricingInformation();
        }
        
        // Include features list if requested
        if ($options['include_features'] ?? true) {
            $data['features'] = $this->getFeaturesList();
        }
        
        // Include legal documents if requested
        if ($options['include_legal_docs'] ?? true) {
            $data['legal_docs'] = $this->getLegalDocuments();
        }
        
        // Include role-specific information
        $data['roles'] = $this->getRoleInformation();
        
        // Include security features
        $data['security_features'] = $this->getSecurityFeatures();
        
        // Include A/B test variant specific data
        if (!empty($options['ab_variant'])) {
            $data = $this->applyABTestVariant($data, $options['ab_variant']);
        }
        
        return $data;
    }

    /**
     * Get platform statistics
     *
     * @return array
     */
    public function getStatistics()
    {
        return Cache::remember('welcome_page_stats', 600, function () {
            try {
                return [
                    'platforms' => $this->getPlatformCount(),
                    'monitoring' => '24/7',
                    'users' => $this->getFormattedUserCount(),
                    'events_monitored' => $this->getFormattedEventCount(),
                    'tickets_tracked' => $this->getFormattedTicketCount(),
                    'active_subscriptions' => $this->getActiveSubscriptionCount(),
                    'success_rate' => $this->getSuccessRate(),
                    'avg_savings' => $this->getAverageSavings()
                ];
            } catch (\Exception $e) {
                Log::error('Error fetching welcome page statistics: ' . $e->getMessage());
                return $this->getFallbackStats();
            }
        });
    }

    /**
     * Get pricing information
     *
     * @return array
     */
    public function getPricingInformation()
    {
        return Cache::remember('welcome_page_pricing', 1800, function () {
            return [
                'monthly_price' => config('subscription.default_monthly_fee', 29.99),
                'yearly_price' => config('subscription.yearly_fee', 299.99),
                'free_trial_days' => config('subscription.free_access_days', 7),
                'default_ticket_limit' => config('subscription.default_ticket_limit', 100),
                'currency' => config('app.currency', 'USD'),
                'processing_fee_rate' => config('purchase.processing_fee_rate', 0.03),
                'service_fee' => config('purchase.service_fee', 2.50),
                'agent_unlimited' => config('subscription.agent_unlimited_tickets', true),
                'no_money_back_guarantee' => config('service.no_money_back_guarantee', true),
                'service_provided_as_is' => config('service.provided_as_is', true)
            ];
        });
    }

    /**
     * Get features list by category
     *
     * @return array
     */
    public function getFeaturesList()
    {
        return Cache::remember('welcome_page_features', 3600, function () {
            return [
                'role_based_access' => [
                    'title' => 'Role-Based Access Control',
                    'description' => 'Customer, Agent, Admin & Scraper roles with tailored permissions and features',
                    'icon' => '👥',
                    'features' => [
                        'Customer role with subscription-based limits',
                        'Agent role with unlimited access',
                        'Administrator role with full system control',
                        'Scraper role for automated operations'
                    ]
                ],
                'subscription_system' => [
                    'title' => 'Flexible Subscription System',
                    'description' => 'Monthly plans with configurable limits, 7-day free trial, and unlimited agent access',
                    'icon' => '💳',
                    'features' => [
                        '7-day free trial period',
                        'Monthly and yearly billing options',
                        'Configurable ticket limits',
                        'Stripe and PayPal integration'
                    ]
                ],
                'legal_compliance' => [
                    'title' => 'Legal Compliance & GDPR',
                    'description' => 'GDPR compliant with mandatory legal document acceptance and comprehensive audit trails',
                    'icon' => '⚖️',
                    'features' => [
                        'GDPR compliance and data protection',
                        'Mandatory legal document acceptance',
                        'Comprehensive audit trail logging',
                        'Privacy by design implementation'
                    ]
                ],
                'enhanced_security' => [
                    'title' => 'Enterprise-Grade Security',
                    'description' => '2FA, device fingerprinting, email/SMS verification, and secure payment processing',
                    'icon' => '🔒',
                    'features' => [
                        'Multi-factor authentication (2FA)',
                        'Device fingerprinting and geolocation',
                        'Enhanced login security',
                        'Secure payment processing (PCI DSS)'
                    ]
                ],
                'monitoring_automation' => [
                    'title' => 'Advanced Monitoring',
                    'description' => 'Real-time monitoring across 50+ platforms with intelligent alerts and automation',
                    'icon' => '📊',
                    'features' => [
                        '50+ integrated ticket platforms',
                        '24/7 real-time monitoring',
                        'Intelligent price alerts',
                        'Automated purchase capabilities'
                    ]
                ]
            ];
        });
    }

    /**
     * Get legal documents information
     *
     * @return array
     */
    public function getLegalDocuments()
    {
        return [
            'terms_of_service' => [
                'title' => 'Terms of Service',
                'url' => route('legal.terms-of-service'),
                'description' => 'Service conditions and user obligations',
                'required' => true,
                'version' => '2.1',
                'last_updated' => '2024-01-15'
            ],
            'service_disclaimer' => [
                'title' => 'Service Disclaimer',
                'url' => route('legal.disclaimer'),
                'description' => 'Service limitations and warranty disclaimers',
                'required' => true,
                'version' => '1.3',
                'last_updated' => '2024-01-15'
            ],
            'privacy_policy' => [
                'title' => 'Privacy Policy',
                'url' => route('legal.privacy-policy'),
                'description' => 'Data collection and privacy practices',
                'required' => true,
                'version' => '2.0',
                'last_updated' => '2024-01-10'
            ],
            'data_processing_agreement' => [
                'title' => 'Data Processing Agreement',
                'url' => route('legal.data-processing-agreement'),
                'description' => 'GDPR compliance and data handling',
                'required' => true,
                'version' => '1.2',
                'last_updated' => '2024-01-08'
            ],
            'cookie_policy' => [
                'title' => 'Cookie Policy',
                'url' => route('legal.cookie-policy'),
                'description' => 'Cookie usage and tracking information',
                'required' => true,
                'version' => '1.1',
                'last_updated' => '2024-01-05'
            ],
            'acceptable_use_policy' => [
                'title' => 'Acceptable Use Policy',
                'url' => route('legal.acceptable-use-policy'),
                'description' => 'Platform usage guidelines and restrictions',
                'required' => true,
                'version' => '1.0',
                'last_updated' => '2024-01-01'
            ]
        ];
    }

    /**
     * Get role-specific information
     *
     * @return array
     */
    public function getRoleInformation()
    {
        return [
            'customer' => [
                'name' => 'Customer',
                'icon' => '👤',
                'price' => '$29.99',
                'period' => '/month',
                'description' => 'Perfect for regular ticket buyers',
                'badge' => 'Most Popular',
                'badge_type' => 'success',
                'features' => [
                    '7-day free trial',
                    '100 tickets per month',
                    'Email verification required',
                    'Optional 2FA security',
                    'Legal document compliance',
                    'Purchase access with limits',
                    'Basic monitoring features'
                ]
            ],
            'agent' => [
                'name' => 'Agent',
                'icon' => '🏆',
                'price' => 'Unlimited',
                'period' => 'Access',
                'description' => 'For ticket professionals & agents',
                'badge' => 'Professional',
                'badge_type' => 'warning',
                'features' => [
                    'Unlimited ticket access',
                    'No subscription required',
                    'Advanced monitoring tools',
                    'Performance analytics',
                    'Priority support',
                    'Automation features',
                    'Professional tools'
                ]
            ],
            'admin' => [
                'name' => 'Administrator',
                'icon' => '👑',
                'price' => 'Full',
                'period' => 'Control',
                'description' => 'Enterprise administration control',
                'badge' => 'Enterprise',
                'badge_type' => 'danger',
                'features' => [
                    'Complete system access',
                    'User management capabilities',
                    'Financial reports',
                    'Analytics dashboard',
                    'API management',
                    'System configuration',
                    'White-label options'
                ]
            ],
            'scraper' => [
                'name' => 'Scraper',
                'icon' => '🤖',
                'price' => 'System',
                'period' => 'Only',
                'description' => 'Automated operations only',
                'badge' => 'System Role',
                'badge_type' => 'info',
                'features' => [
                    'System-only access',
                    'Cannot login to web interface',
                    'Automated ticket scraping',
                    'API-based operations',
                    'Data collection tasks',
                    'Managed by administrators'
                ]
            ]
        ];
    }

    /**
     * Get security features information
     *
     * @return array
     */
    public function getSecurityFeatures()
    {
        return [
            'multi_factor_auth' => [
                'title' => 'Multi-Factor Authentication',
                'description' => 'Google Authenticator 2FA, SMS verification, and backup codes',
                'icon' => '🔐',
                'features' => [
                    'Google Authenticator support',
                    'SMS verification codes',
                    'Emergency backup codes',
                    'Device trust management'
                ]
            ],
            'enhanced_login' => [
                'title' => 'Enhanced Login Security',
                'description' => 'Device fingerprinting and automated tool detection',
                'icon' => '🛡️',
                'features' => [
                    'Device fingerprinting',
                    'Geolocation verification',
                    'Automated tool detection',
                    'Failed attempt monitoring'
                ]
            ],
            'data_encryption' => [
                'title' => 'Data Encryption',
                'description' => 'AES-256 encryption and secure key management',
                'icon' => '🔒',
                'features' => [
                    'AES-256 encryption',
                    'TLS 1.3 in transit',
                    'Payment tokenization',
                    'Secure key management'
                ]
            ],
            'payment_security' => [
                'title' => 'Secure Payment Processing',
                'description' => 'PCI DSS compliant with Stripe and PayPal integration',
                'icon' => '💳',
                'features' => [
                    'PCI DSS compliance',
                    'Stripe & PayPal integration',
                    'No stored payment data',
                    'Fraud protection'
                ]
            ]
        ];
    }

    /**
     * Get user subscription information
     *
     * @param User $user
     * @return array
     */
    public function getUserSubscriptionInfo(User $user)
    {
        try {
            $subscription = $user->subscription()->latest()->first();
            
            return [
                'has_active_subscription' => $user->hasActiveSubscription(),
                'is_in_trial' => $user->isInTrialPeriod(),
                'trial_ends_at' => $user->trial_ends_at,
                'subscription_ends_at' => $subscription?->ends_at,
                'monthly_ticket_usage' => $user->getMonthlyTicketUsage(),
                'ticket_limit' => $user->getTicketLimit(),
                'remaining_tickets' => $user->getRemainingTickets(),
                'subscription_status' => $subscription?->status,
                'can_purchase_tickets' => $user->canPurchaseTickets()
            ];
        } catch (\Exception $e) {
            Log::error('Error fetching user subscription info: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Track page view for analytics
     *
     * @param array $data
     * @return void
     */
    public function trackPageView(array $data)
    {
        try {
            // Store in cache for batch processing
            $cacheKey = 'page_views_' . date('Y-m-d-H');
            $views = Cache::get($cacheKey, []);
            $views[] = $data;
            
            Cache::put($cacheKey, $views, 7200); // Store for 2 hours
            
            // Update daily page view counter
            $dailyKey = 'daily_page_views_' . date('Y-m-d');
            Cache::increment($dailyKey, 1);
            
        } catch (\Exception $e) {
            Log::warning('Error tracking page view: ' . $e->getMessage());
        }
    }

    /**
     * Apply A/B test variant modifications
     *
     * @param array $data
     * @param string $variant
     * @return array
     */
    protected function applyABTestVariant(array $data, string $variant)
    {
        switch ($variant) {
            case 'variant_a':
                // Modify pricing display for variant A
                if (isset($data['pricing'])) {
                    $data['pricing']['highlight_yearly'] = true;
                    $data['pricing']['yearly_discount_percentage'] = 17;
                }
                break;
                
            case 'control':
            default:
                // Default behavior
                break;
        }
        
        return $data;
    }

    // Helper methods for statistics

    protected function getPlatformCount()
    {
        // This would count actual integrated platforms
        return '50+';
    }

    protected function getFormattedUserCount()
    {
        try {
            $count = User::count();
            if ($count >= 1000) {
                return round($count / 1000, 1) . 'K+';
            }
            return $count . '+';
        } catch (\Exception $e) {
            return '15K+';
        }
    }

    protected function getFormattedEventCount()
    {
        try {
            $count = Cache::remember('total_events_monitored', 3600, function () {
                return DB::table('events')->count();
            });
            
            if ($count >= 1000000) {
                return round($count / 1000000, 1) . 'M+';
            } elseif ($count >= 1000) {
                return round($count / 1000, 1) . 'K+';
            }
            return $count . '+';
        } catch (\Exception $e) {
            return '1M+';
        }
    }

    protected function getFormattedTicketCount()
    {
        try {
            $count = Cache::remember('total_tickets_tracked', 3600, function () {
                return DB::table('tickets')->count();
            });
            
            if ($count >= 1000000) {
                return round($count / 1000000, 1) . 'M+';
            } elseif ($count >= 1000) {
                return round($count / 1000, 1) . 'K+';
            }
            return $count . '+';
        } catch (\Exception $e) {
            return '5M+';
        }
    }

    protected function getActiveSubscriptionCount()
    {
        try {
            return UserSubscription::where('status', 'active')
                ->where('ends_at', '>', now())
                ->count();
        } catch (\Exception $e) {
            return 1200;
        }
    }

    protected function getSuccessRate()
    {
        return '98.5%'; // This would be calculated from actual success metrics
    }

    protected function getAverageSavings()
    {
        return '$127'; // This would be calculated from actual purchase data
    }

    protected function getFallbackStats()
    {
        return [
            'platforms' => '50+',
            'monitoring' => '24/7',
            'users' => '15K+',
            'events_monitored' => '1M+',
            'tickets_tracked' => '5M+',
            'active_subscriptions' => 1200,
            'success_rate' => '98.5%',
            'avg_savings' => '$127'
        ];
    }
}
