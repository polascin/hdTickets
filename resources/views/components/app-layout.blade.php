<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'HD Tickets') }} - @isset($title){{ $title }}@else Dashboard @endisset</title>
        <meta name="description" content="Professional sports ticket monitoring and alerting platform">
        <link rel="icon" type="image/png" href="{{ asset('assets/images/hdTicketsLogo.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="{{ css_with_timestamp('https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap') }}" rel="stylesheet" />
        
        <!-- Bootstrap CSS -->
        <link href="{{ css_with_timestamp('https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css') }}" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

        <!-- Accessibility Styles -->
        <link href="{{ asset('css/hd-accessibility.css') }}" rel="stylesheet">
        
        <!-- Scripts -->
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <style>
                /* Comprehensive Tailwind CSS fallback for modern dashboard */
                .font-sans { font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif; }
                .antialiased { -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; }
                .min-h-screen { min-height: 100vh; }
                .bg-gray-100 { background-color: #f3f4f6; }
                .bg-white { background-color: #ffffff; }
                .shadow { box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06); }
                .max-w-7xl { max-width: 80rem; }
                .mx-auto { margin-left: auto; margin-right: auto; }
                .py-6 { padding-top: 1.5rem; padding-bottom: 1.5rem; }
                .py-8 { padding-top: 2rem; padding-bottom: 2rem; }
                .px-4 { padding-left: 1rem; padding-right: 1rem; }
                .sm\:px-6 { padding-left: 1.5rem; padding-right: 1.5rem; }
                .lg\:px-8 { padding-left: 2rem; padding-right: 2rem; }
                @media (min-width: 640px) { .sm\:px-6 { padding-left: 1.5rem; padding-right: 1.5rem; } }
                @media (min-width: 1024px) { .lg\:px-8 { padding-left: 2rem; padding-right: 2rem; } }
                
                /* Dashboard specific styles */
                .dashboard-card { background: white; border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); padding: 1.5rem; transition: all 0.2s; }
                .dashboard-card:hover { box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); transform: translateY(-1px); }
                
                /* Navigation enhancements */
                .nav-shadow { box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); }
                .nav-dropdown { backdrop-filter: blur(8px); }
                .nav-item-active { position: relative; }
                .nav-item-active::after { content: ''; position: absolute; bottom: -2px; left: 0; right: 0; height: 2px; background: linear-gradient(90deg, #3b82f6, #1d4ed8); border-radius: 1px; }
                .mobile-nav-open { max-height: 100vh; overflow-y: auto; }
                .mobile-nav-closed { max-height: 0; overflow: hidden; }
                .stat-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
                .stat-value { font-size: 2.25rem; font-weight: 700; color: white; }
                .stat-label { color: rgba(255, 255, 255, 0.9); font-size: 0.875rem; }
                .chart-container { position: relative; height: 300px; }
                .grid-cols-1 { display: grid; grid-template-columns: repeat(1, minmax(0, 1fr)); gap: 1.5rem; }
                .grid-cols-2 { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1.5rem; }
                .grid-cols-3 { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1.5rem; }
                .grid-cols-4 { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 1.5rem; }
                @media (min-width: 768px) { .md\:grid-cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); } .md\:grid-cols-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); } .md\:grid-cols-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
                @media (min-width: 1024px) { .lg\:grid-cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); } .lg\:grid-cols-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); } .lg\:grid-cols-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); } }

                /* Comprehensive utility classes */
                .text-xs { font-size: 0.75rem; line-height: 1rem; }
                .text-sm { font-size: 0.875rem; line-height: 1.25rem; }
                .text-base { font-size: 1rem; line-height: 1.5rem; }
                .text-lg { font-size: 1.125rem; line-height: 1.75rem; }
                .text-xl { font-size: 1.25rem; line-height: 1.75rem; }
                .text-2xl { font-size: 1.5rem; line-height: 2rem; }
                .text-3xl { font-size: 1.875rem; line-height: 2.25rem; }
                
                .font-normal { font-weight: 400; }
                .font-medium { font-weight: 500; }
                .font-semibold { font-weight: 600; }
                .font-bold { font-weight: 700; }
                
                .text-gray-50 { color: #f9fafb; }
                .text-gray-100 { color: #f3f4f6; }
                .text-gray-200 { color: #e5e7eb; }
                .text-gray-300 { color: #d1d5db; }
                .text-gray-400 { color: #9ca3af; }
                .text-gray-500 { color: #6b7280; }
                .text-gray-600 { color: #4b5563; }
                .text-gray-700 { color: #374151; }
                .text-gray-800 { color: #1f2937; }
                .text-gray-900 { color: #111827; }

                /* Background colors */
                .bg-gray-50 { background-color: #f9fafb; }
                .bg-gray-100 { background-color: #f3f4f6; }
                .bg-gray-200 { background-color: #e5e7eb; }
                .bg-gray-300 { background-color: #d1d5db; }
                .bg-gray-400 { background-color: #9ca3af; }
                .bg-gray-500 { background-color: #6b7280; }
                .bg-gray-600 { background-color: #4b5563; }
                .bg-gray-700 { background-color: #374151; }
                .bg-gray-800 { background-color: #1f2937; }
                .bg-gray-900 { background-color: #111827; }

                /* Colored backgrounds and text */
                .bg-blue-50 { background-color: #eff6ff; }
                .bg-blue-100 { background-color: #dbeafe; }
                .bg-blue-200 { background-color: #bfdbfe; }
                .bg-blue-500 { background-color: #3b82f6; }
                .bg-blue-600 { background-color: #2563eb; }
                .bg-blue-700 { background-color: #1d4ed8; }
                .text-blue-600 { color: #2563eb; }
                .text-blue-700 { color: #1d4ed8; }
                .text-blue-800 { color: #1e40af; }

                .bg-green-50 { background-color: #f0fdf4; }
                .bg-green-100 { background-color: #dcfce7; }
                .bg-green-200 { background-color: #bbf7d0; }
                .bg-green-500 { background-color: #22c55e; }
                .bg-green-600 { background-color: #16a34a; }
                .bg-green-700 { background-color: #15803d; }
                .text-green-600 { color: #16a34a; }
                .text-green-700 { color: #15803d; }
                .text-green-800 { color: #166534; }

                .bg-yellow-50 { background-color: #fefce8; }
                .bg-yellow-100 { background-color: #fef3c7; }
                .bg-yellow-200 { background-color: #fde68a; }
                .bg-yellow-500 { background-color: #eab308; }
                .bg-yellow-600 { background-color: #ca8a04; }
                .bg-yellow-700 { background-color: #a16207; }
                .text-yellow-600 { color: #ca8a04; }
                .text-yellow-700 { color: #a16207; }
                .text-yellow-800 { color: #92400e; }

                .bg-red-50 { background-color: #fef2f2; }
                .bg-red-100 { background-color: #fee2e2; }
                .bg-red-200 { background-color: #fecaca; }
                .bg-red-400 { background-color: #f87171; }
                .bg-red-500 { background-color: #ef4444; }
                .bg-red-600 { background-color: #dc2626; }
                .bg-red-700 { background-color: #b91c1c; }
                .text-red-400 { color: #f87171; }
                .text-red-600 { color: #dc2626; }
                .text-red-700 { color: #b91c1c; }
                .text-red-800 { color: #991b1b; }

                .bg-purple-100 { background-color: #f3e8ff; }
                .bg-purple-500 { background-color: #a855f7; }
                .bg-purple-600 { background-color: #9333ea; }
                .bg-purple-700 { background-color: #7c3aed; }
                .text-purple-700 { color: #7c3aed; }

                .bg-indigo-100 { background-color: #e0e7ff; }
                .bg-indigo-500 { background-color: #6366f1; }
                .bg-indigo-600 { background-color: #4f46e5; }
                .bg-indigo-700 { background-color: #4338ca; }
                .text-indigo-700 { color: #4338ca; }

                .bg-orange-100 { background-color: #fed7aa; }
                .bg-orange-500 { background-color: #f97316; }
                .bg-orange-600 { background-color: #ea580c; }
                .bg-orange-700 { background-color: #c2410c; }
                .text-orange-700 { color: #c2410c; }

                .bg-teal-100 { background-color: #ccfbf1; }
                .bg-teal-200 { background-color: #99f6e4; }
                .text-teal-700 { color: #0f766e; }

                .bg-amber-600 { background-color: #d97706; }
                .text-amber-600 { color: #d97706; }

                /* Layout and spacing */
                .rounded { border-radius: 0.25rem; }
                .rounded-md { border-radius: 0.375rem; }
                .rounded-lg { border-radius: 0.5rem; }
                .rounded-xl { border-radius: 0.75rem; }
                .rounded-full { border-radius: 9999px; }
                
                .p-1 { padding: 0.25rem; }
                .p-2 { padding: 0.5rem; }
                .p-3 { padding: 0.75rem; }
                .p-4 { padding: 1rem; }
                .p-5 { padding: 1.25rem; }
                .p-6 { padding: 1.5rem; }
                .px-2 { padding-left: 0.5rem; padding-right: 0.5rem; }
                .px-3 { padding-left: 0.75rem; padding-right: 0.75rem; }
                .px-4 { padding-left: 1rem; padding-right: 1rem; }
                .px-6 { padding-left: 1.5rem; padding-right: 1.5rem; }
                .py-1 { padding-top: 0.25rem; padding-bottom: 0.25rem; }
                .py-2 { padding-top: 0.5rem; padding-bottom: 0.5rem; }
                .py-3 { padding-top: 0.75rem; padding-bottom: 0.75rem; }
                .py-4 { padding-top: 1rem; padding-bottom: 1rem; }
                
                .m-1 { margin: 0.25rem; }
                .m-2 { margin: 0.5rem; }
                .m-4 { margin: 1rem; }
                .mb-1 { margin-bottom: 0.25rem; }
                .mb-2 { margin-bottom: 0.5rem; }
                .mb-4 { margin-bottom: 1rem; }
                .mb-6 { margin-bottom: 1.5rem; }
                .mt-2 { margin-top: 0.5rem; }
                .mt-4 { margin-top: 1rem; }
                .mt-6 { margin-top: 1.5rem; }
                .mr-1 { margin-right: 0.25rem; }
                .mr-2 { margin-right: 0.5rem; }
                .ml-2 { margin-left: 0.5rem; }
                .ml-4 { margin-left: 1rem; }

                /* Flexbox */
                .flex { display: flex; }
                .flex-col { flex-direction: column; }
                .flex-row { flex-direction: row; }
                .flex-wrap { flex-wrap: wrap; }
                .items-start { align-items: flex-start; }
                .items-center { align-items: center; }
                .items-end { align-items: flex-end; }
                .justify-start { justify-content: flex-start; }
                .justify-center { justify-content: center; }
                .justify-end { justify-content: flex-end; }
                .justify-between { justify-content: space-between; }
                .flex-shrink-0 { flex-shrink: 0; }

                /* Spacing between elements */
                .space-x-1 > * + * { margin-left: 0.25rem; }
                .space-x-2 > * + * { margin-left: 0.5rem; }
                .space-x-3 > * + * { margin-left: 0.75rem; }
                .space-x-4 > * + * { margin-left: 1rem; }
                .space-y-2 > * + * { margin-top: 0.5rem; }
                .space-y-4 > * + * { margin-top: 1rem; }
                .gap-1 { gap: 0.25rem; }
                .gap-2 { gap: 0.5rem; }
                .gap-4 { gap: 1rem; }

                /* Sizing */
                .w-3 { width: 0.75rem; }
                .w-4 { width: 1rem; }
                .w-5 { width: 1.25rem; }
                .w-6 { width: 1.5rem; }
                .w-8 { width: 2rem; }
                .w-10 { width: 2.5rem; }
                .w-12 { width: 3rem; }
                .w-16 { width: 4rem; }
                .w-20 { width: 5rem; }
                .w-24 { width: 6rem; }
                .w-32 { width: 8rem; }
                .w-48 { width: 12rem; }
                .w-64 { width: 16rem; }
                .w-full { width: 100%; }
                .h-3 { height: 0.75rem; }
                .h-4 { height: 1rem; }
                .h-5 { height: 1.25rem; }
                .h-6 { height: 1.5rem; }
                .h-8 { height: 2rem; }
                .h-10 { height: 2.5rem; }
                .h-12 { height: 3rem; }
                .h-16 { height: 4rem; }
                .h-full { height: 100%; }
                .min-w-full { min-width: 100%; }

                /* Borders */
                .border { border-width: 1px; }
                .border-2 { border-width: 2px; }
                .border-4 { border-width: 4px; }
                .border-l-4 { border-left-width: 4px; }
                .border-t { border-top-width: 1px; }
                .border-b { border-bottom-width: 1px; }
                .border-gray-100 { border-color: #f3f4f6; }
                .border-gray-200 { border-color: #e5e7eb; }
                .border-gray-300 { border-color: #d1d5db; }
                .border-green-400 { border-color: #4ade80; }
                .border-red-400 { border-color: #f87171; }
                .divide-y > * + * { border-top-width: 1px; border-color: #e5e7eb; }

                /* Shadows and effects */
                .shadow-sm { box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); }
                .shadow { box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06); }
                .shadow-md { box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); }
                .shadow-lg { box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); }

                /* Hover effects */
                .hover\:bg-gray-50:hover { background-color: #f9fafb; }
                .hover\:bg-gray-100:hover { background-color: #f3f4f6; }
                .hover\:bg-gray-200:hover { background-color: #e5e7eb; }
                .hover\:bg-gray-400:hover { background-color: #9ca3af; }
                .hover\:bg-blue-200:hover { background-color: #bfdbfe; }
                .hover\:bg-blue-600:hover { background-color: #2563eb; }
                .hover\:bg-blue-700:hover { background-color: #1d4ed8; }
                .hover\:bg-green-200:hover { background-color: #bbf7d0; }
                .hover\:bg-green-600:hover { background-color: #16a34a; }
                .hover\:bg-green-700:hover { background-color: #15803d; }
                .hover\:bg-yellow-200:hover { background-color: #fde68a; }
                .hover\:bg-yellow-700:hover { background-color: #a16207; }
                .hover\:bg-red-200:hover { background-color: #fecaca; }
                .hover\:bg-red-700:hover { background-color: #b91c1c; }
                .hover\:bg-purple-200:hover { background-color: #e9d5ff; }
                .hover\:bg-purple-600:hover { background-color: #9333ea; }
                .hover\:bg-purple-700:hover { background-color: #7c3aed; }
                .hover\:bg-indigo-200:hover { background-color: #c7d2fe; }
                .hover\:bg-indigo-600:hover { background-color: #4f46e5; }
                .hover\:bg-indigo-700:hover { background-color: #4338ca; }
                .hover\:bg-orange-200:hover { background-color: #fed7aa; }
                .hover\:bg-orange-600:hover { background-color: #ea580c; }
                .hover\:bg-orange-700:hover { background-color: #c2410c; }
                .hover\:bg-teal-200:hover { background-color: #99f6e4; }
                .hover\:text-blue-600:hover { color: #2563eb; }
                .hover\:shadow-lg:hover { box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); }
                .hover\:scale-105:hover { transform: scale(1.05); }

                /* Focus effects */
                .focus\:outline-none:focus { outline: 2px solid transparent; outline-offset: 2px; }
                .focus\:ring-1:focus { box-shadow: 0 0 0 1px rgba(59, 130, 246, 0.5); }
                .focus\:ring-2:focus { box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5); }
                .focus\:ring-blue-500:focus { box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5); }
                .focus\:ring-indigo-500:focus { box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.5); }
                .focus\:ring-green-500:focus { box-shadow: 0 0 0 2px rgba(34, 197, 94, 0.5); }
                .focus\:ring-purple-500:focus { box-shadow: 0 0 0 2px rgba(168, 85, 247, 0.5); }
                .focus\:ring-orange-500:focus { box-shadow: 0 0 0 2px rgba(249, 115, 22, 0.5); }
                .focus\:ring-offset-2:focus { box-shadow: 0 0 0 2px white, 0 0 0 4px rgba(59, 130, 246, 0.5); }
                .focus\:border-blue-500:focus { border-color: #3b82f6; }
                .focus\:border-indigo-500:focus { border-color: #6366f1; }

                /* Table styles */
                .table-fixed { table-layout: fixed; }
                .whitespace-nowrap { white-space: nowrap; }
                .divide-y { border-collapse: separate; }

                /* Overflow */
                .overflow-hidden { overflow: hidden; }
                .overflow-x-auto { overflow-x: auto; }
                .overflow-y-auto { overflow-y: auto; }

                /* Position */
                .relative { position: relative; }
                .absolute { position: absolute; }
                .fixed { position: fixed; }
                .inset-0 { top: 0; right: 0; bottom: 0; left: 0; }
                .inset-y-0 { top: 0; bottom: 0; }
                .top-20 { top: 5rem; }
                .left-0 { left: 0; }
                .right-0 { right: 0; }
                .bottom-0 { bottom: 0; }
                .z-50 { z-index: 50; }

                /* Transform */
                .transform { transform: translateX(var(--tw-translate-x, 0)) translateY(var(--tw-translate-y, 0)) rotate(var(--tw-rotate, 0)) skewX(var(--tw-skew-x, 0)) skewY(var(--tw-skew-y, 0)) scaleX(var(--tw-scale-x, 1)) scaleY(var(--tw-scale-y, 1)); }
                .transition { transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter; transition-duration: 150ms; transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1); }
                .transition-colors { transition-property: color, background-color, border-color, text-decoration-color, fill, stroke; transition-duration: 150ms; transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1); }
                .duration-200 { transition-duration: 200ms; }

                /* Miscellaneous */
                .cursor-pointer { cursor: pointer; }
                .select-none { user-select: none; }
                .leading-tight { line-height: 1.25; }
                .leading-5 { line-height: 1.25rem; }
                .animate-pulse { animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
                @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: .5; } }
                .truncate { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
                .block { display: block; }
                .inline { display: inline; }
                .inline-block { display: inline-block; }
                .inline-flex { display: inline-flex; }
                .table { display: table; }
                .hidden { display: none; }
                .uppercase { text-transform: uppercase; }
                .lowercase { text-transform: lowercase; }
                .capitalize { text-transform: capitalize; }
                .tracking-wider { letter-spacing: 0.05em; }

                /* Gradients */
                .bg-gradient-to-r { background-image: linear-gradient(to right, var(--tw-gradient-stops)); }
                .from-blue-500 { --tw-gradient-from: #3b82f6; --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to, rgba(59, 130, 246, 0)); }
                .from-blue-100 { --tw-gradient-from: #dbeafe; --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to, rgba(219, 234, 254, 0)); }
                .from-green-100 { --tw-gradient-from: #dcfce7; --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to, rgba(220, 252, 231, 0)); }
                .from-red-100 { --tw-gradient-from: #fee2e2; --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to, rgba(254, 226, 226, 0)); }
                .from-purple-500 { --tw-gradient-from: #a855f7; --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to, rgba(168, 85, 247, 0)); }
                .from-orange-500 { --tw-gradient-from: #f97316; --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to, rgba(249, 115, 22, 0)); }
                .from-green-500 { --tw-gradient-from: #22c55e; --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to, rgba(34, 197, 94, 0)); }
                .from-gray-50 { --tw-gradient-from: #f9fafb; --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to, rgba(249, 250, 251, 0)); }
                .to-indigo-600 { --tw-gradient-to: #4f46e5; }
                .to-blue-200 { --tw-gradient-to: #bfdbfe; }
                .to-green-200 { --tw-gradient-to: #bbf7d0; }
                .to-red-200 { --tw-gradient-to: #fecaca; }
                .to-purple-600 { --tw-gradient-to: #9333ea; }
                .to-orange-600 { --tw-gradient-to: #ea580c; }
                .to-green-600 { --tw-gradient-to: #16a34a; }
                .to-gray-100 { --tw-gradient-to: #f3f4f6; }

                /* Mobile-first responsive enhancements */
                @media (max-width: 768px) {
                    /* Header adjustments */
                    .header-mobile { flex-direction: column; gap: 1rem; }
                    .button-mobile { width: 100%; justify-content: center; min-height: 48px; }
                    
                    /* Touch-friendly form elements */
                    input, select, textarea, button { min-height: 48px !important; font-size: 16px !important; }
                    
                    /* Table responsive behavior */
                    .table-mobile { display: block; overflow-x: auto; white-space: nowrap; }
                    .table-mobile table { min-width: 100%; }
                    
                    /* Card layout mobile optimization */
                    .card-mobile { margin: 0.5rem; border-radius: 0.75rem; }
                    .card-grid-mobile { grid-template-columns: 1fr; gap: 1rem; }
                    
                    /* Modal mobile optimization */
                    .modal-mobile { margin: 1rem; width: calc(100% - 2rem); max-width: none; }
                    
                    /* Action buttons mobile layout */
                    .actions-mobile { flex-direction: column; gap: 0.5rem; }
                    .actions-mobile button, .actions-mobile a { width: 100%; text-align: center; }
                    
                    /* Search and filter mobile */
                    .search-mobile { grid-template-columns: 1fr; }
                    
                    /* Pagination mobile */
                    .pagination-mobile { flex-direction: column; text-align: center; gap: 1rem; }
                }
                
                /* Alpine.js x-cloak directive */
                [x-cloak] { display: none !important; }
                
                /* Touch-friendly improvements for all screen sizes */
                button, a[role="button"], input[type="button"], input[type="submit"] {
                    min-height: 44px;
                    padding: 0.75rem 1rem;
                    touch-action: manipulation;
                }
                
                /* Improved tap targets */
                .tap-target { min-height: 44px; min-width: 44px; }
                
                /* Better spacing for mobile */
                @media (max-width: 640px) {
                    .mobile-padding { padding: 1rem; }
                    .mobile-margin { margin: 0.5rem; }
                    .mobile-text { font-size: 0.875rem; }
                }
            </style>
        @endif
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main id="main-content" role="main">
                {{ $slot }}
            </main>
        </div>
        
        <!-- Accessibility JavaScript Module -->
        <script src="{{ asset('js/hd-accessibility.js') }}" defer></script>
        
        <!-- Password Toggle Functionality -->
        <script>
            function togglePasswordVisibility(inputId) {
                const passwordInput = document.getElementById(inputId);
                const toggleButton = document.getElementById(inputId + '-toggle');
                const toggleDescription = document.getElementById(inputId + '-toggle-description');
                const passwordIcon = document.getElementById(inputId + '-icon');
                
                if (passwordInput && toggleButton) {
                    const isPassword = passwordInput.type === 'password';
                    
                    // Toggle input type
                    passwordInput.type = isPassword ? 'text' : 'password';
                    
                    // Update button label
                    toggleButton.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
                    
                    // Update screen reader description
                    if (toggleDescription) {
                        toggleDescription.textContent = `Click to toggle password visibility. Current state: ${isPassword ? 'visible' : 'hidden'}`;
                    }
                    
                    // Update icon
                    if (passwordIcon) {
                        if (isPassword) {
                            // Eye icon (visible)
                            passwordIcon.innerHTML = '<title>Hide password</title><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>';
                        } else {
                            // Eye-slash icon (hidden)
                            passwordIcon.innerHTML = '<title>Show password</title><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L12 12m-3.122-3.122l4.243 4.243M12 12l6.12 6.12M12 12L9.88 9.88m8.242 8.242L21 21M12 12l2.88-2.88"></path>';
                        }
                    }
                    
                    // Announce state change to screen readers
                    if (window.hdAccessibility) {
                        window.hdAccessibility.announceToScreenReader(
                            `Password is now ${isPassword ? 'visible' : 'hidden'}`,
                            'polite'
                        );
                    }
                }
            }
            
            // Enhanced form submission with accessibility announcements
            document.addEventListener('DOMContentLoaded', function() {
                const forms = document.querySelectorAll('form.hd-form');
                
                forms.forEach(form => {
                    form.addEventListener('submit', function(e) {
                        const submitButton = form.querySelector('button[type="submit"]');
                        const loadingElement = form.querySelector('#login-loading');
                        
                        if (submitButton && loadingElement) {
                            submitButton.disabled = true;
                            submitButton.setAttribute('aria-busy', 'true');
                            loadingElement.textContent = 'Signing you in, please wait...';
                            
                            // Re-enable button after timeout (in case of client-side validation errors)
                            setTimeout(() => {
                                if (submitButton.disabled) {
                                    submitButton.disabled = false;
                                    submitButton.setAttribute('aria-busy', 'false');
                                    loadingElement.textContent = '';
                                }
                            }, 5000);
                        }
                    });
                });
            });
        </script>
    </body>
</html>
