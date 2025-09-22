@php
  use App\Support\Navigation;
  
  $user = auth()->user();
  $navigation = new Navigation();
  $currentRole = $navigation->getCurrentUserRole();
  $primaryNavItems = $navigation->getMenuForRole($currentRole)->take(6); // Limit primary items
  $userMenuItems = $navigation->getUserMenuItems();
  
  $isActive = fn(string $route) => request()->routeIs($route) ? 'is-active' : '';
  $canSeeItem = function (array $item) use ($navigation) {
      return $navigation->canSeeItem($item);
  };
  \Illuminate\Support\Str::class; // ensure Str alias available
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full" x-data="{ sidebar: false, theme: localStorage.getItem('theme') || 'light', toggleTheme() { this.theme = this.theme === 'light' ? 'dark' : 'light';
        localStorage.setItem('theme', this.theme);
        document.documentElement.classList.toggle('dark', this.theme === 'dark'); } }" x-init="document.documentElement.classList.toggle('dark', theme === 'dark')">

  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="manifest" href="/manifest.json" />
    <title>@yield('title', 'Dashboard') • {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
  </head>

  <body class="min-h-screen flex bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100 antialiased">
    <!-- Sidebar -->
    <aside class="uiv2-sidebar" :class="{ 'uiv2-sidebar--open': sidebar }" @keydown.window.escape="sidebar=false">
      <div class="uiv2-sidebar__header">
        <a href="{{ route('dashboard') }}"
          class="flex items-center space-x-2 font-semibold text-slate-800 dark:text-slate-100">
          <img src="{{ asset('assets/images/hdTicketsLogo.png') }}" class="w-8 h-8 rounded" alt="Logo" />
          <span class="text-sm">HD Tickets</span>
        </a>
        <button class="uiv2-icon-btn md:hidden" @click="sidebar=false" aria-label="Close sidebar">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
      <nav class="uiv2-nav" aria-label="Primary">
        <div class="uiv2-nav__section">
          <p class="uiv2-nav__label">Main</p>
          @foreach ($primaryNavItems as $item)
            @if ($canSeeItem($item))
              <a href="{{ $item['url'] ?? '#' }}" class="uiv2-nav__link {{ $item['is_active'] ? 'is-active' : '' }}"
                aria-current="{{ $item['is_active'] ? 'page' : 'false' }}"
                data-testid="nav-{{ \Illuminate\Support\Str::slug($item['label']) }}">
                <span class="uiv2-nav__icon">
                  @if(isset($item['icon_svg']))
                    {!! $item['icon_svg'] !!}
                  @else
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                  @endif
                </span>
                <span>{{ $item['label'] }}</span>
              </a>
            @endif
          @endforeach
        </div>
        <div class="uiv2-nav__section mt-6">
          <p class="uiv2-nav__label">User</p>
          @foreach ($userMenuItems as $item)
            @if ($canSeeItem($item) && ($item['id'] ?? '') !== 'logout')
              <a href="{{ $item['url'] ?? '#' }}" class="uiv2-nav__link"
                 data-testid="nav-{{ \Illuminate\Support\Str::slug($item['label']) }}">
                <span class="uiv2-nav__icon">
                  @if(isset($item['icon_svg']))
                    {!! $item['icon_svg'] !!}
                  @else
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                  @endif
                </span>
                <span>{{ $item['label'] }}</span>
              </a>
            @endif
          @endforeach
          @auth
            <form method="POST" action="{{ route('logout') }}" class="mt-4">
              @csrf
              <button type="submit" class="uiv2-nav__link w-full text-left">
                <span class="uiv2-nav__icon">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                  </svg>
                </span>
                <span>Logout</span>
              </button>
            </form>
          @endauth
        </div>
      </nav>
      <div class="uiv2-sidebar__footer text-xs px-4 py-3 text-slate-500 dark:text-slate-400">
        v{{ config('ui.app.version') }} • {{ now()->year }}
      </div>
    </aside>

    <!-- Overlay -->
    <div class="uiv2-overlay" :class="{ 'uiv2-overlay--visible': sidebar }" @click="sidebar=false"></div>

    <!-- Main -->
    <div class="uiv2-content-column">
      <header class="uiv2-header">
        <div class="flex items-center space-x-2">
          <button class="uiv2-icon-btn md:hidden" @click="sidebar=true" aria-label="Open navigation">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>
          <h1 class="text-lg font-semibold leading-tight" id="page-title">@yield('title', 'Dashboard')</h1>
        </div>
        <div class="flex items-center space-x-2">
          <button class="uiv2-icon-btn" @click="toggleTheme()"
            :aria-label="theme === 'light' ? 'Activate dark theme' : 'Activate light theme'">
            <svg x-show="theme==='light'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M12 8a4 4 0 100 8 4 4 0 000-8z" />
            </svg>
            <svg x-show="theme==='dark'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" />
            </svg>
          </button>
          @auth
            <div class="relative" x-data="{ open: false }" @keydown.escape="open=false">
              <button class="uiv2-user-btn" @click="open=!open" :aria-expanded="open.toString()" aria-haspopup="true">
                <span class="uiv2-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                <span
                  class="hidden sm:inline text-sm font-medium max-w-[120px] truncate">{{ auth()->user()->name }}</span>
                <svg class="w-4 h-4 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>
              <div x-show="open" x-transition @click.outside="open=false" class="uiv2-dropdown" role="menu"
                aria-label="User menu">
                <div class="px-3 py-2 text-xs text-slate-500">Signed in as<br><span
                    class="font-medium text-slate-700 dark:text-slate-200">{{ auth()->user()->email }}</span></div>
                <a href="{{ route('profile.show') }}" class="uiv2-dropdown__item" role="menuitem">Profile</a>
                <a href="{{ route('dashboard.analytics') }}" class="uiv2-dropdown__item" role="menuitem">Analytics</a>
                @if ($user && method_exists($user, 'isAdmin') && $user->isAdmin())
                  <a href="{{ route('admin.dashboard') }}" class="uiv2-dropdown__item" role="menuitem">Admin</a>
                @endif
                <div class="uiv2-dropdown__separator"></div>
                <form method="POST" action="{{ route('logout') }}" x-data>{@csrf}<button type="submit"
                    class="uiv2-dropdown__item w-full text-left">Logout</button></form>
              </div>
            </div>
          @endauth
        </div>
      </header>
      <main id="main-content" class="uiv2-main" tabindex="-1" aria-labelledby="page-title">
        @include('layouts.partials.flash-messages')
        @yield('content')
      </main>
    </div>
    @stack('scripts')
  </body>

</html>
