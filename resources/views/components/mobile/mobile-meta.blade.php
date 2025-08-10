@props([
    'enableZoom' => false,
    'initialScale' => 1.0,
    'minimumScale' => 1.0,
    'maximumScale' => 5.0,
    'userScalable' => false,
    'width' => 'device-width',
    'shrinkToFit' => false,
    'viewportFit' => 'cover'
])

@php
    // Build viewport meta content
    $viewportContent = "width={$width}, initial-scale={$initialScale}";
    
    if ($enableZoom) {
        $viewportContent .= ", minimum-scale={$minimumScale}, maximum-scale={$maximumScale}, user-scalable=yes";
    } else {
        $viewportContent .= ", user-scalable=no";
    }
    
    if ($shrinkToFit) {
        $viewportContent .= ", shrink-to-fit=yes";
    }
    
    if ($viewportFit) {
        $viewportContent .= ", viewport-fit={$viewportFit}";
    }
@endphp

<!-- Mobile Viewport Meta Tags -->
<meta name="viewport" content="{{ $viewportContent }}">

<!-- iOS Specific Meta Tags -->
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="HD Tickets">
<meta name="apple-touch-fullscreen" content="yes">

<!-- Android Chrome Meta Tags -->
<meta name="mobile-web-app-capable" content="yes">
<meta name="theme-color" content="#3b82f6">
<meta name="msapplication-navbutton-color" content="#3b82f6">

<!-- Format Detection -->
<meta name="format-detection" content="telephone=no, date=no, address=no, email=no">

<!-- Touch Icons and Splash Screens -->
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/images/pwa/apple-touch-icon.png') }}">
<link rel="apple-touch-icon" sizes="152x152" href="{{ asset('assets/images/pwa/apple-touch-icon-152x152.png') }}">
<link rel="apple-touch-icon" sizes="144x144" href="{{ asset('assets/images/pwa/apple-touch-icon-144x144.png') }}">
<link rel="apple-touch-icon" sizes="120x120" href="{{ asset('assets/images/pwa/apple-touch-icon-120x120.png') }}">
<link rel="apple-touch-icon" sizes="114x114" href="{{ asset('assets/images/pwa/apple-touch-icon-114x114.png') }}">
<link rel="apple-touch-icon" sizes="76x76" href="{{ asset('assets/images/pwa/apple-touch-icon-76x76.png') }}">
<link rel="apple-touch-icon" sizes="72x72" href="{{ asset('assets/images/pwa/apple-touch-icon-72x72.png') }}">
<link rel="apple-touch-icon" sizes="60x60" href="{{ asset('assets/images/pwa/apple-touch-icon-60x60.png') }}">
<link rel="apple-touch-icon" sizes="57x57" href="{{ asset('assets/images/pwa/apple-touch-icon-57x57.png') }}">

<!-- iOS Splash Screens -->
<link rel="apple-touch-startup-image" href="{{ asset('assets/images/pwa/apple-launch-2048x2732.png') }}" media="(device-width: 1024px) and (device-height: 1366px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
<link rel="apple-touch-startup-image" href="{{ asset('assets/images/pwa/apple-launch-2732x2048.png') }}" media="(device-width: 1024px) and (device-height: 1366px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
<link rel="apple-touch-startup-image" href="{{ asset('assets/images/pwa/apple-launch-1668x2388.png') }}" media="(device-width: 834px) and (device-height: 1194px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
<link rel="apple-touch-startup-image" href="{{ asset('assets/images/pwa/apple-launch-2388x1668.png') }}" media="(device-width: 834px) and (device-height: 1194px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
<link rel="apple-touch-startup-image" href="{{ asset('assets/images/pwa/apple-launch-1536x2048.png') }}" media="(device-width: 768px) and (device-height: 1024px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
<link rel="apple-touch-startup-image" href="{{ asset('assets/images/pwa/apple-launch-2048x1536.png') }}" media="(device-width: 768px) and (device-height: 1024px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
<link rel="apple-touch-startup-image" href="{{ asset('assets/images/pwa/apple-launch-1242x2688.png') }}" media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
<link rel="apple-touch-startup-image" href="{{ asset('assets/images/pwa/apple-launch-2688x1242.png') }}" media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)">
<link rel="apple-touch-startup-image" href="{{ asset('assets/images/pwa/apple-launch-1125x2436.png') }}" media="(device-width: 375px) and (device-height: 812px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
<link rel="apple-touch-startup-image" href="{{ asset('assets/images/pwa/apple-launch-2436x1125.png') }}" media="(device-width: 375px) and (device-height: 812px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)">
<link rel="apple-touch-startup-image" href="{{ asset('assets/images/pwa/apple-launch-828x1792.png') }}" media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
<link rel="apple-touch-startup-image" href="{{ asset('assets/images/pwa/apple-launch-1792x828.png') }}" media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
<link rel="apple-touch-startup-image" href="{{ asset('assets/images/pwa/apple-launch-1242x2208.png') }}" media="(device-width: 414px) and (device-height: 736px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)">
<link rel="apple-touch-startup-image" href="{{ asset('assets/images/pwa/apple-launch-2208x1242.png') }}" media="(device-width: 414px) and (device-height: 736px) and (-webkit-device-pixel-ratio: 3) and (orientation: landscape)">
<link rel="apple-touch-startup-image" href="{{ asset('assets/images/pwa/apple-launch-750x1334.png') }}" media="(device-width: 375px) and (device-height: 667px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
<link rel="apple-touch-startup-image" href="{{ asset('assets/images/pwa/apple-launch-1334x750.png') }}" media="(device-width: 375px) and (device-height: 667px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">
<link rel="apple-touch-startup-image" href="{{ asset('assets/images/pwa/apple-launch-640x1136.png') }}" media="(device-width: 320px) and (device-height: 568px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)">
<link rel="apple-touch-startup-image" href="{{ asset('assets/images/pwa/apple-launch-1136x640.png') }}" media="(device-width: 320px) and (device-height: 568px) and (-webkit-device-pixel-ratio: 2) and (orientation: landscape)">

<!-- Microsoft Tile Icons -->
<meta name="msapplication-TileColor" content="#3b82f6">
<meta name="msapplication-TileImage" content="{{ asset('assets/images/pwa/ms-icon-144x144.png') }}">
<meta name="msapplication-config" content="{{ asset('browserconfig.xml') }}">

<!-- Android Chrome Icons -->
<link rel="icon" type="image/png" sizes="192x192" href="{{ asset('assets/images/pwa/android-icon-192x192.png') }}">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/images/pwa/favicon-32x32.png') }}">
<link rel="icon" type="image/png" sizes="96x96" href="{{ asset('assets/images/pwa/favicon-96x96.png') }}">
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/images/pwa/favicon-16x16.png') }}">

<!-- Preconnect to improve loading performance -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<!-- Mobile-specific styles and scripts will be injected here -->
@if(request()->header('User-Agent') && (strpos(request()->header('User-Agent'), 'Mobile') !== false || strpos(request()->header('User-Agent'), 'Android') !== false))
<style>
/* Mobile-specific critical CSS */
body {
    -webkit-text-size-adjust: 100%;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    touch-action: manipulation;
}

/* Prevent zoom on iOS form inputs */
input[type="color"],
input[type="date"],
input[type="datetime"],
input[type="datetime-local"],
input[type="email"],
input[type="month"],
input[type="number"],
input[type="password"],
input[type="search"],
input[type="tel"],
input[type="text"],
input[type="time"],
input[type="url"],
input[type="week"],
select:focus,
textarea {
    font-size: 16px !important;
}

/* Safe area adjustments */
.safe-area-top {
    padding-top: constant(safe-area-inset-top);
    padding-top: env(safe-area-inset-top);
}

.safe-area-bottom {
    padding-bottom: constant(safe-area-inset-bottom);
    padding-bottom: env(safe-area-inset-bottom);
}

.safe-area-left {
    padding-left: constant(safe-area-inset-left);
    padding-left: env(safe-area-inset-left);
}

.safe-area-right {
    padding-right: constant(safe-area-inset-right);
    padding-right: env(safe-area-inset-right);
}

/* Improved touch targets */
.touch-target {
    min-height: 44px;
    min-width: 44px;
}

.touch-target-lg {
    min-height: 56px;
    min-width: 56px;
}

/* Connection indicator */
.connection-indicator {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background-color: #10b981;
    animation: pulse 2s infinite;
}

.connection-indicator.offline {
    background-color: #ef4444;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}
</style>

<script>
// Critical mobile JavaScript
(function() {
    // Prevent zoom on double tap
    let lastTouchEnd = 0;
    document.addEventListener('touchend', function (event) {
        const now = (new Date()).getTime();
        if (now - lastTouchEnd <= 300) {
            event.preventDefault();
        }
        lastTouchEnd = now;
    }, false);
    
    // Update connection status
    function updateConnectionStatus() {
        const indicators = document.querySelectorAll('.connection-indicator');
        indicators.forEach(indicator => {
            if (navigator.onLine) {
                indicator.classList.remove('offline');
            } else {
                indicator.classList.add('offline');
            }
        });
    }
    
    // Listen for connection changes
    window.addEventListener('online', updateConnectionStatus);
    window.addEventListener('offline', updateConnectionStatus);
    
    // Initial connection status
    document.addEventListener('DOMContentLoaded', updateConnectionStatus);
})();
</script>
@endif
