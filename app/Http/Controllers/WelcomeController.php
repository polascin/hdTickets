<?php

namespace App\Http\Controllers;

use App\Services\WelcomePageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WelcomeController extends Controller
{
    protected $welcomePageService;

    public function __construct(WelcomePageService $welcomePageService)
    {
        $this->welcomePageService = $welcomePageService;
        
        // Apply middleware for caching and analytics
        $this->middleware('welcome.page')->only('index');
    }

    /**
     * Display the welcome page with dynamic data
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
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
            
            // Load dynamic data for the welcome page
            $data = $this->welcomePageService->getWelcomePageData([
                'include_stats' => true,
                'include_pricing' => true,
                'include_features' => true,
                'include_legal_docs' => true,
                'ab_variant' => $abVariant
            ]);
            
            // Add user context if authenticated
            if (Auth::check()) {
                $data['user'] = Auth::user();
                $data['user_subscription'] = $this->welcomePageService->getUserSubscriptionInfo(Auth::user());
            }
            
            // Add A/B test variant to data
            $data['ab_variant'] = $abVariant;
            
            // Track page view
            $this->trackPageView($request, $abVariant);
            
            // Cache page data for performance
            $cacheKey = 'welcome_page_data_' . ($abVariant ?? 'default');
            $cachedData = Cache::remember($cacheKey, 300, function () use ($data) {
                return $data;
            });
            
            return view('welcome', array_merge($cachedData, [
                'user' => $data['user'] ?? null,
                'user_subscription' => $data['user_subscription'] ?? null,
            ]));
            
        } catch (\Exception $e) {
            Log::error('Welcome page error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Fallback to simple welcome view without dynamic data
            return view('welcome', [
                'stats' => $this->getFallbackStats(),
                'features' => $this->getFallbackFeatures(),
                'pricing' => $this->getFallbackPricing(),
                'legal_docs' => $this->getFallbackLegalDocs(),
                'ab_variant' => 'default'
            ]);
        }
    }

    /**
     * API endpoint for welcome page statistics
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function stats()
    {
        try {
            $stats = Cache::remember('welcome_stats', 600, function () {
                return $this->welcomePageService->getStatistics();
            });
            
            return response()->json($stats);
            
        } catch (\Exception $e) {
            Log::error('Welcome stats API error: ' . $e->getMessage());
            
            return response()->json($this->getFallbackStats(), 200);
        }
    }

    /**
     * Handle A/B testing variant assignment
     *
     * @param Request $request
     * @return string|null
     */
    protected function getABTestVariant(Request $request)
    {
        // Check if A/B testing is enabled
        if (!config('app.ab_testing_enabled', false)) {
            return null;
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
     *
     * @param Request $request
     * @return bool
     */
    protected function shouldRedirectUser(Request $request)
    {
        // Example: Redirect authenticated users to dashboard
        // This is optional and can be configured via settings
        if (config('welcome.redirect_authenticated_users', false) && Auth::check()) {
            return true;
        }
        
        return false;
    }

    /**
     * Handle user redirection
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
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
     * @param Request $request
     * @param string|null $abVariant
     * @return void
     */
    protected function trackPageView(Request $request, $abVariant = null)
    {
        try {
            // Track page view with user context
            $this->welcomePageService->trackPageView([
                'user_id' => Auth::id(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'referrer' => $request->header('referer'),
                'ab_variant' => $abVariant,
                'timestamp' => now(),
                'session_id' => $request->session()->getId()
            ]);
            
        } catch (\Exception $e) {
            // Silently log analytics errors, don't break the page
            Log::warning('Analytics tracking error: ' . $e->getMessage());
        }
    }

    /**
     * Get fallback statistics when service fails
     *
     * @return array
     */
    protected function getFallbackStats()
    {
        return [
            'platforms' => '50+',
            'monitoring' => '24/7',
            'users' => '15K+',
            'events_monitored' => '1M+',
            'tickets_tracked' => '5M+'
        ];
    }

    /**
     * Get fallback features when service fails
     *
     * @return array
     */
    protected function getFallbackFeatures()
    {
        return [
            'role_based_access' => [
                'title' => 'Role-Based Access',
                'description' => 'Customer, Agent, Admin & Scraper roles with tailored permissions',
                'icon' => '👥'
            ],
            'subscription_system' => [
                'title' => 'Subscription System',
                'description' => 'Monthly plans with configurable limits and 7-day free trial',
                'icon' => '💳'
            ],
            'legal_compliance' => [
                'title' => 'Legal Compliance',
                'description' => 'GDPR compliant with mandatory legal document acceptance',
                'icon' => '⚖️'
            ],
            'enhanced_security' => [
                'title' => 'Enhanced Security',
                'description' => '2FA, device fingerprinting, and secure payment processing',
                'icon' => '🔒'
            ]
        ];
    }

    /**
     * Get fallback pricing when service fails
     *
     * @return array
     */
    protected function getFallbackPricing()
    {
        return [
            'monthly_price' => 29.99,
            'yearly_price' => 299.99,
            'free_trial_days' => 7,
            'default_ticket_limit' => 100,
            'currency' => 'USD'
        ];
    }

    /**
     * Get fallback legal documents when service fails
     *
     * @return array
     */
    protected function getFallbackLegalDocs()
    {
        return [
            'terms_of_service' => '/legal/terms-of-service',
            'service_disclaimer' => '/legal/service-disclaimer',
            'privacy_policy' => '/legal/privacy-policy',
            'data_processing_agreement' => '/legal/data-processing-agreement',
            'cookie_policy' => '/legal/cookie-policy',
            'acceptable_use_policy' => '/legal/acceptable-use-policy'
        ];
    }
}
