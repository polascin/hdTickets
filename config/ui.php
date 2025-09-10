<?php declare(strict_types=1);

return [
    'app' => [
        'name'    => config('app.name', 'HD Tickets'),
        'version' => trim((string) file_get_contents(base_path('VERSION'))),
    ],
    // Unified navigation definition; each item: label, route, icon (heroicon outline path), roles (array|*), badge (optional callable or string)
    'navigation' => [
        'primary' => [
            [
                'label' => 'Dashboard',
                'route' => 'dashboard',
                'icon'  => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
                'roles' => '*',
            ],
            [
                'label' => 'Tickets',
                'route' => 'tickets.scraping.index',
                'icon'  => 'M9 17v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6m6 0a2 2 0 01-2 2H5a2 2 0 01-2-2m6 0h6m0 0v-6a2 2 0 012-2h2a2 2 0 012 2v6m-6 0a2 2 0 002 2h2a2 2 0 002-2',
                'roles' => ['agent', 'admin'],
            ],
            [
                'label' => 'Monitoring',
                'route' => 'monitoring.index',
                'icon'  => 'M3 3v18h18M7 14l3-3 4 4 5-9',
                'roles' => ['agent', 'admin'],
            ],
            [
                'label' => 'Purchase Queue',
                'route' => 'purchase-decisions.index',
                'icon'  => 'M3 5h18M8 5v14m8-14v14M5 9h14M5 13h14M5 17h14',
                'roles' => ['agent', 'admin'],
            ],
            [
                'label' => 'Sources',
                'route' => 'ticket-sources.index',
                'icon'  => 'M4 6h16M4 10h16M4 14h10M4 18h6',
                'roles' => ['agent', 'admin'],
            ],
            [
                'label' => 'Analytics',
                'route' => 'dashboard.analytics',
                'icon'  => 'M4 19h16M4 15h10M4 11h6M4 7h2',
                'roles' => '*',
            ],
        ],
        'secondary' => [
            [
                'label' => 'Profile',
                'route' => 'profile.show',
                'icon'  => 'M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 11a3 3 0 10-6 0 3 3 0 006 0z',
                'roles' => '*',
            ],
            [
                'label' => 'Admin',
                'route' => 'admin.dashboard',
                'icon'  => 'M12 8c-1.657 0-3 .895-3 2v6h6v-6c0-1.105-1.343-2-3-2zm0 0V5m0 0a3 3 0 013 3m-3-3a3 3 0 00-3 3',
                'roles' => ['admin'],
            ],
        ],
    ],
];
