{{-- JSON-LD Structured Data Component for SEO --}}
@props(['type' => 'website', 'page' => null])

@php
$baseData = [
    "@context" => "https://schema.org",
    "@type" => "Organization",
    "name" => "HD Tickets",
    "description" => "Professional sports event ticket monitoring platform with subscription-based access, role-based permissions, automated purchasing, and GDPR compliance.",
    "url" => url('/'),
    "logo" => asset('assets/images/hdTicketsLogo.png'),
    "foundingDate" => "2024",
    "slogan" => "Never miss your favorite team again",
    "contactPoint" => [
        "@type" => "ContactPoint",
        "contactType" => "customer service",
        "availableLanguage" => ["English"],
        "areaServed" => "US"
    ],
    "sameAs" => [
        // Add social media profiles when available
    ],
    "offers" => [
        "@type" => "Offer",
        "name" => "Professional Sports Ticket Monitoring",
        "description" => "Comprehensive sports ticket monitoring with automated alerts and purchasing",
        "category" => "Sports Technology Service",
        "availability" => "https://schema.org/InStock"
    ]
];

$websiteData = [
    "@context" => "https://schema.org",
    "@type" => "WebSite",
    "name" => "HD Tickets",
    "url" => url('/'),
    "potentialAction" => [
        "@type" => "SearchAction",
        "target" => url('/search?q={search_term_string}'),
        "query-input" => "required name=search_term_string"
    ],
    "mainEntity" => [
        "@type" => "SoftwareApplication",
        "name" => "HD Tickets Platform",
        "applicationCategory" => "Sports Technology",
        "operatingSystem" => "Web Browser",
        "offers" => [
            "@type" => "Offer",
            "price" => "29.99",
            "priceCurrency" => "USD",
            "priceValidUntil" => now()->addYear()->toISOString(),
            "availability" => "https://schema.org/InStock",
            "validFrom" => now()->toISOString()
        ],
        "aggregateRating" => [
            "@type" => "AggregateRating",
            "ratingValue" => "4.8",
            "reviewCount" => "150",
            "bestRating" => "5",
            "worstRating" => "1"
        ]
    ]
];

$webPageData = [
    "@context" => "https://schema.org",
    "@type" => "WebPage",
    "name" => "HD Tickets - Professional Sports Ticket Monitoring Platform",
    "description" => "Professional sports event ticket monitoring with subscription-based access, role-based permissions, automated purchasing, and GDPR compliance.",
    "url" => url()->current(),
    "inLanguage" => "en-US",
    "isPartOf" => [
        "@type" => "WebSite",
        "name" => "HD Tickets",
        "url" => url('/')
    ],
    "about" => [
        "@type" => "Thing",
        "name" => "Sports Ticket Monitoring",
        "description" => "Professional monitoring system for sports event tickets"
    ],
    "mainContentOfPage" => [
        "@type" => "WebPageElement",
        "cssSelector" => "main"
    ],
    "significantLink" => [
        url('/register'),
        url('/login'),
        url('/legal/terms'),
        url('/legal/privacy')
    ]
];

$breadcrumbData = [
    "@context" => "https://schema.org",
    "@type" => "BreadcrumbList",
    "itemListElement" => [
        [
            "@type" => "ListItem",
            "position" => 1,
            "name" => "Home",
            "item" => url('/')
        ]
    ]
];

$faqData = [
    "@context" => "https://schema.org",
    "@type" => "FAQPage",
    "mainEntity" => [
        [
            "@type" => "Question",
            "name" => "What is HD Tickets?",
            "acceptedAnswer" => [
                "@type" => "Answer",
                "text" => "HD Tickets is a professional sports event ticket monitoring platform that provides subscription-based access to ticket price tracking, availability alerts, and automated purchasing across 50+ ticket platforms."
            ]
        ],
        [
            "@type" => "Question",
            "name" => "How much does HD Tickets cost?",
            "acceptedAnswer" => [
                "@type" => "Answer",
                "text" => "HD Tickets offers a subscription-based service starting at $29.99 per month, with a 7-day free trial for new customers. Different plans offer varying ticket limits and features."
            ]
        ],
        [
            "@type" => "Question",
            "name" => "Is HD Tickets GDPR compliant?",
            "acceptedAnswer" => [
                "@type" => "Answer",
                "text" => "Yes, HD Tickets is fully GDPR compliant with comprehensive data processing agreements, privacy controls, and user consent management systems in place."
            ]
        ],
        [
            "@type" => "Question",
            "name" => "What security features does HD Tickets offer?",
            "acceptedAnswer" => [
                "@type" => "Answer",
                "text" => "HD Tickets provides enterprise-grade security including two-factor authentication (2FA), role-based access control, device fingerprinting, session management, and comprehensive audit trails."
            ]
        ]
    ]
];

// Select appropriate structured data based on page type
$structuredDataSets = [];

switch ($type) {
    case 'homepage':
        $structuredDataSets = [$baseData, $websiteData, $webPageData, $breadcrumbData, $faqData];
        break;
    case 'product':
        $structuredDataSets = [$baseData, $webPageData, $breadcrumbData];
        break;
    case 'legal':
        $structuredDataSets = [$baseData, $webPageData, $breadcrumbData];
        break;
    default:
        $structuredDataSets = [$baseData, $webPageData];
        break;
}
@endphp

@foreach($structuredDataSets as $data)
<script type="application/ld+json">
{!! json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) !!}
</script>
@endforeach
