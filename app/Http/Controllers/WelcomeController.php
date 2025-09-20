<?php declare(strict_types=1);

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
     * Handle A/B testing variant assignment
     *
     * @return string|null
     */
    protected function getABTestVariant(Request $request)
    {
        // Check if A/B testing is enabled
        if (!config('app.ab_testing_enabled', FALSE)) {
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
}
