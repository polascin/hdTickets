<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\WelcomePageService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class WelcomeController extends Controller
{
    public function __construct()
    {
        // Temporarily disable middleware to test
        // $this->middleware('welcome.page')->only('index');
    }

    /**
     * Display the welcome page with dynamic data
     *
     * @return RedirectResponse|View
     */
    public function index(Request $request)
    {
        try {
            // A/B Testing - randomly assign variant for new visitors
            $abVariant = $this->getABTestVariant($request);

            // Check if user should be redirected (optional redirect logic)
            if ($this->shouldRedirectUser($request)) {
                return $this->handleRedirect($request);
            }

            // Resolve service via container to avoid constructor DI issues
            $service = app(WelcomePageService::class);

            // Load dynamic data for the welcome page
            $data = $service->getWelcomePageData([
                'include_stats'      => TRUE,
                'include_pricing'    => TRUE,
                'include_features'   => TRUE,
                'include_legal_docs' => TRUE,
                'ab_variant'         => $abVariant,
            ]);

            // Add user context if authenticated
            if (Auth::check()) {
                $data['user'] = Auth::user();
                $data['user_subscription'] = $service->getUserSubscriptionInfo(Auth::user());
            }

            // Add A/B test variant to data
            $data['ab_variant'] = $abVariant;

            // Track page view
            $this->trackPageView($request, $abVariant);

            // Cache page data for performance
            $cacheKey = 'welcome_page_data_' . ($abVariant ?? 'default');
            $cachedData = Cache::remember($cacheKey, 300, fn () => $data);

            return view('welcome', array_merge($cachedData, [
                'user'              => $data['user'] ?? NULL,
                'user_subscription' => $data['user_subscription'] ?? NULL,
            ]));
        } catch (Exception $e) {
            Log::error('Welcome page error: ' . $e->getMessage(), [
                'user_id'    => Auth::id(),
                'ip'         => $request->ip(),
                'user_agent' => $request->userAgent(),
                'trace'      => $e->getTraceAsString(),
            ]);

            // Fallback to simple welcome view without dynamic data
            return view('welcome', [
                'stats'      => $this->getFallbackStats(),
                'features'   => $this->getFallbackFeatures(),
                'pricing'    => $this->getFallbackPricing(),
                'legal_docs' => $this->getFallbackLegalDocs(),
                'ab_variant' => 'default',
            ]);
        }
    }

    /**
     * API endpoint for welcome page statistics
     *
     * @return JsonResponse
     */
    public function stats()
    {
        try {
            $stats = Cache::remember('welcome_stats', 600, fn () => app(WelcomePageService::class)->getStatistics());

            return response()->json($stats);
        } catch (Exception $e) {
            Log::error('Welcome stats API error: ' . $e->getMessage());

            return response()->json($this->getFallbackStats(), 200);
        }
    }

    /**
     * Display the comprehensive welcome page
     *
     * @return View
     */
    public function newWelcome(Request $request)
    {
        try {
            // Resolve service via container to avoid constructor DI issues
            $service = app(WelcomePageService::class);

            // Load dynamic data for the welcome page
            $data = $service->getWelcomePageData([
                'include_stats'      => TRUE,
                'include_pricing'    => TRUE,
                'include_features'   => TRUE,
                'include_legal_docs' => TRUE,
                'ab_variant'         => 'comprehensive',
            ]);

            return view('welcome-enhanced', $data);
        } catch (Exception $e) {
            Log::error('Error loading comprehensive welcome page', [
                'error'      => $e->getMessage(),
                'user_agent' => $request->userAgent(),
                'ip'         => $request->ip(),
            ]);

            // Fallback to basic welcome view
            return view('welcome-enhanced', [
                'total_tickets'         => 0,
                'active_events'         => 0,
                'satisfied_customers'   => 0,
                'avg_savings'           => 0,
                'avg_purchase_time'     => 0,
                'subscription_plans'    => [],
                'key_features'          => [],
                'platform_integrations' => [],
                'legal_documents'       => [],
            ]);
        }
    }

    /**
     * Display the modern welcome landing page
     *
     * @return View
     */
    public function modernWelcome(Request $request)
    {
        try {
            // Resolve service via container
            $service = app(WelcomePageService::class);

            // Load comprehensive data for the modern welcome page
            $data = $service->getWelcomePageData([
                'include_stats'            => TRUE,
                'include_pricing'          => TRUE,
                'include_features'         => TRUE,
                'include_testimonials'     => TRUE,
                'include_featured_tickets' => TRUE,
                'ab_variant'               => 'modern_landing',
            ]);

            // Add modern landing page specific data
            $data['featured_tickets'] = $this->getFeaturedTickets();
            $data['platform_integrations'] = $this->getPlatformIntegrations();
            $data['user_testimonials'] = $this->getUserTestimonials();

            return view('welcome-modern', $data);
        } catch (Exception $e) {
            Log::error('Error loading modern welcome page', [
                'error'      => $e->getMessage(),
                'user_agent' => $request->userAgent(),
                'ip'         => $request->ip(),
                'trace'      => $e->getTraceAsString(),
            ]);

            // Fallback with static data
            return view('welcome-modern', $this->getFallbackModernData());
        }
    }

    /**
     * Display the enhanced backend features welcome page
     *
     * @return View
     */
    public function enhancedWelcome(Request $request)
    {
        try {
            // Resolve service via container to avoid constructor DI issues
            $service = app(WelcomePageService::class);

            // Load dynamic data for the enhanced welcome page
            $data = $service->getWelcomePageData([
                'include_stats'      => TRUE,
                'include_pricing'    => TRUE,
                'include_features'   => TRUE,
                'include_legal_docs' => TRUE,
                'include_security'   => TRUE,
                'include_platforms'  => TRUE,
                'ab_variant'         => 'enhanced_backend',
            ]);

            // Add backend-specific data
            $data['backend_features'] = $this->getBackendFeatures();
            $data['technology_stack'] = $this->getTechnologyStack();
            $data['security_features'] = $this->getSecurityFeatures();
            $data['platform_integrations'] = $this->getPlatformIntegrations();

            return view('welcome-enhanced', $data);
        } catch (Exception $e) {
            Log::error('Error loading enhanced welcome page', [
                'error'      => $e->getMessage(),
                'user_agent' => $request->userAgent(),
                'ip'         => $request->ip(),
                'trace'      => $e->getTraceAsString(),
            ]);

            // Fallback to basic enhanced view with static data
            return view('welcome-enhanced', $this->getFallbackEnhancedData());
        }
    }

    /**
     * Handle A/B testing variant assignment
     *
     * @return string|null
     */
    protected function getABTestVariant(Request $request)
    {
        // Check if A/B testing is enabled
        if (! config('app.ab_testing_enabled', FALSE)) {
            return;
        }

        // Check if user already has a variant assigned
        $sessionVariant = $request->session()->get('ab_variant');
        if ($sessionVariant) {
            return $sessionVariant;
        }

        // Randomly assign variant (50/50 split)
        $variants = ['control', 'variant_a'];
        $variant = $variants[array_rand($variants)];

        // Store in session
        $request->session()->put('ab_variant', $variant);

        return $variant;
    }

    /**
     * Check if user should be redirected
     */
    protected function shouldRedirectUser(Request $request): bool
    {
        // Example: Redirect authenticated users to dashboard
        // This is optional and can be configured via settings
        return config('welcome.redirect_authenticated_users', FALSE) && Auth::check();
    }

    /**
     * Handle user redirection
     *
     * @return RedirectResponse
     */
    protected function handleRedirect(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return redirect()->route('login');
    }

    /**
     * Track page view for analytics
     *
     * @param string|null $abVariant
     */
    protected function trackPageView(Request $request, $abVariant = NULL): void
    {
        try {
            $service = app(WelcomePageService::class);

            // Track page view with user context
            $service->trackPageView([
                'user_id'    => Auth::id(),
                'ip'         => $request->ip(),
                'user_agent' => $request->userAgent(),
                'referrer'   => $request->header('referer'),
                'ab_variant' => $abVariant,
                'timestamp'  => now(),
                'session_id' => $request->session()->getId(),
            ]);
        } catch (Exception $e) {
            // Silently log analytics errors, don't break the page
            Log::warning('Analytics tracking error: ' . $e->getMessage());
        }
    }

    /**
     * Get fallback statistics when service fails
     */
    protected function getFallbackStats(): array
    {
        return [
            'platforms'        => '50+',
            'monitoring'       => '24/7',
            'users'            => '15K+',
            'events_monitored' => '1M+',
            'tickets_tracked'  => '5M+',
        ];
    }

    /**
     * Get fallback features when service fails
     */
    protected function getFallbackFeatures(): array
    {
        return [
            'role_based_access' => [
                'title'       => 'Role-Based Access',
                'description' => 'Customer, Agent, Admin & Scraper roles with tailored permissions',
                'icon'        => '👥',
            ],
            'subscription_system' => [
                'title'       => 'Subscription System',
                'description' => 'Monthly plans with configurable limits and 7-day free trial',
                'icon'        => '💳',
            ],
            'legal_compliance' => [
                'title'       => 'Legal Compliance',
                'description' => 'GDPR compliant with mandatory legal document acceptance',
                'icon'        => '⚖️',
            ],
            'enhanced_security' => [
                'title'       => 'Enhanced Security',
                'description' => '2FA, device fingerprinting, and secure payment processing',
                'icon'        => '🔒',
            ],
        ];
    }

    /**
     * Get fallback pricing when service fails
     */
    protected function getFallbackPricing(): array
    {
        return [
            'monthly_price'        => 29.99,
            'yearly_price'         => 299.99,
            'free_trial_days'      => 7,
            'default_ticket_limit' => 100,
            'currency'             => 'USD',
        ];
    }

    /**
     * Get fallback legal documents when service fails
     */
    protected function getFallbackLegalDocs(): array
    {
        return [
            'terms_of_service'          => '/legal/terms-of-service',
            'service_disclaimer'        => '/legal/service-disclaimer',
            'privacy_policy'            => '/legal/privacy-policy',
            'data_processing_agreement' => '/legal/data-processing-agreement',
            'cookie_policy'             => '/legal/cookie-policy',
            'acceptable_use_policy'     => '/legal/acceptable-use-policy',
        ];
    }

    /**
     * Get featured tickets for the modern welcome page
     */
    protected function getFeaturedTickets(): array
    {
        return [
            [
                'event_name'     => 'Liverpool vs Manchester City',
                'venue'          => 'Anfield Stadium',
                'section'        => 'Kop End',
                'current_price'  => 150,
                'original_price' => 200,
                'discount'       => 25,
                'platform'       => 'StubHub',
            ],
            [
                'event_name'     => 'Arsenal vs Chelsea',
                'venue'          => 'Emirates Stadium',
                'section'        => 'North Bank',
                'current_price'  => 95,
                'original_price' => 130,
                'discount'       => 27,
                'platform'       => 'Viagogo',
            ],
            [
                'event_name'     => 'Manchester United vs Tottenham',
                'venue'          => 'Old Trafford',
                'section'        => 'Stretford End',
                'current_price'  => 120,
                'original_price' => 180,
                'discount'       => 33,
                'platform'       => 'Ticketmaster',
            ],
        ];
    }

    /**
     * Get user testimonials for the modern welcome page
     */
    protected function getUserTestimonials(): array
    {
        return [
            [
                'name'    => 'James Davidson',
                'role'    => 'Manchester United Fan',
                'avatar'  => 'JD',
                'rating'  => 5,
                'comment' => 'HD Tickets saved me over £300 on Champions League final tickets. The alerts are instant and the platform is so easy to use.',
            ],
            [
                'name'    => 'Sarah Thompson',
                'role'    => 'Arsenal Season Ticket Holder',
                'avatar'  => 'ST',
                'rating'  => 5,
                'comment' => 'As a season ticket holder for Arsenal, I use HD Tickets to find away game tickets. It\'s found me deals I never would have seen.',
            ],
            [
                'name'    => 'Mike Roberts',
                'role'    => 'Tennis Enthusiast',
                'avatar'  => 'MR',
                'rating'  => 5,
                'comment' => 'The automated purchasing feature is a game-changer. I got Wimbledon final tickets while I was sleeping!',
            ],
        ];
    }

    /**
     * Get fallback data for modern welcome page
     */
    protected function getFallbackModernData(): array
    {
        return [
            'stats' => [
                'total_tickets'       => 50000,
                'active_events'       => 2500,
                'satisfied_customers' => 10000,
                'avg_savings'         => 35,
            ],
            'featured_tickets'      => $this->getFeaturedTickets(),
            'user_testimonials'     => $this->getUserTestimonials(),
            'platform_integrations' => $this->getPlatformIntegrations(),
        ];
    }

    /**
     * Get backend features data
     */
    private function getBackendFeatures(): array
    {
        return [
            'ticket_monitoring' => [
                'title'       => 'Real-Time Ticket Monitoring',
                'description' => 'Advanced web scraping and API integration across 15+ major ticketing platforms',
                'features'    => [
                    'Multi-platform scraping service with rotation',
                    'Real-time availability notifications',
                    'Historical price analysis and trends',
                    'Custom monitoring criteria and filters',
                    'Anti-detection and CAPTCHA handling',
                ],
            ],
            'purchase_automation' => [
                'title'       => 'AI-Powered Purchase Automation',
                'description' => 'Intelligent purchase decision engine with machine learning algorithms',
                'features'    => [
                    'Smart purchase decision algorithms',
                    'Automated checkout with payment processing',
                    'Purchase queue management system',
                    'Risk assessment and validation',
                    'Success rate optimization',
                ],
            ],
            'security_system' => [
                'title'       => 'Enterprise Security',
                'description' => 'Multi-layered security architecture with advanced authentication',
                'features'    => [
                    'Two-factor authentication (2FA)',
                    'Trusted device management',
                    'End-to-end encryption',
                    'Security incident monitoring',
                    'Comprehensive audit trails',
                ],
            ],
            'analytics_engine' => [
                'title'       => 'Advanced Analytics Engine',
                'description' => 'Comprehensive business intelligence with predictive analytics',
                'features'    => [
                    'Real-time analytics dashboard',
                    'User behavior tracking and insights',
                    'Market trend analysis',
                    'Performance metrics and KPIs',
                    'Automated reporting system',
                ],
            ],
        ];
    }

    /**
     * Get technology stack data
     */
    private function getTechnologyStack(): array
    {
        return [
            'backend' => [
                'Laravel 10',
                'PHP 8.4',
                'MySQL/MariaDB',
                'Redis Cache',
            ],
            'infrastructure' => [
                'Apache2',
                'Ubuntu 24.04',
                'Queue Jobs',
                'WebSockets',
            ],
            'architecture' => [
                'Domain-Driven Design',
                'Event-Driven Architecture',
                'CQRS Pattern',
                'Microservices Ready',
            ],
        ];
    }

    /**
     * Get security features data
     */
    private function getSecurityFeatures(): array
    {
        return [
            'authentication' => [
                'title'       => 'Advanced Authentication',
                'description' => 'Two-factor authentication, trusted devices, and biometric support',
            ],
            'encryption' => [
                'title'       => 'Data Encryption',
                'description' => 'End-to-end encryption with industry-standard AES-256 protocols',
            ],
            'monitoring' => [
                'title'       => 'Security Monitoring',
                'description' => 'Real-time threat detection with automated incident response',
            ],
            'anti_fraud' => [
                'title'       => 'Anti-Fraud Protection',
                'description' => 'Advanced fraud detection with machine learning-based risk assessment',
            ],
        ];
    }

    /**
     * Get platform integrations data
     */
    private function getPlatformIntegrations(): array
    {
        return [
            'ticketmaster' => [
                'name'        => 'Ticketmaster',
                'description' => 'Official API integration with real-time inventory access',
                'status'      => 'active',
            ],
            'stubhub' => [
                'name'        => 'StubHub',
                'description' => 'Secondary market monitoring with price comparison',
                'status'      => 'active',
            ],
            'seatgeek' => [
                'name'        => 'SeatGeek',
                'description' => 'Marketplace integration with deal scoring',
                'status'      => 'active',
            ],
            'football_clubs' => [
                'name'        => 'Football Club Stores',
                'description' => 'Direct integration with official team stores',
                'status'      => 'active',
            ],
        ];
    }

    /**
     * Get fallback enhanced data
     */
    private function getFallbackEnhancedData(): array
    {
        return [
            'stats' => [
                'platforms'    => '15+',
                'monitoring'   => '24/7',
                'users'        => '10K+',
                'success_rate' => '99.9%',
            ],
            'backend_features'      => $this->getBackendFeatures(),
            'technology_stack'      => $this->getTechnologyStack(),
            'security_features'     => $this->getSecurityFeatures(),
            'platform_integrations' => $this->getPlatformIntegrations(),
        ];
    }
}
