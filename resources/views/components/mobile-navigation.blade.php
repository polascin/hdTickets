{{--
    Enhanced Mobile Navigation Component
    
    Features:
    - Touch-friendly hamburger menu
    - Smooth animations and transitions
    - Accessible keyboard navigation
    - Role-based menu items
    - Gesture support
    - Progressive enhancement
--}}
@props([
    'user' => null,
    'currentRoute' => null,
    'showSearch' => true,
    'showNotifications' => true,
])

@php
  $user = $user ?? Auth::user();
  $currentRoute = $currentRoute ?? Request::route()->getName();
@endphp

<nav x-data="mobileNavigation()" class="hd-mobile-nav" role="navigation" aria-label="Main navigation"
  :class="{ 'nav-open': isOpen }">

  <!-- Mobile Header Bar -->
  <div class="mobile-nav-header">
    <div class="mobile-nav-content">
      <!-- Logo and Branding -->
      <div class="mobile-nav-brand">
        <a href="{{ route('dashboard') }}" class="mobile-brand-link" aria-label="HD Tickets - Go to dashboard">
          <x-application-logo size="small" class="transition-transform duration-200 hover:scale-105"
            aria-hidden="true" />
          <span class="mobile-brand-text">HD Tickets</span>
        </a>
      </div>

      <!-- Mobile Action Bar -->
      <div class="mobile-nav-actions">
        @if ($showSearch)
          <button @click="toggleSearch()" class="mobile-action-btn mobile-search-btn" aria-label="Toggle search"
            :aria-expanded="showSearch">
            <svg class="mobile-action-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
          </button>
        @endif

        @if ($showNotifications && ($user?->isAdmin() || $user?->isAgent()))
          <button @click="toggleNotifications()" class="mobile-action-btn mobile-notification-btn"
            aria-label="Toggle notifications" :aria-expanded="showNotifications">
            <svg class="mobile-action-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 17h5l-5 5v-5zM12 17H7a3 3 0 01-3-3V5a3 3 0 013-3h5"></path>
            </svg>
            <!-- Notification Badge -->
            <span class="mobile-notification-badge" x-show="notificationCount > 0" x-text="notificationCount"
              aria-label="Unread notifications">
            </span>
          </button>
        @endif

        <!-- Hamburger Menu Button -->
        <button @click="toggle()" class="mobile-hamburger" aria-label="Toggle navigation menu"
          :aria-expanded="isOpen.toString()" :class="{ 'hamburger-active': isOpen }">
          <span class="hamburger-box">
            <span class="hamburger-inner"></span>
          </span>
        </button>
      </div>
    </div>

    <!-- Mobile Search Bar -->
    <div class="mobile-search" x-show="searchOpen" x-collapse x-transition:enter="transition ease-out duration-200"
      x-transition:enter-start="opacity-0 -translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
      <div class="mobile-search-form">
        <label for="mobile-search-input" class="sr-only">Search tickets and events</label>
        <input type="search" id="mobile-search-input" class="mobile-search-input"
          placeholder="Search tickets, events, venues..." x-model="searchQuery" @input="performSearch()">
        <button @click="clearSearch()" class="mobile-search-clear" x-show="searchQuery.length > 0"
          aria-label="Clear search">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>
    </div>
  </div>

  <!-- Mobile Menu Overlay -->
  <div class="mobile-menu-overlay" x-show="isOpen" @click="close()"
    x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
  </div>

  <!-- Mobile Menu Panel -->
  <div class="mobile-menu-panel" x-show="isOpen" x-transition:enter="transition ease-out duration-300 transform"
    x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
    x-transition:leave="transition ease-in duration-200 transform" x-transition:leave-start="translate-x-0"
    x-transition:leave-end="translate-x-full" role="dialog" aria-label="Navigation menu">

    <!-- Menu Header -->
    <div class="mobile-menu-header">
      @if ($user)
        <div class="mobile-user-info">
          @php $profileDisplay = $user->getProfileDisplay(); @endphp
          <div class="mobile-user-avatar">
            @if ($profileDisplay['has_picture'])
              <img src="{{ $profileDisplay['picture_url'] }}" alt="{{ $profileDisplay['display_name'] }}"
                class="user-avatar-img">
            @else
              <div class="user-avatar-placeholder">
                <span>{{ $profileDisplay['initials'] }}</span>
              </div>
            @endif
          </div>
          <div class="mobile-user-details">
            <h3 class="user-name">{{ $profileDisplay['display_name'] }}</h3>
            <p class="user-email">{{ $user->email }}</p>
            <span class="user-role">{{ ucfirst($user->role) }}</span>
          </div>
        </div>
      @endif

      <button @click="close()" class="mobile-menu-close" aria-label="Close navigation menu">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>

    <!-- Menu Navigation -->
    <nav class="mobile-menu-nav" role="navigation">
      <ul class="mobile-nav-list" role="list">
        <!-- Dashboard -->
        <li class="mobile-nav-item">
          <a href="{{ route('dashboard') }}"
            class="mobile-nav-link {{ $currentRoute === 'dashboard' ? 'active' : '' }}" @click="close()">
            <svg class="mobile-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2v0"></path>
            </svg>
            <span class="mobile-nav-text">Dashboard</span>
          </a>
        </li>

        @if ($user && ($user->isAdmin() || $user->isAgent()))
          <!-- Sports Tickets -->
          <li class="mobile-nav-item">
            <a href="{{ route('tickets.scraping.index') }}"
              class="mobile-nav-link {{ Str::startsWith($currentRoute, 'tickets.scraping') ? 'active' : '' }}"
              @click="close()">
              <svg class="mobile-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1V7a2 2 0 00-2-2H5zM5 14a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1v-3a2 2 0 00-2-2H5z">
                </path>
              </svg>
              <span class="mobile-nav-text">Sports Tickets</span>
            </a>
          </li>

          <!-- Ticket Alerts -->
          <li class="mobile-nav-item">
            <a href="{{ route('tickets.alerts.index') }}"
              class="mobile-nav-link {{ Str::startsWith($currentRoute, 'tickets.alerts') ? 'active' : '' }}"
              @click="close()">
              <svg class="mobile-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 17h5l-5 5v-5zM12 17H7a3 3 0 01-3-3V5a3 3 0 013-3h5"></path>
              </svg>
              <span class="mobile-nav-text">My Alerts</span>
            </a>
          </li>

          <!-- Purchase Queue -->
          <li class="mobile-nav-item">
            <a href="{{ route('purchase-decisions.index') }}"
              class="mobile-nav-link {{ Str::startsWith($currentRoute, 'purchase-decisions') ? 'active' : '' }}"
              @click="close()">
              <svg class="mobile-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17M17 13a2 2 0 100 4 2 2 0 000-4zm-8 4a2 2 0 11-4 0 2 2 0 014 0z">
                </path>
              </svg>
              <span class="mobile-nav-text">Purchase Queue</span>
            </a>
          </li>

          <!-- Ticket Sources -->
          <li class="mobile-nav-item">
            <a href="{{ route('ticket-sources.index') }}"
              class="mobile-nav-link {{ Str::startsWith($currentRoute, 'ticket-sources') ? 'active' : '' }}"
              @click="close()">
              <svg class="mobile-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1">
                </path>
              </svg>
              <span class="mobile-nav-text">Sources</span>
            </a>
          </li>
        @endif

        <!-- Divider -->
        <li class="mobile-nav-divider" role="separator"></li>

        <!-- Profile -->
        <li class="mobile-nav-item">
          <a href="{{ route('profile.show') }}"
            class="mobile-nav-link {{ Str::startsWith($currentRoute, 'profile') ? 'active' : '' }}" @click="close()">
            <svg class="mobile-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
            <span class="mobile-nav-text">Profile</span>
            @if ($user && $user->getProfileCompletion()['percentage'] < 90)
              <span class="mobile-nav-badge">!</span>
            @endif
          </a>
        </li>

        <!-- Settings -->
        <li class="mobile-nav-item">
          <a href="{{ route('profile.show') }}#settings" class="mobile-nav-link" @click="close()">
            <svg class="mobile-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
              </path>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            <span class="mobile-nav-text">Settings</span>
          </a>
        </li>

        <!-- Sign Out -->
        <li class="mobile-nav-item mobile-nav-logout">
          <form method="POST" action="{{ route('logout') }}" class="w-full">
            @csrf
            <button type="submit" class="mobile-nav-link mobile-nav-logout-btn">
              <svg class="mobile-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
              </svg>
              <span class="mobile-nav-text">Sign Out</span>
            </button>
          </form>
        </li>
      </ul>
    </nav>
  </div>
</nav>

@push('scripts')
  <script>
    function mobileNavigation() {
      return {
        isOpen: false,
        searchOpen: false,
        searchQuery: '',
        notificationCount: 0,

        init() {
          // Close menu on escape key
          document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
              this.close();
            }
          });

          // Handle swipe gestures
          this.initGestures();

          // Load notification count
          this.loadNotificationCount();
        },

        toggle() {
          this.isOpen = !this.isOpen;
          this.updateBodyLock();
        },

        open() {
          this.isOpen = true;
          this.updateBodyLock();
        },

        close() {
          this.isOpen = false;
          this.searchOpen = false;
          this.updateBodyLock();
        },

        toggleSearch() {
          this.searchOpen = !this.searchOpen;
          if (this.searchOpen) {
            this.$nextTick(() => {
              document.getElementById('mobile-search-input')?.focus();
            });
          }
        },

        toggleNotifications() {
          // Implement notification panel toggle
          console.log('Toggle notifications');
        },

        performSearch() {
          // Implement search functionality
          if (this.searchQuery.length > 2) {
            console.log('Searching for:', this.searchQuery);
          }
        },

        clearSearch() {
          this.searchQuery = '';
        },

        updateBodyLock() {
          if (this.isOpen) {
            document.body.classList.add('mobile-nav-open');
          } else {
            document.body.classList.remove('mobile-nav-open');
          }
        },

        initGestures() {
          // Implement swipe-to-close gesture
          let startX = null;

          document.addEventListener('touchstart', (e) => {
            if (this.isOpen) {
              startX = e.touches[0].clientX;
            }
          });

          document.addEventListener('touchmove', (e) => {
            if (this.isOpen && startX !== null) {
              const currentX = e.touches[0].clientX;
              const diffX = currentX - startX;

              // Close menu if swiped right more than 100px
              if (diffX > 100) {
                this.close();
                startX = null;
              }
            }
          });

          document.addEventListener('touchend', () => {
            startX = null;
          });
        },

        loadNotificationCount() {
          // Load notification count from API
          fetch('/api/notifications/count')
            .then(response => response.json())
            .then(data => {
              this.notificationCount = data.count || 0;
            })
            .catch(() => {
              this.notificationCount = 0;
            });
        }
      }
    }
  </script>
@endpush
