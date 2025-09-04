{{-- SEO Analytics Integration Component --}}
@props(['gtag_id' => env('GOOGLE_ANALYTICS_ID'), 'gsc_verification' => env('GOOGLE_SEARCH_CONSOLE_VERIFICATION')])

@if($gtag_id)
    <!-- Google Analytics 4 -->
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $gtag_id }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        
        // Enhanced ecommerce and user engagement tracking
        gtag('config', '{{ $gtag_id }}', {
            // Enhanced measurement
            enhanced_measurements: true,
            
            // Content grouping for legal pages
            custom_map: {
                'custom_parameter_1': 'document_type',
                'custom_parameter_2': 'user_role'
            },
            
            // User engagement tracking
            engagement_time_msec: 100,
            
            // Conversion tracking
            send_page_view: true,
            
            // Privacy settings
            anonymize_ip: true,
            allow_google_signals: false,
            allow_ad_personalization_signals: false
        });
        
        // Track legal document views
        @if(request()->is('legal/*'))
            gtag('event', 'legal_document_view', {
                event_category: 'legal',
                event_label: '{{ request()->path() }}',
                document_type: '{{ basename(request()->path()) }}',
                user_role: '{{ Auth::user()->role ?? "guest" }}'
            });
        @endif
        
        // Track user registration events
        @if(request()->routeIs('register.*'))
            gtag('event', 'registration_page_view', {
                event_category: 'registration',
                event_label: 'registration_form',
                registration_type: 'public'
            });
        @endif
        
        // Track subscription events
        @if(request()->routeIs('subscription.*'))
            gtag('event', 'subscription_page_view', {
                event_category: 'subscription',
                event_label: '{{ request()->route()->getName() }}',
                subscription_plan: 'monthly'
            });
        @endif
        
        // Enhanced site search tracking
        function trackSiteSearch(searchTerm, searchCategory = null, resultsCount = null) {
            gtag('event', 'search', {
                search_term: searchTerm,
                search_category: searchCategory,
                results_count: resultsCount
            });
        }
        
        // Track downloads and PDF views
        function trackDownload(fileName, fileType, downloadSource) {
            gtag('event', 'file_download', {
                event_category: 'downloads',
                event_label: fileName,
                file_extension: fileType,
                download_source: downloadSource
            });
        }
        
        // Track form submissions
        function trackFormSubmission(formName, formType, success = true) {
            gtag('event', success ? 'form_submit' : 'form_submit_failed', {
                event_category: 'forms',
                event_label: formName,
                form_type: formType,
                success: success
            });
        }
        
        // Track user engagement with features
        function trackFeatureEngagement(featureName, actionType, value = null) {
            gtag('event', 'feature_engagement', {
                event_category: 'features',
                event_label: featureName,
                action_type: actionType,
                value: value
            });
        }
        
        // Track subscription conversions
        function trackSubscriptionConversion(planType, planValue, currency = 'USD') {
            gtag('event', 'purchase', {
                transaction_id: Date.now().toString(),
                value: planValue,
                currency: currency,
                items: [{
                    item_id: 'hd_tickets_subscription',
                    item_name: 'HD Tickets Subscription',
                    item_category: 'subscription',
                    item_variant: planType,
                    price: planValue,
                    quantity: 1
                }]
            });
        }
        
        // Page timing tracking
        window.addEventListener('load', function() {
            gtag('event', 'timing_complete', {
                name: 'page_load',
                value: Math.round(performance.now())
            });
        });
        
        // Scroll depth tracking
        let scrollDepthTracked = [];
        window.addEventListener('scroll', function() {
            const scrollPercent = Math.round((window.scrollY / (document.documentElement.scrollHeight - window.innerHeight)) * 100);
            const milestones = [25, 50, 75, 90, 100];
            
            milestones.forEach(milestone => {
                if (scrollPercent >= milestone && !scrollDepthTracked.includes(milestone)) {
                    scrollDepthTracked.push(milestone);
                    gtag('event', 'scroll', {
                        event_category: 'engagement',
                        event_label: milestone + '%',
                        value: milestone
                    });
                }
            });
        });
    </script>
@endif

@if($gsc_verification)
    <!-- Google Search Console Verification -->
    <meta name="google-site-verification" content="{{ $gsc_verification }}">
@endif

<!-- Additional SEO and Analytics Meta Tags -->
@if(env('BING_WEBMASTER_VERIFICATION'))
    <meta name="msvalidate.01" content="{{ env('BING_WEBMASTER_VERIFICATION') }}">
@endif

@if(env('YANDEX_WEBMASTER_VERIFICATION'))
    <meta name="yandex-verification" content="{{ env('YANDEX_WEBMASTER_VERIFICATION') }}">
@endif

<!-- Facebook Pixel (if configured) -->
@if(env('FACEBOOK_PIXEL_ID'))
    <script>
        !function(f,b,e,v,n,t,s)
        {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};
        if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
        n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];
        s.parentNode.insertBefore(t,s)}(window, document,'script',
        'https://connect.facebook.net/en_US/fbevents.js');
        
        fbq('init', '{{ env('FACEBOOK_PIXEL_ID') }}');
        fbq('track', 'PageView');
        
        // Track legal document views
        @if(request()->is('legal/*'))
            fbq('trackCustom', 'LegalDocumentView', {
                document_type: '{{ basename(request()->path()) }}'
            });
        @endif
        
        // Track registrations
        @if(request()->routeIs('register.*'))
            fbq('track', 'InitiateCheckout');
        @endif
    </script>
    <noscript>
        <img height="1" width="1" style="display:none" 
             src="https://www.facebook.com/tr?id={{ env('FACEBOOK_PIXEL_ID') }}&ev=PageView&noscript=1">
    </noscript>
@endif

<!-- Hotjar Tracking (if configured) -->
@if(env('HOTJAR_ID'))
    <script>
        (function(h,o,t,j,a,r){
            h.hj=h.hj||function(){(h.hj.q=h.hj.q||[]).push(arguments)};
            h._hjSettings={hjid:{{ env('HOTJAR_ID') }},hjsv:6};
            a=o.getElementsByTagName('head')[0];
            r=o.createElement('script');r.async=1;
            r.src=t+h._hjSettings.hjid+j+h._hjSettings.hjsv;
            a.appendChild(r);
        })(window,document,'https://static.hotjar.com/c/hotjar-','.js?sv=');
    </script>
@endif

<!-- Microsoft Clarity (if configured) -->
@if(env('MICROSOFT_CLARITY_ID'))
    <script type="text/javascript">
        (function(c,l,a,r,i,t,y){
            c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
            t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
            y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
        })(window, document, "clarity", "script", "{{ env('MICROSOFT_CLARITY_ID') }}");
    </script>
@endif

<!-- Custom event tracking functions available globally -->
<script>
    // Make tracking functions available globally
    window.hdTicketsAnalytics = {
        trackSearch: typeof trackSiteSearch !== 'undefined' ? trackSiteSearch : function() {},
        trackDownload: typeof trackDownload !== 'undefined' ? trackDownload : function() {},
        trackForm: typeof trackFormSubmission !== 'undefined' ? trackFormSubmission : function() {},
        trackFeature: typeof trackFeatureEngagement !== 'undefined' ? trackFeatureEngagement : function() {},
        trackSubscription: typeof trackSubscriptionConversion !== 'undefined' ? trackSubscriptionConversion : function() {}
    };
    
    // Automatic link tracking for external links
    document.addEventListener('click', function(e) {
        if (e.target.tagName === 'A' && e.target.href) {
            const url = new URL(e.target.href);
            const isExternal = url.hostname !== window.location.hostname;
            const isDownload = e.target.hasAttribute('download') || 
                             /\.(pdf|doc|docx|xls|xlsx|zip|txt)$/i.test(url.pathname);
            
            if (isExternal && typeof gtag !== 'undefined') {
                gtag('event', 'click', {
                    event_category: 'outbound',
                    event_label: url.hostname,
                    transport_type: 'beacon'
                });
            }
            
            if (isDownload && typeof gtag !== 'undefined') {
                const fileName = url.pathname.split('/').pop();
                const fileExtension = fileName.split('.').pop();
                gtag('event', 'file_download', {
                    event_category: 'downloads',
                    event_label: fileName,
                    file_extension: fileExtension
                });
            }
        }
    });
    
    // Track print actions
    window.addEventListener('beforeprint', function() {
        if (typeof gtag !== 'undefined') {
            gtag('event', 'print', {
                event_category: 'engagement',
                event_label: window.location.pathname
            });
        }
    });
</script>

<!-- Structured Data for Analytics -->
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "WebSite",
    "name": "HD Tickets",
    "url": "{{ url('/') }}",
    "potentialAction": {
        "@type": "SearchAction",
        "target": {
            "@type": "EntryPoint",
            "urlTemplate": "{{ url('/search') }}?q={search_term_string}"
        },
        "query-input": "required name=search_term_string"
    },
    "sameAs": [
        "https://twitter.com/hdtickets",
        "https://facebook.com/hdtickets",
        "https://linkedin.com/company/hdtickets"
    ]
}
</script>
