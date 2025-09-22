<?php

return [
    /*
    |--------------------------------------------------------------------------
    | HD Tickets Navigation Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the navigation structure for the HD Tickets platform.
    | It serves as the single source of truth for all navigation items,
    | supporting role-based visibility, hierarchical structures, and 
    | accessibility features.
    |
    */

    // Default navigation behavior
    'defaults' => [
        'collapsible' => true,
        'mobile_overlay' => true,
        'show_icons' => true,
        'show_badges' => true,
        'animate_transitions' => true,
    ],

    // Navigation items grouped by role
    'menus' => [
        
        // Customer Navigation
        'customer' => [
            'label' => 'Customer Menu',
            'icon' => 'user-circle',
            'items' => [
                [
                    'id' => 'dashboard',
                    'label' => 'Dashboard',
                    'icon' => 'home',
                    'route' => 'customer.dashboard',
                    'badge' => null,
                    'children' => [],
                    'permissions' => ['customer'],
                    'active_patterns' => ['customer/dashboard*'],
                    'description' => 'Your personal dashboard with tickets and alerts'
                ],
                [
                    'id' => 'discover',
                    'label' => 'Discover Events',
                    'icon' => 'search',
                    'route' => 'customer.discover',
                    'badge' => null,
                    'children' => [],
                    'permissions' => ['customer'],
                    'active_patterns' => ['customer/discover*', 'events/search*'],
                    'description' => 'Find and explore sports events'
                ],
                [
                    'id' => 'alerts',
                    'label' => 'Price Alerts',
                    'icon' => 'bell',
                    'route' => 'customer.alerts.index',
                    'badge' => 'alerts_count',
                    'children' => [
                        [
                            'id' => 'alerts.active',
                            'label' => 'Active Alerts',
                            'route' => 'customer.alerts.active',
                            'permissions' => ['customer']
                        ],
                        [
                            'id' => 'alerts.history',
                            'label' => 'Alert History', 
                            'route' => 'customer.alerts.history',
                            'permissions' => ['customer']
                        ]
                    ],
                    'permissions' => ['customer'],
                    'active_patterns' => ['customer/alerts*'],
                    'description' => 'Manage your ticket price alerts'
                ],
                [
                    'id' => 'watchlist',
                    'label' => 'Watchlist',
                    'icon' => 'heart',
                    'route' => 'customer.watchlist',
                    'badge' => 'watchlist_count',
                    'children' => [],
                    'permissions' => ['customer'],
                    'active_patterns' => ['customer/watchlist*'],
                    'description' => 'Your saved events and favorites'
                ],
                [
                    'id' => 'subscription',
                    'label' => 'Subscription',
                    'icon' => 'credit-card',
                    'route' => 'customer.subscription',
                    'badge' => null,
                    'children' => [
                        [
                            'id' => 'subscription.manage',
                            'label' => 'Manage Plan',
                            'route' => 'customer.subscription.manage',
                            'permissions' => ['customer']
                        ],
                        [
                            'id' => 'subscription.billing',
                            'label' => 'Billing History',
                            'route' => 'customer.subscription.billing',
                            'permissions' => ['customer']
                        ]
                    ],
                    'permissions' => ['customer'],
                    'active_patterns' => ['customer/subscription*'],
                    'description' => 'Manage your subscription and billing'
                ]
            ]
        ],

        // Agent Navigation
        'agent' => [
            'label' => 'Agent Menu',
            'icon' => 'briefcase',
            'items' => [
                [
                    'id' => 'dashboard',
                    'label' => 'Dashboard',
                    'icon' => 'chart-bar',
                    'route' => 'agent.dashboard',
                    'badge' => null,
                    'children' => [],
                    'permissions' => ['agent'],
                    'active_patterns' => ['agent/dashboard*'],
                    'description' => 'Agent performance and monitoring overview'
                ],
                [
                    'id' => 'monitoring',
                    'label' => 'Ticket Monitor',
                    'icon' => 'eye',
                    'route' => 'agent.monitoring',
                    'badge' => 'monitoring_count',
                    'children' => [
                        [
                            'id' => 'monitoring.live',
                            'label' => 'Live Feed',
                            'route' => 'agent.monitoring.live',
                            'permissions' => ['agent']
                        ],
                        [
                            'id' => 'monitoring.filters',
                            'label' => 'Filter Setup',
                            'route' => 'agent.monitoring.filters',
                            'permissions' => ['agent']
                        ]
                    ],
                    'permissions' => ['agent'],
                    'active_patterns' => ['agent/monitoring*'],
                    'description' => 'Real-time ticket monitoring and filtering'
                ],
                [
                    'id' => 'queue',
                    'label' => 'Purchase Queue',
                    'icon' => 'queue-list',
                    'route' => 'agent.queue',
                    'badge' => 'queue_count',
                    'children' => [],
                    'permissions' => ['agent'],
                    'active_patterns' => ['agent/queue*'],
                    'description' => 'Ticket purchase decision queue'
                ],
                [
                    'id' => 'performance',
                    'label' => 'Performance',
                    'icon' => 'chart-line',
                    'route' => 'agent.performance',
                    'badge' => null,
                    'children' => [
                        [
                            'id' => 'performance.metrics',
                            'label' => 'Key Metrics',
                            'route' => 'agent.performance.metrics',
                            'permissions' => ['agent']
                        ],
                        [
                            'id' => 'performance.reports',
                            'label' => 'Reports',
                            'route' => 'agent.performance.reports',
                            'permissions' => ['agent']
                        ]
                    ],
                    'permissions' => ['agent'],
                    'active_patterns' => ['agent/performance*'],
                    'description' => 'Track your success rates and performance'
                ],
                [
                    'id' => 'alerts_mgmt',
                    'label' => 'Alert Management',
                    'icon' => 'cog',
                    'route' => 'agent.alerts',
                    'badge' => null,
                    'children' => [],
                    'permissions' => ['agent'],
                    'active_patterns' => ['agent/alerts*'],
                    'description' => 'Configure and manage your alerts'
                ]
            ]
        ],

        // Admin Navigation
        'admin' => [
            'label' => 'Admin Menu',
            'icon' => 'shield-check',
            'items' => [
                [
                    'id' => 'dashboard',
                    'label' => 'Admin Dashboard',
                    'icon' => 'squares-2x2',
                    'route' => 'admin.dashboard',
                    'badge' => null,
                    'children' => [],
                    'permissions' => ['admin'],
                    'active_patterns' => ['admin/dashboard*'],
                    'description' => 'System overview and key metrics'
                ],
                [
                    'id' => 'users',
                    'label' => 'User Management',
                    'icon' => 'users',
                    'route' => 'admin.users.index',
                    'badge' => null,
                    'children' => [
                        [
                            'id' => 'users.customers',
                            'label' => 'Customers',
                            'route' => 'admin.users.customers',
                            'permissions' => ['admin']
                        ],
                        [
                            'id' => 'users.agents',
                            'label' => 'Agents',
                            'route' => 'admin.users.agents', 
                            'permissions' => ['admin']
                        ],
                        [
                            'id' => 'users.admins',
                            'label' => 'Administrators',
                            'route' => 'admin.users.admins',
                            'permissions' => ['admin']
                        ]
                    ],
                    'permissions' => ['admin'],
                    'active_patterns' => ['admin/users*'],
                    'description' => 'Manage all platform users'
                ],
                [
                    'id' => 'system',
                    'label' => 'System',
                    'icon' => 'cog-6-tooth',
                    'route' => 'admin.system.index',
                    'badge' => null,
                    'children' => [
                        [
                            'id' => 'system.performance',
                            'label' => 'Performance',
                            'route' => 'admin.system.performance',
                            'permissions' => ['admin']
                        ],
                        [
                            'id' => 'system.logs',
                            'label' => 'System Logs',
                            'route' => 'admin.system.logs',
                            'permissions' => ['admin']
                        ],
                        [
                            'id' => 'system.config',
                            'label' => 'Configuration',
                            'route' => 'admin.system.config',
                            'permissions' => ['admin']
                        ]
                    ],
                    'permissions' => ['admin'],
                    'active_patterns' => ['admin/system*'],
                    'description' => 'System configuration and monitoring'
                ],
                [
                    'id' => 'analytics',
                    'label' => 'Analytics',
                    'icon' => 'chart-pie',
                    'route' => 'admin.analytics',
                    'badge' => null,
                    'children' => [
                        [
                            'id' => 'analytics.revenue',
                            'label' => 'Revenue',
                            'route' => 'admin.analytics.revenue',
                            'permissions' => ['admin']
                        ],
                        [
                            'id' => 'analytics.subscriptions',
                            'label' => 'Subscriptions',
                            'route' => 'admin.analytics.subscriptions',
                            'permissions' => ['admin']
                        ],
                        [
                            'id' => 'analytics.usage',
                            'label' => 'Platform Usage',
                            'route' => 'admin.analytics.usage',
                            'permissions' => ['admin']
                        ]
                    ],
                    'permissions' => ['admin'],
                    'active_patterns' => ['admin/analytics*'],
                    'description' => 'Revenue and subscription analytics'
                ],
                [
                    'id' => 'scraping',
                    'label' => 'Scraping Operations',
                    'icon' => 'globe-alt',
                    'route' => 'admin.scraping.index',
                    'badge' => 'scraping_status',
                    'children' => [
                        [
                            'id' => 'scraping.status',
                            'label' => 'Status Overview',
                            'route' => 'admin.scraping.status',
                            'permissions' => ['admin']
                        ],
                        [
                            'id' => 'scraping.config',
                            'label' => 'Configuration',
                            'route' => 'admin.scraping.config',
                            'permissions' => ['admin']
                        ],
                        [
                            'id' => 'scraping.logs',
                            'label' => 'Operation Logs',
                            'route' => 'admin.scraping.logs',
                            'permissions' => ['admin']
                        ]
                    ],
                    'permissions' => ['admin'],
                    'active_patterns' => ['admin/scraping*'],
                    'description' => 'Monitor and configure scraping operations'
                ]
            ]
        ]
    ],

    // User menu items (appears in header)
    'user_menu' => [
        [
            'id' => 'profile',
            'label' => 'Profile',
            'icon' => 'user',
            'route' => 'profile.show',
            'permissions' => ['customer', 'agent', 'admin'],
            'separator' => false
        ],
        [
            'id' => 'account_settings',
            'label' => 'Account Settings',
            'icon' => 'cog',
            'route' => 'account.settings',
            'permissions' => ['customer', 'agent', 'admin'],
            'separator' => false
        ],
        [
            'id' => 'admin_panel',
            'label' => 'Admin Panel',
            'icon' => 'shield-check',
            'route' => 'admin.dashboard',
            'permissions' => ['admin'],
            'separator' => true,
            'highlight' => true
        ],
        [
            'id' => 'analytics_shortcut',
            'label' => 'Analytics',
            'icon' => 'chart-bar',
            'route' => 'admin.analytics',
            'permissions' => ['admin'],
            'separator' => false
        ],
        [
            'id' => 'logout',
            'label' => 'Sign Out',
            'icon' => 'arrow-right-on-rectangle',
            'route' => 'logout',
            'method' => 'POST',
            'permissions' => ['customer', 'agent', 'admin'],
            'separator' => true,
            'confirm' => 'Are you sure you want to sign out?'
        ]
    ],

    // Quick actions that appear in header
    'quick_actions' => [
        'search' => [
            'enabled' => true,
            'placeholder' => 'Search events, venues...',
            'route' => 'search.events',
            'permissions' => ['customer', 'agent'],
            'keyboard_shortcut' => ['cmd', 'k']
        ],
        'notifications' => [
            'enabled' => true,
            'badge_key' => 'notifications_count',
            'route' => 'notifications.index',
            'permissions' => ['customer', 'agent', 'admin']
        ],
        'create_alert' => [
            'enabled' => true,
            'label' => 'New Alert',
            'icon' => 'plus',
            'route' => 'customer.alerts.create',
            'permissions' => ['customer'],
            'variant' => 'primary'
        ]
    ],

    // Navigation behavior based on breakpoints
    'responsive' => [
        'mobile' => [
            'breakpoint' => 768,
            'behavior' => 'overlay',
            'backdrop' => true,
            'swipe_to_close' => true
        ],
        'tablet' => [
            'breakpoint' => 1024,
            'behavior' => 'overlay',
            'backdrop' => true,
            'swipe_to_close' => false
        ],
        'desktop' => [
            'breakpoint' => 1024,
            'behavior' => 'sidebar',
            'collapsible' => true,
            'default_collapsed' => false
        ]
    ],

    // Badge configuration for dynamic content
    'badges' => [
        'alerts_count' => [
            'type' => 'count',
            'source' => 'user.active_alerts_count',
            'max_display' => 99,
            'variant' => 'primary'
        ],
        'watchlist_count' => [
            'type' => 'count', 
            'source' => 'user.watchlist_count',
            'max_display' => 99,
            'variant' => 'info'
        ],
        'monitoring_count' => [
            'type' => 'count',
            'source' => 'user.monitoring_items_count', 
            'max_display' => 999,
            'variant' => 'info'
        ],
        'queue_count' => [
            'type' => 'count',
            'source' => 'user.queue_items_count',
            'max_display' => 99,
            'variant' => 'warning'
        ],
        'notifications_count' => [
            'type' => 'count',
            'source' => 'user.unread_notifications_count',
            'max_display' => 99,
            'variant' => 'error'
        ],
        'scraping_status' => [
            'type' => 'status',
            'source' => 'system.scraping_status',
            'variants' => [
                'running' => 'success',
                'paused' => 'warning', 
                'error' => 'error',
                'stopped' => 'info'
            ]
        ]
    ],

    // Icon mapping for common navigation icons
    'icons' => [
        'home' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>',
        'search' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>',
        'bell' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>',
        'heart' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>',
        'credit-card' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>',
        'user' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>',
        'cog' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
        'plus' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>',
        'shield-check' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>',
        'chart-bar' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>',
        'arrow-right-on-rectangle' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>',
        'user-circle' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        'briefcase' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4a2 2 0 00-2-2H8a2 2 0 00-2 2v2m8 0V4a2 2 0 012 2v2m0 0h4a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V10a2 2 0 012-2h4m8 0V8a2 2 0 00-2-2H10a2 2 0 00-2 2v2m8 0V10a2 2 0 012 2v8a2 2 0 01-2 2H6a2 2 0 01-2-2v-8c0-1.1.9-2 2-2h8a2 2 0 012 2z"/></svg>',
        'eye' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>',
        'queue-list' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>',
        'chart-line' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941"/></svg>',
        'squares-2x2' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>',
        'users' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>',
        'cog-6-tooth' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
        'chart-pie' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l3.108-.77a.75.75 0 00.547-1.21l-1.506-2.636a.75.75 0 01.434-1.086l1.896-.632c1.131-.377 1.175-1.982.058-2.42l-8.106-3.176A.75.75 0 007.5 5.04V9a.75.75 0 01-.22.53L5.78 11.03a.75.75 0 000 1.061L7.28 13.59a.75.75 0 00.22.53V21a.75.75 0 001.5 0v-4.14l4.25-4.25z"/></svg>',
        'globe-alt' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>'
    ]
];