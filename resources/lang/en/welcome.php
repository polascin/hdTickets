<?php declare(strict_types=1);

return [
    'app_name' => 'HD Tickets',

    'hero' => [
        'title'            => 'HD Tickets',
        'subtitle'         => 'Never Miss Your Team Again',
        'description'      => 'Professional sports ticket monitoring platform with comprehensive user management, subscription-based access, and automated purchasing. Track prices across 50+ platforms with role-based permissions and legal compliance.',
        'cta_start_trial'  => 'Start 7-Day Free Trial',
        'cta_sign_in'      => 'Sign In',
        'greeting'         => 'Welcome back, :name! 🎉',
        'role'             => 'Your role: :role',
        'go_to_dashboard'  => 'Go to Dashboard',
        'trial_disclaimer' => 'No credit card required • 7 days free • Cancel anytime',
    ],

    'stats' => [
        'platforms'  => 'Integrated Platforms',
        'monitoring' => 'Real-Time Monitoring',
        'users'      => 'Active Users',
    ],

    'features' => [
        'role_based_access' => [
            'title'       => 'Role-Based Access',
            'description' => 'Tailored permissions for Customer, Agent, Admin, and Scraper roles ensuring secure, role-appropriate access.',
        ],
        'subscription' => [
            'title'       => 'Flexible Subscription System',
            'description' => 'Monthly subscription plans with configurable ticket limits, 7-day free trial period, and unlimited agent access.',
        ],
        'legal' => [
            'title'       => 'Legal Compliance',
            'description' => 'Fully GDPR compliant with mandatory legal document acceptance and detailed audit trails.',
        ],
        'security' => [
            'title'       => 'Advanced Security',
            'description' => 'Multi-factor authentication, device fingerprinting, email and SMS verification, and secure payment integrations.',
        ],
    ],

    'roles' => [
        'customer' => [
            'name'     => 'Customer',
            'badge'    => 'Most Popular',
            'price'    => '$29.99',
            'period'   => '/month',
            'features' => [
                '7-day free trial',
                '100 tickets/month',
                'Email verification',
                'Optional 2FA',
                'Legal document compliance',
                'Purchase access',
                'Basic monitoring',
            ],
            'cta' => 'Start Free Trial',
        ],
        'agent' => [
            'name'     => 'Agent',
            'badge'    => 'Professional',
            'price'    => 'Unlimited',
            'period'   => 'Access',
            'features' => [
                'Unlimited tickets',
                'No subscription required',
                'Advanced monitoring',
                'Performance metrics',
                'Priority support',
                'Automation features',
                'Professional tools',
            ],
            'cta' => 'Contact Sales',
        ],
        'admin' => [
            'name'     => 'Administrator',
            'badge'    => 'Enterprise',
            'price'    => 'Full',
            'period'   => 'Control',
            'features' => [
                'Complete system access',
                'User management',
                'Financial reports',
                'Analytics dashboard',
                'API management',
                'System configuration',
                'White-label options',
            ],
            'cta' => 'Enterprise Demo',
        ],
        'scraper' => [
            'note' => 'Scraper role is system-only for automated operations and cannot login to the web interface.',
        ],
    ],

    'subscription' => [
        'title'      => 'Subscription Plans',
        'subtitle'   => 'Flexible pricing designed to grow with your ticket monitoring needs',
        'free_trial' => [
            'name'     => 'Free Trial',
            'badge'    => '7 Days Free',
            'price'    => '$0',
            'period'   => 'for 7 days',
            'features' => [
                'Full platform access',
                '100 tickets included',
                'Email verification',
                'Basic support',
                'No credit card required',
                'Cancel anytime',
            ],
            'note' => 'Perfect for testing the platform',
            'cta'  => 'Start Free Trial',
        ],
        'monthly' => [
            'name'     => 'Monthly Plan',
            'badge'    => 'Most Popular',
            'price'    => '$29.99',
            'period'   => '/month',
            'features' => [
                '100 tickets per month',
                'Real-time monitoring',
                'Price alerts',
                'Email & SMS notifications',
                'Purchase automation',
                'Priority support',
                'Legal compliance',
                '2FA security',
            ],
            'note' => 'Best for regular ticket buyers',
            'cta'  => 'Subscribe Monthly',
        ],
        'professional' => [
            'name'     => 'Professional',
            'badge'    => 'Unlimited',
            'price'    => 'Unlimited',
            'period'   => 'tickets',
            'features' => [
                'Unlimited ticket access',
                'No monthly subscription',
                'Advanced monitoring tools',
                'Performance analytics',
                'API access',
                'Bulk operations',
                'White-label options',
                'Dedicated support',
            ],
            'note' => 'For ticket professionals & agents',
            'cta'  => 'Contact Sales',
        ],
    ],

    'legal' => [
        'title'      => 'Legal Compliance & Trust',
        'subtitle'   => 'Full transparency and compliance with international regulations and industry standards',
        'disclaimer' => 'No Money-Back Guarantee Policy: All sales are final. Service provided "as-is" with no warranties. Please review our Terms of Service and Service Disclaimer before subscribing.',
    ],

    'footer' => [
        'all_rights'       => 'All rights reserved.',
        'platform_tagline' => 'Professional Sports Event Ticket Monitoring Platform',
    ],
];
