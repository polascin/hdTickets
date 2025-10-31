# HD Tickets Marketing UI Implementation Guide

## Progress: 6/27 Tasks Completed

### ✅ Completed
1. Feature branch created: `feat/marketing-ui-parity`
2. TicketScoutie assets catalogued
3. Branding assets prepared in `public/assets/branding/`
4. Tailwind config extended with animations, spacing, fonts
5. `resources/css/marketing.css` created with component classes
6. `resources/js/marketing.js` created with Alpine helpers
7. Vite config updated to build marketing assets
8. Marketing layout created: `resources/views/layouts/marketing.blade.php`

### 🔨 Next Steps (Priority Order)

#### IMMEDIATE: Core Partials & Pages

**1. Create `resources/views/public/partials/header.blade.php`**
```blade
<nav class="glass-nav" x-data="{ mobileOpen: false }">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between h-16">
      {{-- Logo --}}
      <a href="{{ route('public.home') }}" class="flex items-center">
        <img src="{{ asset('assets/branding/hdTicketsLogo.webp') }}" alt="HD Tickets" class="h-10 w-10" width="40" height="40">
        <span class="ml-3 text-xl font-bold gradient-text-emerald">HD Tickets</span>
      </a>
      
      {{-- Desktop Nav --}}
      <nav class="hidden md:flex space-x-8">
        <a href="{{ route('public.home') }}" class="nav-link">Home</a>
        <a href="{{ route('public.pricing') }}" class="nav-link">Pricing</a>
        <a href="{{ route('public.coverage') }}" class="nav-link">Coverage</a>
        <a href="{{ route('public.faqs') }}" class="nav-link">FAQs</a>
        <a href="{{ route('tickets.main') }}" class="nav-link">Browse Tickets</a>
      </nav>
      
      {{-- Auth Buttons --}}
      <div class="hidden md:flex items-center space-x-4">
        @auth
          <a href="{{ route('dashboard') }}" class="btn-marketing-secondary">Dashboard</a>
        @else
          <a href="{{ route('login') }}" class="btn-marketing-secondary">Sign In</a>
          <a href="{{ route('register') }}" class="btn-marketing-primary">Get Started</a>
        @endauth
      </div>
      
      {{-- Mobile menu button --}}
      <button @click="mobileOpen = !mobileOpen" class="md:hidden">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path x-show="!mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
          <path x-show="mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>
    
    {{-- Mobile menu --}}
    <div x-show="mobileOpen" x-transition class="md:hidden pb-4">
      <a href="{{ route('public.home') }}" class="block py-2">Home</a>
      <a href="{{ route('public.pricing') }}" class="block py-2">Pricing</a>
      <a href="{{ route('public.coverage') }}" class="block py-2">Coverage</a>
      <a href="{{ route('public.faqs') }}" class="block py-2">FAQs</a>
      <a href="{{ route('tickets.main') }}" class="block py-2">Browse Tickets</a>
    </div>
  </div>
</nav>
```

**2. Create `resources/views/public/partials/footer.blade.php`**
```blade
<footer class="footer-enhanced text-white py-12">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
      {{-- Brand --}}
      <div>
        <div class="flex items-center mb-4">
          <img src="{{ asset('assets/branding/hdTicketsLogo.png') }}" alt="HD Tickets" class="h-10 w-10 brightness-0 invert">
          <span class="ml-3 text-xl font-bold">HD Tickets</span>
        </div>
        <p class="text-gray-300 text-sm">Smart sports ticket monitoring for fans who never want to miss a match.</p>
      </div>
      
      {{-- Product --}}
      <div>
        <h3 class="font-semibold mb-4">Product</h3>
        <ul class="space-y-2 text-sm">
          <li><a href="{{ route('public.pricing') }}" class="text-gray-300 hover:text-white">Pricing</a></li>
          <li><a href="{{ route('public.coverage') }}" class="text-gray-300 hover:text-white">Coverage</a></li>
          <li><a href="{{ route('tickets.main') }}" class="text-gray-300 hover:text-white">Browse Tickets</a></li>
        </ul>
      </div>
      
      {{-- Support --}}
      <div>
        <h3 class="font-semibold mb-4">Support</h3>
        <ul class="space-y-2 text-sm">
          <li><a href="{{ route('public.faqs') }}" class="text-gray-300 hover:text-white">FAQs</a></li>
          <li><a href="mailto:support@hdtickets.com" class="text-gray-300 hover:text-white">Contact</a></li>
        </ul>
      </div>
      
      {{-- Legal --}}
      <div>
        <h3 class="font-semibold mb-4">Legal</h3>
        <ul class="space-y-2 text-sm">
          <li><a href="{{ route('legal.terms-of-service') }}" class="text-gray-300 hover:text-white">Terms</a></li>
          <li><a href="{{ route('legal.privacy-policy') }}" class="text-gray-300 hover:text-white">Privacy</a></li>
          <li><a href="{{ route('legal.cookie-policy') }}" class="text-gray-300 hover:text-white">Cookies</a></li>
        </ul>
      </div>
    </div>
    
    <div class="border-t border-gray-700 mt-8 pt-8 text-center text-sm text-gray-400">
      <p>&copy; {{ date('Y') }} HD Tickets. All rights reserved.</p>
    </div>
  </div>
</footer>
```

**3. Add Routes to `routes/web.php`** (add before require auth.php)
```php
/*
|--------------------------------------------------------------------------
| Public Marketing Routes
|--------------------------------------------------------------------------
*/
Route::get('/new', function () {
    $stats = Cache::remember('homepage_stats', 900, function () {
        return [
            'total_tickets' => DB::table('scraped_tickets')->where('status', 'active')->count(),
            'platforms' => DB::table('scraped_tickets')->distinct('platform')->count('platform'),
            'cities' => DB::table('scraped_tickets')->distinct('location')->count('location'),
        ];
    });
    return view('public.home', compact('stats'));
})->name('public.home');

Route::get('/pricing', fn() => view('public.pricing'))->name('public.pricing');
Route::get('/coverage', fn() => view('public.coverage'))->name('public.coverage');
Route::get('/faqs', fn() => view('public.faqs'))->name('public.faqs');
```

**4. Create Home Page `resources/views/public/home.blade.php`** - KEY STRUCTURE:
```blade
@extends('layouts.marketing')

@section('title', 'HD Tickets - Never Miss Your Favourite Sports Events')

@section('content')
{{-- Hero Section --}}
<section class="relative py-20 overflow-hidden">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center">
      <h1 class="text-5xl md:text-6xl font-bold text-gray-900 mb-6">
        Never Miss Your<br>
        <span class="gradient-text-emerald">Favourite Sports Events</span>
      </h1>
      <p class="text-xl text-gray-600 mb-8 max-w-2xl mx-auto">
        Get instant alerts when tickets become available. Monitor prices across 40+ platforms. 
        Secure the best seats automatically.
      </p>
      
      {{-- Hero Search --}}
      <form action="{{ route('tickets.main') }}" method="GET" class="max-w-2xl mx-auto mb-12">
        <input type="text" name="q" placeholder="Search for teams, events, or venues..." class="hero-search">
      </form>
      
      {{-- Quick Stats --}}
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-4xl mx-auto">
        <div class="stat-card">
          <div class="text-3xl font-bold text-emerald-600">{{ number_format($stats['total_tickets'] ?? 0) }}</div>
          <div class="text-gray-600">Active Tickets</div>
        </div>
        <div class="stat-card">
          <div class="text-3xl font-bold text-blue-600">{{ $stats['platforms'] ?? 0 }}+</div>
          <div class="text-gray-600">Platforms Monitored</div>
        </div>
        <div class="stat-card">
          <div class="text-3xl font-bold text-purple-600">{{ $stats['cities'] ?? 0 }}+</div>
          <div class="text-gray-600">Cities Covered</div>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- Features Section --}}
<section id="features" class="py-20 bg-white">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <h2 class="text-3xl font-bold text-center mb-12">Why Choose HD Tickets?</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
      {{-- Feature cards here --}}
    </div>
  </div>
</section>

{{-- CTA Section --}}
<section class="py-20 bg-gradient-to-r from-emerald-600 to-teal-600">
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-white">
    <h2 class="text-4xl font-bold mb-4">Ready to Never Miss a Match?</h2>
    <p class="text-xl mb-8">Join thousands of fans already using HD Tickets</p>
    <a href="{{ route('register') }}" class="inline-block px-8 py-4 bg-white text-emerald-600 rounded-lg font-semibold text-lg hover:bg-gray-100 transition">
      Get Started Free
    </a>
  </div>
</section>
@endsection
```

**5. Create minimal Pricing, Coverage, and FAQs pages** following same structure

**6. Add GA4 Config** to `config/services.php`:
```php
'analytics' => [
    'ga4' => env('GA4_ID', null),
],
```

**7. Build Assets**:
```bash
npm install
npm run build
```

**8. Test Routes**:
```bash
php artisan route:list | grep public
```

### Testing Commands
```bash
# Build assets
npm run dev

# Verify routes
php artisan route:list

# Create simple Pest test
vendor/bin/pest --init
# Then create tests/Feature/PublicPagesTest.php
```

### Rollback if Needed
Simply revert routes/web.php changes - files are isolated under `resources/views/public/`

### Performance Checklist
- ✅ Inter font preloaded
- ✅ Logo preloaded as WebP
- ✅ DNS prefetch for Google Fonts
- ✅ Skip link for accessibility
- ✅ Semantic HTML with ARIA labels
- ✅ Mobile-responsive navigation
- ✅ GA4 conditional loading

### Quick Win: Preview at `/new`
The new landing is accessible at `/new` route for review before replacing root `/`
