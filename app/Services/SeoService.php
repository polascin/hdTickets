<?php declare(strict_types=1);

namespace App\Services;

use App\Models\LegalDocument;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

class SeoService
{
    /**
     * Generate dynamic sitemap data
     */
    public function generateSitemapData(): array
    {
        return Cache::remember('seo_sitemap_data', 3600, function () {
            $sitemapData = [];

            // Homepage - highest priority
            $sitemapData[] = [
                'url'        => route('home'),
                'lastmod'    => now()->toISOString(),
                'changefreq' => 'weekly',
                'priority'   => '1.0',
                'images'     => [
                    [
                        'loc'     => asset('assets/images/hdTicketsLogo.png'),
                        'title'   => 'HD Tickets - Professional Sports Ticket Monitoring Platform',
                        'caption' => 'HD Tickets logo - Professional sports event ticket monitoring platform',
                    ],
                ],
            ];

            // Authentication pages
            if (Route::has('login')) {
                $sitemapData[] = [
                    'url'        => route('login'),
                    'lastmod'    => now()->toISOString(),
                    'changefreq' => 'monthly',
                    'priority'   => '0.8',
                ];
            }

            if (Route::has('register.public')) {
                $sitemapData[] = [
                    'url'        => route('register.public'),
                    'lastmod'    => now()->toISOString(),
                    'changefreq' => 'monthly',
                    'priority'   => '0.9',
                ];
            }

            // Legal documents
            $this->addLegalDocumentsToSitemap($sitemapData);

            // Support and information pages
            $this->addStaticPagesToSitemap($sitemapData);

            return $sitemapData;
        });
    }

    /**
     * Generate XML sitemap content
     */
    public function generateXmlSitemap(): string
    {
        $sitemapData = $this->generateSitemapData();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n";
        $xml .= '        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"' . "\n";
        $xml .= '        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"' . "\n";
        $xml .= '        xmlns:xhtml="http://www.w3.org/1999/xhtml"' . "\n";
        $xml .= '        xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9' . "\n";
        $xml .= '                            http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd' . "\n";
        $xml .= '                            http://www.google.com/schemas/sitemap-image/1.1' . "\n";
        $xml .= '                            http://www.google.com/schemas/sitemap-image/1.1/sitemap-image.xsd">' . "\n";

        foreach ($sitemapData as $urlData) {
            $xml .= "    <url>\n";
            $xml .= '        <loc>' . htmlspecialchars($urlData['url']) . "</loc>\n";
            $xml .= '        <lastmod>' . $urlData['lastmod'] . "</lastmod>\n";
            $xml .= '        <changefreq>' . $urlData['changefreq'] . "</changefreq>\n";
            $xml .= '        <priority>' . $urlData['priority'] . "</priority>\n";

            if (isset($urlData['images']) && !empty($urlData['images'])) {
                foreach ($urlData['images'] as $image) {
                    $xml .= "        <image:image>\n";
                    $xml .= '            <image:loc>' . htmlspecialchars($image['loc']) . "</image:loc>\n";
                    $xml .= '            <image:title>' . htmlspecialchars($image['title']) . "</image:title>\n";
                    $xml .= '            <image:caption>' . htmlspecialchars($image['caption']) . "</image:caption>\n";
                    $xml .= "        </image:image>\n";
                }
            }

            $xml .= '        <xhtml:link rel="alternate" hreflang="en" href="' . htmlspecialchars($urlData['url']) . "\" />\n";
            $xml .= '        <xhtml:link rel="alternate" hreflang="x-default" href="' . htmlspecialchars($urlData['url']) . "\" />\n";
            $xml .= "    </url>\n";
        }

        $xml .= "</urlset>\n";

        return $xml;
    }

    /**
     * Get SEO meta data for a specific page
     */
    public function getPageSeoData(string $page, array $params = []): array
    {
        $seoData = Cache::remember("seo_page_{$page}", 1800, function () use ($page) {
            return match ($page) {
                'homepage' => [
                    'title'       => 'HD Tickets - Professional Sports Ticket Monitoring Platform',
                    'description' => 'Professional sports event ticket monitoring with subscription-based access, role-based permissions, automated purchasing, and GDPR compliance. Track prices across 50+ platforms with enterprise-grade security.',
                    'keywords'    => 'sports tickets monitoring, ticket price tracking, automated ticket purchasing, sports events, subscription ticket service, role-based access, GDPR compliant ticketing, 2FA security, professional ticket monitoring, real-time alerts',
                    'og_image'    => asset('assets/images/hdTicketsLogo.png'),
                    'canonical'   => route('home'),
                ],
                'legal' => [
                    'title'       => 'Legal Documents - HD Tickets',
                    'description' => 'Complete legal documentation for HD Tickets professional sports ticket monitoring platform. Terms of service, privacy policy, GDPR compliance, disclaimers, and data protection agreements.',
                    'keywords'    => 'HD Tickets legal documents, terms of service, privacy policy, GDPR compliance, sports ticket monitoring legal, data protection, legal policies',
                    'og_image'    => asset('assets/images/hdTicketsLogo.png'),
                    'canonical'   => route('legal.index'),
                ],
                'registration' => [
                    'title'       => 'Register - HD Tickets Professional Sports Monitoring',
                    'description' => 'Register for HD Tickets professional sports ticket monitoring platform. 7-day free trial, subscription-based access, role-based permissions, and enterprise security.',
                    'keywords'    => 'HD Tickets registration, sports ticket monitoring signup, professional sports platform, subscription registration, ticket monitoring account',
                    'og_image'    => asset('assets/images/hdTicketsLogo.png'),
                    'canonical'   => route('register.public'),
                ],
                default => [
                    'title'       => 'HD Tickets - Professional Sports Monitoring',
                    'description' => 'Professional sports ticket monitoring platform with comprehensive features.',
                    'keywords'    => 'HD Tickets, sports monitoring, professional platform',
                    'og_image'    => asset('assets/images/hdTicketsLogo.png'),
                    'canonical'   => route('home'),
                ],
            };
        });

        // Override with any provided params
        return array_merge($seoData, $params);
    }

    /**
     * Generate breadcrumb structured data
     */
    public function generateBreadcrumbData(array $breadcrumbs): array
    {
        $breadcrumbList = [
            '@context'        => 'https://schema.org',
            '@type'           => 'BreadcrumbList',
            'itemListElement' => [],
        ];

        foreach ($breadcrumbs as $position => $breadcrumb) {
            $breadcrumbList['itemListElement'][] = [
                '@type'    => 'ListItem',
                'position' => $position + 1,
                'name'     => $breadcrumb['name'],
                'item'     => $breadcrumb['url'],
            ];
        }

        return $breadcrumbList;
    }

    /**
     * Generate FAQ structured data
     */
    public function generateFaqData(array $faqs): array
    {
        $faqData = [
            '@context'   => 'https://schema.org',
            '@type'      => 'FAQPage',
            'mainEntity' => [],
        ];

        foreach ($faqs as $faq) {
            $faqData['mainEntity'][] = [
                '@type'          => 'Question',
                'name'           => $faq['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text'  => $faq['answer'],
                ],
            ];
        }

        return $faqData;
    }

    /**
     * Clear SEO caches
     */
    public function clearSeoCache(): void
    {
        Cache::forget('seo_sitemap_data');
        Cache::tags(['seo'])->flush();
    }

    /**
     * Validate URL for sitemap inclusion
     */
    public function isValidSitemapUrl(string $url): bool
    {
        // Check if URL is valid
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return FALSE;
        }

        // Check if URL is from our domain
        $parsedUrl = parse_url($url);
        $currentDomain = parse_url(config('app.url'), PHP_URL_HOST);

        if ($parsedUrl['host'] !== $currentDomain) {
            return FALSE;
        }

        // Exclude admin and private pages
        $excludedPaths = [
            '/admin',
            '/dashboard',
            '/profile',
            '/settings',
            '/api',
            '/webhook',
            '/storage',
            '/.env',
            '/.git',
        ];

        foreach ($excludedPaths as $excludedPath) {
            if (strpos($parsedUrl['path'], $excludedPath) === 0) {
                return FALSE;
            }
        }

        return TRUE;
    }

    /**
     * Generate robots.txt content dynamically
     */
    public function generateRobotsTxt(): string
    {
        $robots = "# HD Tickets - Sports Event Ticket Monitoring Platform\n";
        $robots .= "# Professional sports ticket monitoring with subscription-based access\n\n";

        $robots .= "User-agent: *\n\n";

        // Allow crawling of main pages and content
        $robots .= "# Allow crawling of main pages and content\n";
        $robots .= "Allow: /\n";
        $robots .= "Allow: /legal/*\n";
        $robots .= "Allow: /assets/*\n";
        $robots .= "Allow: /css/*\n";
        $robots .= "Allow: /js/*\n";
        $robots .= "Allow: /images/*\n\n";

        // Disallow sensitive areas
        $robots .= "# Disallow crawling of sensitive and private areas\n";
        $sensitiveAreas = [
            '/admin', '/admin/*',
            '/dashboard', '/dashboard/*',
            '/profile', '/profile/*',
            '/user/*', '/settings/*',
            '/api/*', '/webhook/*',
            '/monitoring/*', '/scraper/*',
            '/purchase/*', '/subscription/*',
            '/payment/*',
        ];

        foreach ($sensitiveAreas as $area) {
            $robots .= "Disallow: {$area}\n";
        }

        $robots .= "\n# Crawl delays for different bots\n";
        $robots .= "User-agent: Googlebot\nCrawl-delay: 1\n\n";
        $robots .= "User-agent: Bingbot\nCrawl-delay: 2\n\n";

        $robots .= "# Sitemap location\n";
        $robots .= 'Sitemap: ' . url('/sitemap.xml') . "\n\n";

        $robots .= "# Note: HD Tickets is a professional sports event ticket monitoring platform\n";
        $robots .= "# This is NOT a helpdesk ticket system\n";
        $robots .= "# Service provided \"as is\" with no warranty - see Terms of Service\n";

        return $robots;
    }

    /**
     * Add legal documents to sitemap
     */
    private function addLegalDocumentsToSitemap(array &$sitemapData): void
    {
        $legalRoutes = [
            'legal.terms-of-service'          => ['priority' => '0.7', 'changefreq' => 'monthly'],
            'legal.privacy-policy'            => ['priority' => '0.7', 'changefreq' => 'monthly'],
            'legal.disclaimer'                => ['priority' => '0.6', 'changefreq' => 'monthly'],
            'legal.gdpr-compliance'           => ['priority' => '0.6', 'changefreq' => 'monthly'],
            'legal.data-processing-agreement' => ['priority' => '0.5', 'changefreq' => 'monthly'],
            'legal.cookie-policy'             => ['priority' => '0.5', 'changefreq' => 'monthly'],
            'legal.acceptable-use-policy'     => ['priority' => '0.5', 'changefreq' => 'monthly'],
            'legal.legal-notices'             => ['priority' => '0.5', 'changefreq' => 'monthly'],
        ];

        foreach ($legalRoutes as $routeName => $config) {
            if (Route::has($routeName)) {
                $lastmod = now()->toISOString();

                // Try to get actual document update date
                try {
                    $documentType = str_replace(['legal.', '-'], ['', '_'], $routeName);
                    $document = LegalDocument::getActive($documentType);
                    if ($document && $document->effective_date) {
                        $lastmod = $document->effective_date->toISOString();
                    }
                } catch (Exception $e) {
                    // Use default date if document not found
                }

                $sitemapData[] = [
                    'url'        => route($routeName),
                    'lastmod'    => $lastmod,
                    'changefreq' => $config['changefreq'],
                    'priority'   => $config['priority'],
                ];
            }
        }
    }

    /**
     * Add static pages to sitemap
     */
    private function addStaticPagesToSitemap(array &$sitemapData): void
    {
        $staticPages = [
            'contact' => ['priority' => '0.6', 'changefreq' => 'monthly'],
            'support' => ['priority' => '0.7', 'changefreq' => 'weekly'],
            'about'   => ['priority' => '0.6', 'changefreq' => 'monthly'],
            'docs'    => ['priority' => '0.4', 'changefreq' => 'monthly'],
        ];

        foreach ($staticPages as $routeName => $config) {
            if (Route::has($routeName)) {
                $sitemapData[] = [
                    'url'        => route($routeName),
                    'lastmod'    => now()->toISOString(),
                    'changefreq' => $config['changefreq'],
                    'priority'   => $config['priority'],
                ];
            }
        }
    }
}
