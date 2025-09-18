<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="@yield('meta_description', 'HD Tickets legal documents and policies. Professional sports ticket monitoring platform with comprehensive legal compliance and GDPR protection.')">
    <meta name="keywords" content="@yield('meta_keywords', 'HD Tickets legal, terms of service, privacy policy, GDPR compliance, sports ticket monitoring, legal documents, data protection')">
    <meta name="robots" content="index, follow, max-snippet:-1">
    <meta name="author" content="HD Tickets">
    <meta name="generator" content="Laravel {{ app()->version() }}">
    <meta name="theme-color" content="#2563eb">
    
    <!-- Canonical and Language Tags -->
    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="alternate" hreflang="en" href="{{ url()->current() }}">
    <link rel="alternate" hreflang="x-default" href="{{ url()->current() }}">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:type" content="article">
    <meta property="og:site_name" content="HD Tickets">
    <meta property="og:title" content="@yield('og_title', 'Legal Documents - HD Tickets Legal')">
    <meta property="og:description" content="@yield('og_description', 'Legal documents and policies for HD Tickets professional sports ticket monitoring platform')">
    <meta property="og:image" content="{{ asset('assets/images/hdTicketsLogo.png') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:locale" content="en_US">
    <meta property="article:publisher" content="HD Tickets">
    <meta property="article:section" content="Legal Documents">
    <meta property="article:modified_time" content="@yield('modified_time', now()->toISOString())">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:site" content="@hdtickets">
    <meta name="twitter:title" content="@yield('twitter_title', 'Legal Documents - HD Tickets Legal')">
    <meta name="twitter:description" content="@yield('twitter_description', 'Legal documents for HD Tickets professional sports ticket monitoring platform')">
    <meta name="twitter:image" content="{{ asset('assets/images/hdTicketsLogo.png') }}">
    
    <!-- Structured Data -->
    <x-seo.structured-data type="legal" />
    
    <!-- Analytics Integration -->
    <x-seo.analytics />
    
    <!-- Performance and Resource Hints -->
    <link rel="dns-prefetch" href="//fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preload" href="{{ asset('assets/css/app.css') }}" as="style">
    
    <!-- Icons -->
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/images/hdTicketsLogo.png') }}">
    
    <title>@yield('title', 'Legal Documents') - HD Tickets</title>
    
    <!-- Compiled CSS -->
    @vite(['resources/css/app.css'])
    
    <!-- Critical CSS for legal pages -->
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #374151;
            background: #f9fafb;
        }
        
        .legal-container {
            max-width: 4xl;
            margin: 0 auto;
            padding: 2rem 1rem;
        }
        
        .legal-document {
            background: white;
            border-radius: 12px;
            padding: 3rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
        }
        
        .legal-header {
            text-align: center;
            margin-bottom: 3rem;
            padding-bottom: 2rem;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .legal-title {
            font-size: 2.5rem;
            font-weight: 900;
            color: #1f2937;
            margin-bottom: 1rem;
            line-height: 1.2;
        }
        
        .legal-subtitle {
            font-size: 1.125rem;
            color: #6b7280;
            margin-bottom: 0.5rem;
        }
        
        .legal-updated {
            font-size: 0.875rem;
            color: #9ca3af;
            font-style: italic;
        }
        
        .legal-content {
            font-size: 1rem;
            line-height: 1.75;
            color: #374151;
        }
        
        .legal-content h2 {
            font-size: 1.875rem;
            font-weight: 700;
            color: #1f2937;
            margin: 2.5rem 0 1.5rem 0;
            padding-top: 1rem;
            border-top: 1px solid #e5e7eb;
        }
        
        .legal-content h3 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #374151;
            margin: 2rem 0 1rem 0;
        }
        
        .legal-content h4 {
            font-size: 1.25rem;
            font-weight: 600;
            color: #4b5563;
            margin: 1.5rem 0 0.75rem 0;
        }
        
        .legal-content p {
            margin: 1rem 0;
        }
        
        .legal-content ul, .legal-content ol {
            margin: 1rem 0;
            padding-left: 2rem;
        }
        
        .legal-content li {
            margin: 0.5rem 0;
        }
        
        .legal-content a {
            color: #2563eb;
            text-decoration: underline;
        }
        
        .legal-content a:hover {
            color: #1d4ed8;
        }
        
        .legal-nav {
            background: white;
            border-bottom: 1px solid #e5e7eb;
            sticky-top: 0;
            z-index: 10;
        }
        
        .legal-nav-container {
            max-width: 7xl;
            margin: 0 auto;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .legal-nav-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .legal-nav-logo img {
            width: 32px;
            height: 32px;
            border-radius: 6px;
        }
        
        .legal-nav-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1f2937;
        }
        
        .legal-nav-links a {
            color: #6b7280;
            text-decoration: none;
            font-size: 0.875rem;
            margin: 0 0.75rem;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            transition: all 0.2s;
        }
        
        .legal-nav-links a:hover {
            background: #f3f4f6;
            color: #374151;
        }
        
        .legal-footer {
            margin-top: 3rem;
            padding: 2rem 0;
            border-top: 1px solid #e5e7eb;
            text-align: center;
        }
        
        .legal-footer-links {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }
        
        .legal-footer-links a {
            color: #6b7280;
            text-decoration: none;
            font-size: 0.875rem;
        }
        
        .legal-footer-links a:hover {
            color: #2563eb;
        }
        
        .legal-footer-info {
            color: #9ca3af;
            font-size: 0.75rem;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .legal-container {
                padding: 1rem;
            }
            
            .legal-document {
                padding: 2rem 1.5rem;
            }
            
            .legal-title {
                font-size: 2rem;
            }
            
            .legal-nav-container {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .legal-footer-links {
                flex-direction: column;
                gap: 0.5rem;
            }
        }
        
        /* Print Styles */
        @media print {
            body {
                background: white;
                font-size: 12pt;
                line-height: 1.5;
            }
            
            .legal-nav, .legal-footer {
                display: none;
            }
            
            .legal-document {
                box-shadow: none;
                border: none;
                padding: 0;
            }
            
            .legal-content h2 {
                page-break-after: avoid;
            }
        }
        
        /* Accessibility */
        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
        
        /* Focus styles for accessibility */
        a:focus, button:focus {
            outline: 2px solid #2563eb;
            outline-offset: 2px;
        }
    </style>
</head>
<body>
    
    <!-- Navigation -->
    <nav class="legal-nav" role="navigation" aria-label="Legal document navigation">
        <div class="legal-nav-container">
            <div class="legal-nav-logo">
                <a href="{{ route('home') }}" aria-label="Go to HD Tickets homepage">
                    <img src="{{ asset('assets/images/hdTicketsLogo.png') }}" alt="HD Tickets Logo" width="32" height="32">
                    <span class="legal-nav-title">HD Tickets</span>
                </a>
            </div>
            
            <div class="legal-nav-links">
                <a href="{{ route('home') }}">Home</a>
                <a href="{{ route('legal.terms-of-service') }}">Terms</a>
                <a href="{{ route('legal.privacy-policy') }}">Privacy</a>
                <a href="{{ route('legal.disclaimer') }}">Disclaimer</a>
                <a href="{{ route('legal.gdpr-compliance') }}">GDPR</a>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <main id="main-content" class="legal-container" role="main">
        <article class="legal-document" role="article">
            <header class="legal-header">
                <h1 class="legal-title">@yield('document_title')</h1>
                <p class="legal-subtitle">@yield('document_subtitle', 'HD Tickets - Professional Sports Ticket Monitoring Platform')</p>
                @if(isset($document) && $document->effective_date)
                    <p class="legal-updated">
                        Last Updated: <time datetime="{{ $document->effective_date->toISOString() }}">
                            {{ $document->effective_date->format('F j, Y') }}
                        </time>
                        @if($document->version)
                            | Version {{ $document->version }}
                        @endif
                    </p>
                @endif
            </header>
            
            <div class="legal-content" role="document">
                @yield('content')
            </div>
        </article>
        
        <!-- Cross-references to other legal documents -->
        <aside class="mt-8 p-6 bg-gray-50 rounded-lg" role="complementary" aria-label="Related legal documents">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Related Legal Documents</h2>
            <nav class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @unless(request()->routeIs('legal.terms-of-service'))
                    <a href="{{ route('legal.terms-of-service') }}" class="text-blue-600 hover:text-blue-800 text-sm underline">Terms of Service</a>
                @endunless
                @unless(request()->routeIs('legal.privacy-policy'))
                    <a href="{{ route('legal.privacy-policy') }}" class="text-blue-600 hover:text-blue-800 text-sm underline">Privacy Policy</a>
                @endunless
                @unless(request()->routeIs('legal.disclaimer'))
                    <a href="{{ route('legal.disclaimer') }}" class="text-blue-600 hover:text-blue-800 text-sm underline">Service Disclaimer</a>
                @endunless
                @unless(request()->routeIs('legal.gdpr-compliance'))
                    <a href="{{ route('legal.gdpr-compliance') }}" class="text-blue-600 hover:text-blue-800 text-sm underline">GDPR Compliance</a>
                @endunless
                @unless(request()->routeIs('legal.data-processing-agreement'))
                    <a href="{{ route('legal.data-processing-agreement') }}" class="text-blue-600 hover:text-blue-800 text-sm underline">Data Processing Agreement</a>
                @endunless
                @unless(request()->routeIs('legal.cookie-policy'))
                    <a href="{{ route('legal.cookie-policy') }}" class="text-blue-600 hover:text-blue-800 text-sm underline">Cookie Policy</a>
                @endunless
            </nav>
        </aside>
    </main>
    
    <!-- Footer -->
    <footer class="legal-footer" role="contentinfo">
        <div class="legal-footer-links">
            <a href="{{ route('home') }}">Home</a>
            <a href="{{ route('register.public') }}">Register</a>
            <a href="{{ route('login') }}">Login</a>
            <a href="{{ route('legal.terms-of-service') }}">Terms</a>
            <a href="{{ route('legal.privacy-policy') }}">Privacy</a>
            <a href="{{ route('legal.disclaimer') }}">Disclaimer</a>
        </div>
        <div class="legal-footer-info">
            <p>© {{ date('Y') }} HD Tickets. All rights reserved.</p>
            <p>Professional Sports Event Ticket Monitoring Platform</p>
            <p>Service provided "as-is" with no warranty or money-back guarantee.</p>
        </div>
    </footer>
    
    <!-- Compiled JavaScript -->
    @vite(['resources/js/app.js'])
    
    <!-- Legal page specific JavaScript -->
    <script>
        // Table of contents generator
        document.addEventListener('DOMContentLoaded', function() {
            const headings = document.querySelectorAll('.legal-content h2, .legal-content h3');
            if (headings.length > 3) {
                generateTableOfContents(headings);
            }
            
            // Print functionality
            if (window.location.search.includes('print=true')) {
                window.print();
            }
            
            // Smooth scrolling for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
        });
        
        function generateTableOfContents(headings) {
            const toc = document.createElement('nav');
            toc.className = 'table-of-contents bg-blue-50 p-4 rounded-lg mb-6';
            toc.setAttribute('role', 'navigation');
            toc.setAttribute('aria-label', 'Table of contents');
            
            const tocTitle = document.createElement('h2');
            tocTitle.textContent = 'Table of Contents';
            tocTitle.className = 'text-lg font-semibold text-gray-900 mb-3';
            toc.appendChild(tocTitle);
            
            const tocList = document.createElement('ol');
            tocList.className = 'space-y-1 text-sm';
            
            headings.forEach((heading, index) => {
                const id = `heading-${index}`;
                heading.id = id;
                
                const listItem = document.createElement('li');
                const link = document.createElement('a');
                link.href = `#${id}`;
                link.textContent = heading.textContent;
                link.className = heading.tagName === 'H2' ? 'text-blue-600 font-medium' : 'text-blue-500 ml-4';
                
                listItem.appendChild(link);
                tocList.appendChild(listItem);
            });
            
            toc.appendChild(tocList);
            
            const content = document.querySelector('.legal-content');
            content.insertBefore(toc, content.firstChild);
        }
    </script>
</body>
</html>
