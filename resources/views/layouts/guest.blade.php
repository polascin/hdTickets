<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="x-csrf-token" content="{{ csrf_token() }}">
    <meta name="_token" content="{{ csrf_token() }}">
    <meta name="authenticated" content="{{ auth()->check() ? 'true' : 'false' }}">

    <title>{{ config('app.name', 'HD Tickets') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/hdTicketsLogo.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:300,400,500,600,700&display=swap"
      rel="stylesheet" />

    <!-- Inline Critical CSS for Above-the-Fold Content -->
    <style>
      {!! file_get_contents(resource_path('css/critical.css')) !!}
    </style>

    <!-- Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
      @vite(['resources/css/app.css', 'resources/js/app.js'])
      <style>
        /* Cache-busting timestamp: {{ now()->timestamp }} */
        /* Lazy load non-critical styles after critical rendering */
        .non-critical-styles {
          display: none;
        }
      </style>
      <script>
        // Lazy load non-critical CSS after page load
        window.addEventListener('load', function() {
          const link = document.createElement('link');
          link.rel = 'stylesheet';
          link.href = '{{ asset('css/components/hd-backgrounds.css') }}';
          document.head.appendChild(link);
        });
      </script>
    @else
      <style>
        /* HD Tickets Modern Login Design - Cache-busting timestamp: {{ now()->timestamp }} */

        /* Core Layout Styles */
        .min-h-screen {
          min-height: 100vh;
        }

        .flex {
          display: flex;
        }

        .flex-col {
          flex-direction: column;
        }

        .items-center {
          align-items: center;
        }

        .justify-center {
          justify-content: center;
        }

        .w-full {
          width: 100%;
        }

        .h-full {
          height: 100%;
        }

        .font-sans {
          font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .antialiased {
          -webkit-font-smoothing: antialiased;
          -moz-osx-font-smoothing: grayscale;
        }

        /* Modern HD Tickets Background */
        .hd-login-bg {
          background: linear-gradient(135deg,
              #1e40af 0%,
              /* HD Professional Blue */
              #3b82f6 35%,
              /* Lighter blue */
              #8b5cf6 70%,
              /* Purple accent */
              #1e40af 100%
              /* Back to professional blue */
            );
          position: relative;
          overflow: hidden;
        }

        /* Animated Background Pattern */
        .hd-login-bg::before {
          content: '';
          position: absolute;
          inset: 0;
          background-image:
            radial-gradient(circle at 25% 25%, rgba(255, 255, 255, 0.1) 1px, transparent 1px),
            radial-gradient(circle at 75% 75%, rgba(255, 255, 255, 0.1) 1px, transparent 1px);
          background-size: 60px 60px;
          animation: float-pattern 20s ease-in-out infinite;
        }

        /* Sports-themed decorative elements */
        .hd-login-bg::after {
          content: '';
          position: absolute;
          top: 10%;
          right: 10%;
          width: 300px;
          height: 300px;
          background: radial-gradient(circle, rgba(255, 255, 255, 0.05) 0%, transparent 70%);
          border-radius: 50%;
          animation: pulse-glow 6s ease-in-out infinite;
        }

        @keyframes float-pattern {

          0%,
          100% {
            transform: translateY(0) rotate(0deg);
          }

          50% {
            transform: translateY(-10px) rotate(5deg);
          }
        }

        @keyframes pulse-glow {

          0%,
          100% {
            opacity: 0.3;
            transform: scale(1);
          }

          50% {
            opacity: 0.6;
            transform: scale(1.05);
          }
        }

        /* Modern Card Design */
        .hd-login-card {
          background: rgba(255, 255, 255, 0.95);
          backdrop-filter: blur(20px);
          border: 1px solid rgba(255, 255, 255, 0.2);
          border-radius: 24px;
          box-shadow:
            0 25px 50px -12px rgba(0, 0, 0, 0.15),
            0 0 0 1px rgba(255, 255, 255, 0.1);
          padding: 48px 64px;
          width: 100%;
          max-width: 1024px;
          /* Increased to 1024px for proper desktop display */
          position: relative;
          z-index: 1;
          transition: all 0.3s ease;
        }

        .hd-login-card:hover {
          transform: translateY(-2px);
          box-shadow:
            0 32px 64px -12px rgba(0, 0, 0, 0.2),
            0 0 0 1px rgba(255, 255, 255, 0.15);
        }

        /* Logo Styling */
        .hd-logo {
          width: 80px;
          height: 80px;
          margin: 0 auto 24px;
          display: block;
          border-radius: 16px;
          box-shadow: 0 8px 25px rgba(30, 64, 175, 0.15);
          transition: all 0.3s ease;
        }

        .hd-logo:hover {
          transform: scale(1.05);
          box-shadow: 0 12px 35px rgba(30, 64, 175, 0.25);
        }

        /* Brand Title */
        .hd-title {
          font-size: 2rem;
          font-weight: 700;
          color: #1e40af;
          text-align: center;
          margin: 0 0 8px 0;
          letter-spacing: -0.025em;
        }

        /* Tagline Styling */
        .hd-tagline {
          font-size: 0.875rem;
          color: #64748b;
          text-align: center;
          margin: 0 0 32px 0;
          line-height: 1.5;
          font-weight: 500;
        }

        /* Form Styling */
        .space-y-6>*+* {
          margin-top: 24px;
        }

        .space-y-1>*+* {
          margin-top: 4px;
        }

        /* Input Styling */
        .form-input {
          display: block;
          width: 100%;
          padding: 14px 16px;
          font-size: 1rem;
          border: 2px solid #e2e8f0;
          border-radius: 12px;
          background-color: #ffffff;
          color: #1e293b;
          transition: all 0.2s ease;
          font-weight: 500;
        }

        .form-input:focus {
          outline: none;
          border-color: #1e40af;
          box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.1);
          background-color: #ffffff;
        }

        .form-input::placeholder {
          color: #94a3b8;
          font-weight: 400;
        }

        /* Label Styling */
        .form-label {
          display: block;
          font-size: 0.875rem;
          font-weight: 600;
          color: #374151;
          margin-bottom: 6px;
        }

        /* Button Styling */
        .hd-btn-primary {
          display: inline-flex;
          align-items: center;
          justify-content: center;
          width: 100%;
          padding: 14px 24px;
          font-size: 1rem;
          font-weight: 600;
          color: white;
          background: linear-gradient(135deg, #1e40af, #3b82f6);
          border: none;
          border-radius: 12px;
          cursor: pointer;
          transition: all 0.2s ease;
          position: relative;
          overflow: hidden;
          min-height: 52px;
        }

        .hd-btn-primary::before {
          content: '';
          position: absolute;
          inset: 0;
          background: linear-gradient(135deg, #1d4ed8, #2563eb);
          opacity: 0;
          transition: opacity 0.2s ease;
        }

        .hd-btn-primary:hover::before {
          opacity: 1;
        }

        .hd-btn-primary:hover {
          transform: translateY(-1px);
          box-shadow: 0 12px 25px rgba(30, 64, 175, 0.3);
        }

        .hd-btn-primary:active {
          transform: translateY(0);
        }

        .hd-btn-primary span {
          position: relative;
          z-index: 1;
        }

        /* Checkbox Styling */
        .form-checkbox {
          width: 20px;
          height: 20px;
          border: 2px solid #d1d5db;
          border-radius: 4px;
          background-color: #ffffff;
          cursor: pointer;
          margin-right: 12px;
          display: inline-block;
          vertical-align: middle;
          position: relative;
          appearance: none;
          -webkit-appearance: none;
        }

        .form-checkbox:checked {
          background-color: #1e40af;
          border-color: #1e40af;
        }

        /* Checkbox checkmark */
        .form-checkbox:checked::after {
          content: '✓';
          position: absolute;
          top: 50%;
          left: 50%;
          transform: translate(-50%, -50%);
          color: white;
          font-size: 14px;
          font-weight: bold;
        }

        /* Remember me section styling */
        .hd-enhanced-checkbox-wrapper {
          display: flex;
          align-items: center;
          gap: 12px;
        }

        .hd-enhanced-checkbox-wrapper label {
          font-size: 1rem;
          color: #374151;
          cursor: pointer;
          user-select: none;
        }

        /* Link Styling */
        .hd-link {
          color: #1e40af;
          text-decoration: none;
          font-weight: 500;
          transition: all 0.2s ease;
        }

        .hd-link:hover {
          color: #1d4ed8;
          text-decoration: underline;
        }

        /* Alert Styling */
        .hd-alert {
          padding: 16px;
          border-radius: 12px;
          margin-bottom: 24px;
          display: flex;
          align-items: flex-start;
          gap: 12px;
        }

        .hd-alert-info {
          background-color: #eff6ff;
          border: 1px solid #bfdbfe;
          color: #1e40af;
        }

        /* Responsive Design */

        /* Large Desktop (1920px+) - Uses default 1024px max-width */
        @media (min-width: 1920px) {
          .hd-login-card {
            /* Use default max-width: 1024px; */
            padding: 56px 64px;
          }

          .hd-title {
            font-size: 2.25rem;
            margin-bottom: 12px;
          }

          .hd-tagline {
            font-size: 1rem;
            margin-bottom: 40px;
          }

          .hd-logo {
            width: 90px;
            height: 90px;
            margin-bottom: 32px;
          }

          .form-input {
            padding: 16px 18px;
            font-size: 1.0625rem;
          }

          .hd-btn-primary {
            padding: 16px 28px;
            font-size: 1.0625rem;
            min-height: 56px;
          }
        }

        /* Standard Desktop (1024px - 1919px) - Uses default 1024px max-width */
        @media (min-width: 1024px) and (max-width: 1919px) {
          .hd-login-card {
            /* Use default max-width: 1024px; */
            padding: 52px;
          }

          .hd-title {
            font-size: 2.125rem;
          }

          .hd-tagline {
            font-size: 0.9375rem;
            margin-bottom: 36px;
          }

          .hd-logo {
            width: 85px;
            height: 85px;
            margin-bottom: 28px;
          }
        }

        /* Tablet Landscape (768px - 1023px) */
        @media (min-width: 768px) and (max-width: 1023px) {
          .hd-login-card {
            max-width: 420px;
            padding: 44px 40px;
            margin: 24px;
          }

          .hd-title {
            font-size: 1.875rem;
          }

          .hd-tagline {
            font-size: 0.875rem;
            margin-bottom: 32px;
          }

          .hd-logo {
            width: 75px;
            height: 75px;
            margin-bottom: 24px;
          }

          .form-input {
            padding: 15px 16px;
            font-size: 1rem;
          }

          .hd-btn-primary {
            padding: 15px 26px;
            min-height: 50px;
          }
        }

        /* Mobile (320px - 767px) */
        @media (max-width: 767px) {
          .hd-login-card {
            padding: 32px 24px;
            margin: 16px;
            border-radius: 20px;
            max-width: none;
          }

          .hd-title {
            font-size: 1.75rem;
          }

          .hd-tagline {
            font-size: 0.8125rem;
            margin-bottom: 28px;
          }

          .hd-logo {
            width: 70px;
            height: 70px;
          }

          .form-input {
            padding: 16px;
            font-size: 16px;
            /* Prevents zoom on iOS */
            min-height: 48px;
            /* Touch-friendly */
          }

          .hd-btn-primary {
            padding: 16px 24px;
            min-height: 48px;
            /* Touch-friendly */
            font-size: 1rem;
          }

          /* Ensure password toggle button is touch-friendly */
          .absolute.inset-y-0.right-0 {
            min-width: 44px;
            min-height: 44px;
          }

          /* Checkbox touch targets */
          .form-checkbox {
            width: 20px;
            height: 20px;
          }

          /* Remember me section mobile optimization */
          .hd-enhanced-checkbox-wrapper {
            min-height: 44px;
            align-items: center;
          }
        }

        /* Small Mobile (320px - 480px) */
        @media (max-width: 480px) {
          .hd-login-card {
            padding: 28px 20px;
            margin: 12px;
          }

          .hd-title {
            font-size: 1.625rem;
          }

          .hd-tagline {
            font-size: 0.75rem;
            line-height: 1.4;
          }

          .space-y-6>*+* {
            margin-top: 20px;
          }
        }

        /* Utility Classes */
        .text-center {
          text-align: center;
        }

        .flex-items-center {
          display: flex;
          align-items: center;
        }

        .justify-between {
          justify-content: space-between;
        }

        .mt-4 {
          margin-top: 16px;
        }

        .mt-6 {
          margin-top: 24px;
        }

        .text-sm {
          font-size: 0.875rem;
        }

        .font-medium {
          font-weight: 500;
        }

        .relative {
          position: relative;
        }

        .absolute {
          position: absolute;
        }

        .inset-y-0 {
          top: 0;
          bottom: 0;
        }

        .right-0 {
          right: 0;
        }

        .pr-3 {
          padding-right: 12px;
        }

        .pointer-events-none {
          pointer-events: none;
        }

        .h-4 {
          height: 16px;
        }

        .w-4 {
          width: 16px;
        }

        .h-5 {
          height: 20px;
        }

        .w-5 {
          width: 20px;
        }

        .text-gray-400 {
          color: #9ca3af;
        }

        .ml-2 {
          margin-left: 8px;
        }

        .block {
          display: block;
        }
      </style>
    @endif

    <!-- Authentication Security Script -->
    <script src="{{ asset('js/auth-security-new.js') }}"></script>
  </head>

  <body class="font-sans antialiased">
    <div class="min-h-screen flex items-center justify-center hd-login-bg">
      <div class="hd-login-card">
        <!-- HD Tickets Logo with WebP optimization and layout shift prevention -->
        <a href="/" class="block">
          <picture>
            <source srcset="{{ asset('assets/images/hdTicketsLogo.webp') }}" type="image/webp" width="80"
              height="80">
            <img src="{{ asset('assets/images/hdTicketsLogo.png') }}"
              alt="HD Tickets - Sports Events Entry Tickets Monitoring System" class="hd-logo" width="80"
              height="80" loading="eager" decoding="async">
          </picture>
        </a>

        <!-- Brand Title -->
        <h1 class="hd-title">HD Tickets</h1>

        <!-- Tagline -->
        <p class="hd-tagline">
          Comprehensive Sports Events Entry Tickets<br>
          Monitoring, Scraping and Purchase System
        </p>

        <!-- Login Form Content -->
        {{ $slot }}
      </div>
    </div>
  </body>

</html>
