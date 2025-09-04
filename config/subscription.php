<?php declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Subscription Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the HD Tickets subscription system
    |
    */

    // Free access period for new customers (in days)
    'free_access_days' => (int) env('SUBSCRIPTION_FREE_ACCESS_DAYS', 7),

    // Default monthly subscription fee
    'default_monthly_fee' => (float) env('SUBSCRIPTION_DEFAULT_MONTHLY_FEE', 29.99),

    // Default ticket limit per month for customers
    'default_ticket_limit' => (int) env('SUBSCRIPTION_DEFAULT_TICKET_LIMIT', 100),

    // Whether agents have unlimited ticket access
    'agent_unlimited_tickets' => env('SUBSCRIPTION_AGENT_UNLIMITED_TICKETS', TRUE),

    // Service terms
    'terms' => [
        'provided_as_is'          => env('SERVICE_PROVIDED_AS_IS', TRUE),
        'no_warranty'             => env('SERVICE_NO_WARRANTY', TRUE),
        'no_money_back_guarantee' => env('SERVICE_NO_MONEY_BACK_GUARANTEE', TRUE),
    ],

    // Grace period settings
    'grace_period_days' => 3, // Days after subscription expires before blocking access

    // Trial settings
    'trial_period_days'           => 14,
    'trial_automatically_enabled' => FALSE,
];
