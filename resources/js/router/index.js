/**
 * HD Tickets Vue Router Configuration
 * Implements route-based code splitting with dynamic imports
 */

import { createRouter, createWebHistory } from 'vue-router';

/**
 * Route-based lazy loading with chunk naming
 * This enables code splitting at the route level
 */
const routes = [
  {
    path: '/',
    name: 'dashboard',
    component: () => import(/* webpackChunkName: "dashboard" */ '@components/SportsDashboardLayout.vue'),
    meta: {
      title: 'HD Tickets - Dashboard',
      preload: true,
      requiresAuth: true
    }
  },
  {
    path: '/tickets',
    name: 'tickets',
    component: () => import(/* webpackChunkName: "tickets" */ '@components/TicketDashboard.vue'),
    meta: {
      title: 'HD Tickets - Event Tickets',
      requiresAuth: true
    }
  },
  {
    path: '/events',
    name: 'events',
    component: () => import(/* webpackChunkName: "events" */ '@components/EventList.vue'),
    meta: {
      title: 'HD Tickets - Sports Events',
      requiresAuth: true
    }
  },
  {
    path: '/analytics',
    name: 'analytics',
    component: () => import(/* webpackChunkName: "analytics" */ '@components/AnalyticsDashboard.vue'),
    meta: {
      title: 'HD Tickets - Analytics',
      requiresAuth: true,
      permissions: ['view_analytics']
    }
  },
  {
    path: '/monitoring',
    name: 'monitoring',
    component: () => import(/* webpackChunkName: "monitoring" */ '@components/RealTimeMonitoringDashboard.vue'),
    meta: {
      title: 'HD Tickets - Real-time Monitoring',
      requiresAuth: true,
      permissions: ['view_monitoring']
    }
  },
  {
    path: '/admin',
    name: 'admin',
    component: () => import(/* webpackChunkName: "admin" */ '@components/admin/AdminDashboard.vue'),
    meta: {
      title: 'HD Tickets - Admin Panel',
      requiresAuth: true,
      permissions: ['admin_access']
    },
    children: [
      {
        path: 'users',
        name: 'admin-users',
        component: () => import(/* webpackChunkName: "admin-users" */ '@components/admin/UserManagement.vue'),
        meta: {
          title: 'User Management',
          permissions: ['manage_users']
        }
      },
      {
        path: 'settings',
        name: 'admin-settings',
        component: () => import(/* webpackChunkName: "admin-settings" */ '@components/admin/SystemSettings.vue'),
        meta: {
          title: 'System Settings',
          permissions: ['system_settings']
        }
      }
    ]
  },
  {
    path: '/preferences',
    name: 'preferences',
    component: () => import(/* webpackChunkName: "preferences" */ '@components/UserPreferencesPanel.vue'),
    meta: {
      title: 'HD Tickets - User Preferences',
      requiresAuth: true
    }
  },
  {
    path: '/search',
    name: 'search',
    component: () => import(/* webpackChunkName: "search" */ '@components/TicketSearch.vue'),
    meta: {
      title: 'HD Tickets - Search',
      requiresAuth: true
    }
  },
  {
    path: '/login',
    name: 'login',
    component: () => import(/* webpackChunkName: "auth" */ '@components/auth/LoginForm.vue'),
    meta: {
      title: 'HD Tickets - Login',
      requiresGuest: true
    }
  },
  {
    path: '/profile',
    name: 'profile',
    component: () => import(/* webpackChunkName: "profile" */ '@components/user/UserProfile.vue'),
    meta: {
      title: 'HD Tickets - Profile',
      requiresAuth: true
    }
  },
  // Error pages
  {
    path: '/404',
    name: 'not-found',
    component: () => import(/* webpackChunkName: "errors" */ '@components/errors/NotFound.vue'),
    meta: {
      title: 'Page Not Found'
    }
  },
  {
    path: '/500',
    name: 'server-error',
    component: () => import(/* webpackChunkName: "errors" */ '@components/errors/ServerError.vue'),
    meta: {
      title: 'Server Error'
    }
  },
  {
    path: '/offline',
    name: 'offline',
    component: () => import(/* webpackChunkName: "offline" */ '@components/errors/OfflinePage.vue'),
    meta: {
      title: 'Offline Mode'
    }
  },
  // Catch-all route
  {
    path: '/:pathMatch(.*)*',
    redirect: '/404'
  }
];

// Create router instance
const router = createRouter({
  history: createWebHistory('/'),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition;
    } else {
      return { top: 0, behavior: 'smooth' };
    }
  }
});

/**
 * Navigation guards for authentication and performance
 */
router.beforeEach(async (to, from, next) => {
  // Start loading indicator
  if (window.hdTicketsUtils?.loading) {
    window.hdTicketsUtils.loading(`Loading ${to.meta.title || 'page'}...`);
  }

  // Update document title
  if (to.meta.title) {
    document.title = to.meta.title;
  }

  // Performance: Mark navigation start
  if (window.performanceMonitoring) {
    window.performanceMonitoring.mark(`navigation-start-${to.name}`);
  }

  // Authentication check
  if (to.meta.requiresAuth) {
    const isAuthenticated = await checkAuthentication();
    if (!isAuthenticated) {
      next({ name: 'login', query: { redirect: to.fullPath } });
      return;
    }

    // Permission check
    if (to.meta.permissions) {
      const hasPermissions = await checkPermissions(to.meta.permissions);
      if (!hasPermissions) {
        next({ name: 'not-found' });
        return;
      }
    }
  }

  // Guest-only routes
  if (to.meta.requiresGuest) {
    const isAuthenticated = await checkAuthentication();
    if (isAuthenticated) {
      next({ name: 'dashboard' });
      return;
    }
  }

  next();
});

router.afterEach((to, from) => {
  // Stop loading indicator
  if (window.hdTicketsUtils?.stopLoading) {
    window.hdTicketsUtils.stopLoading();
  }

  // Performance: Mark navigation end
  if (window.performanceMonitoring) {
    window.performanceMonitoring.mark(`navigation-end-${to.name}`);
    if (from.name) {
      window.performanceMonitoring.measure(
        `navigation-${from.name}-to-${to.name}`,
        `navigation-start-${to.name}`,
        `navigation-end-${to.name}`
      );
    }
  }

  // Analytics tracking
  if (window.gtag) {
    window.gtag('config', 'GA_MEASUREMENT_ID', {
      page_path: to.path,
      page_title: to.meta.title
    });
  }

  // Update page visibility state
  if (window.Alpine && window.Alpine.store) {
    const appStore = window.Alpine.store('app');
    if (appStore) {
      appStore.currentRoute = to.name;
    }
  }
});

/**
 * Route preloading for performance
 */
router.preloadRoute = (routeName) => {
  const route = routes.find(r => r.name === routeName);
  if (route && route.component) {
    // Preload the route component
    route.component().catch(err => {
      console.warn(`Failed to preload route ${routeName}:`, err);
    });
  }
};

/**
 * Preload critical routes on idle
 */
if ('requestIdleCallback' in window) {
  window.requestIdleCallback(() => {
    const criticalRoutes = routes
      .filter(route => route.meta?.preload)
      .map(route => route.name);
    
    criticalRoutes.forEach(routeName => {
      router.preloadRoute(routeName);
    });
  });
}

/**
 * Authentication helper
 */
async function checkAuthentication() {
  try {
    // Check if user data exists in global context
    if (window.Laravel && window.Laravel.user) {
      return true;
    }

    // Make API call to verify authentication
    const response = await fetch('/api/auth/check', {
      method: 'GET',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      }
    });

    return response.ok;
  } catch (error) {
    console.error('Authentication check failed:', error);
    return false;
  }
}

/**
 * Permissions helper
 */
async function checkPermissions(requiredPermissions) {
  try {
    if (!Array.isArray(requiredPermissions)) {
      requiredPermissions = [requiredPermissions];
    }

    // Check user permissions from global context
    if (window.Laravel && window.Laravel.user && window.Laravel.user.permissions) {
      const userPermissions = window.Laravel.user.permissions;
      return requiredPermissions.every(permission => 
        userPermissions.includes(permission)
      );
    }

    // Make API call to check permissions
    const response = await fetch('/api/auth/permissions', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      },
      body: JSON.stringify({ permissions: requiredPermissions })
    });

    if (response.ok) {
      const data = await response.json();
      return data.hasPermissions;
    }

    return false;
  } catch (error) {
    console.error('Permission check failed:', error);
    return false;
  }
}

export default router;
