<?php declare(strict_types = 1);

// odsl-/var/www/hdtickets/tests
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v1',
   'data' => 
  array (
    '/var/www/hdtickets/tests/Factories/TestDataFactory.php' => 
    array (
      0 => '6d0ccc28df44102e69e735c4361b0b80513cc74a',
      1 => 
      array (
        0 => 'tests\\factories\\testdatafactory',
      ),
      2 => 
      array (
        0 => 'tests\\factories\\createuser',
        1 => 'tests\\factories\\createadminuser',
        2 => 'tests\\factories\\createpremiumuser',
        3 => 'tests\\factories\\createticket',
        4 => 'tests\\factories\\createticketsource',
        5 => 'tests\\factories\\createcategory',
        6 => 'tests\\factories\\createpurchaseattempt',
        7 => 'tests\\factories\\createticketalert',
        8 => 'tests\\factories\\createscrapedticket',
        9 => 'tests\\factories\\createmultiple',
        10 => 'tests\\factories\\createticketscenario',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/tests/CreatesApplication.php' => 
    array (
      0 => '9ce723e789ae6d8b538c2d37f6efb4e72aa13a27',
      1 => 
      array (
        0 => 'tests\\createsapplication',
      ),
      2 => 
      array (
        0 => 'tests\\createapplication',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/tests/Feature/RoleBasedAccessControlTest.php' => 
    array (
      0 => '40281ebddefcb5aeee3d2b88eb7006144b6b870c',
      1 => 
      array (
        0 => 'tests\\feature\\rolebasedaccesscontroltest',
      ),
      2 => 
      array (
        0 => 'tests\\feature\\test_admin_user_can_access_admin_dashboard',
        1 => 'tests\\feature\\test_admin_user_can_access_admin_dashboard_directly',
        2 => 'tests\\feature\\test_agent_user_redirected_to_agent_dashboard',
        3 => 'tests\\feature\\test_agent_user_can_access_agent_dashboard_directly',
        4 => 'tests\\feature\\test_customer_user_redirected_to_customer_dashboard',
        5 => 'tests\\feature\\test_customer_user_can_access_customer_dashboard_directly',
        6 => 'tests\\feature\\test_scraper_user_redirected_to_scraper_dashboard',
        7 => 'tests\\feature\\test_scraper_user_can_access_scraper_dashboard_directly',
        8 => 'tests\\feature\\test_ticketmaster_admin_has_proper_access',
        9 => 'tests\\feature\\test_undefined_role_falls_back_to_customer_dashboard',
        10 => 'tests\\feature\\test_customer_cannot_access_admin_dashboard',
        11 => 'tests\\feature\\test_customer_cannot_access_agent_dashboard',
        12 => 'tests\\feature\\test_customer_cannot_access_scraper_dashboard',
        13 => 'tests\\feature\\test_agent_cannot_access_admin_dashboard',
        14 => 'tests\\feature\\test_agent_cannot_access_scraper_dashboard',
        15 => 'tests\\feature\\test_scraper_cannot_access_admin_dashboard',
        16 => 'tests\\feature\\test_scraper_cannot_access_agent_dashboard',
        17 => 'tests\\feature\\test_unauthenticated_user_redirected_to_login',
        18 => 'tests\\feature\\test_inactive_user_is_logged_out',
        19 => 'tests\\feature\\test_admin_can_access_all_dashboards',
        20 => 'tests\\feature\\test_role_middleware_fallback_behavior',
        21 => 'tests\\feature\\test_user_role_methods_work_correctly',
        22 => 'tests\\feature\\test_dashboard_access_logs_user_activity',
        23 => 'tests\\feature\\test_user_with_empty_string_role_handled_properly',
        24 => 'tests\\feature\\setup',
        25 => 'tests\\feature\\createtestusers',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/tests/TestRunner.php' => 
    array (
      0 => '79180e26711c11295cb455d143170fb9e08a39d8',
      1 => 
      array (
        0 => 'tests\\testrunner',
      ),
      2 => 
      array (
        0 => 'tests\\__construct',
        1 => 'tests\\runalltests',
        2 => 'tests\\runtestsuite',
        3 => 'tests\\rununittests',
        4 => 'tests\\runperformancetests',
        5 => 'tests\\generatejmeterscripts',
        6 => 'tests\\generatecoveragereport',
        7 => 'tests\\parsecoverageresults',
        8 => 'tests\\checkcoveragethresholds',
        9 => 'tests\\collectperformancemetrics',
        10 => 'tests\\generatesummary',
        11 => 'tests\\formatsummaryreport',
        12 => 'tests\\setuptestenvironment',
        13 => 'tests\\cleanuptestenvironment',
        14 => 'tests\\optimizeforperformance',
        15 => 'tests\\ensuredirectoriesexist',
        16 => 'tests\\getdatabasequerycount',
        17 => 'tests\\getcachehitrate',
        18 => 'tests\\getqueuejobcount',
        19 => 'tests\\getjmetertemplate',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/tests/Performance/SystemPerformanceTest.php' => 
    array (
      0 => '8ac6045a5b45c6a6c87f4f833ed576f41bac13f8',
      1 => 
      array (
        0 => 'tests\\performance\\systemperformancetest',
      ),
      2 => 
      array (
        0 => 'tests\\performance\\it_can_handle_high_volume_ticket_listing_requests',
        1 => 'tests\\performance\\it_can_handle_complex_ticket_filtering_efficiently',
        2 => 'tests\\performance\\it_can_handle_concurrent_user_registrations',
        3 => 'tests\\performance\\it_can_handle_bulk_purchase_attempts_efficiently',
        4 => 'tests\\performance\\database_queries_are_optimized_for_ticket_listing',
        5 => 'tests\\performance\\scraping_service_performs_efficiently_under_load',
        6 => 'tests\\performance\\notification_service_handles_bulk_notifications_efficiently',
        7 => 'tests\\performance\\cache_improves_api_response_times',
        8 => 'tests\\performance\\purchase_engine_processes_queue_efficiently',
        9 => 'tests\\performance\\memory_usage_remains_stable_during_long_operations',
        10 => 'tests\\performance\\api_rate_limiting_performs_efficiently',
        11 => 'tests\\performance\\database_connection_pool_handles_concurrent_requests',
        12 => 'tests\\performance\\search_functionality_performs_well_with_large_dataset',
        13 => 'tests\\performance\\pagination_performs_efficiently_across_large_datasets',
        14 => 'tests\\performance\\createlargeticketdataset',
        15 => 'tests\\performance\\createmultipleticketsources',
        16 => 'tests\\performance\\createmultipleusers',
        17 => 'tests\\performance\\createmultipletickets',
        18 => 'tests\\performance\\createpurchaseattempt',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/tests/TestCase.php' => 
    array (
      0 => '3c2ac313835b435e0e1bdb18db56c6229d9e28e1',
      1 => 
      array (
        0 => 'tests\\testcase',
      ),
      2 => 
      array (
        0 => 'tests\\setup',
        1 => 'tests\\teardown',
        2 => 'tests\\configuretestenvironment',
        3 => 'tests\\cleanuptestresources',
        4 => 'tests\\createtestuser',
        5 => 'tests\\createtestticket',
        6 => 'tests\\createtestticketsource',
        7 => 'tests\\mockexternalservices',
        8 => 'tests\\mockstripeservice',
        9 => 'tests\\mockpaypalservice',
        10 => 'tests\\mocktwilioservice',
        11 => 'tests\\mockslackservice',
        12 => 'tests\\mockscrapingservices',
        13 => 'tests\\assertdatabasehasticket',
        14 => 'tests\\assertuserreceivednotification',
        15 => 'tests\\assertqueuehasjob',
        16 => 'tests\\withdatabasetransaction',
        17 => 'tests\\traveltofuture',
        18 => 'tests\\getapiheaders',
        19 => 'tests\\assertapiresponse',
        20 => 'tests\\measureperformance',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/tests/Fixtures/TestReportingDashboard.php' => 
    array (
      0 => 'ebb7070d00e0bc0870df5bb06c2017bd373ccd28',
      1 => 
      array (
        0 => 'tests\\fixtures\\testreportingdashboard',
        1 => 'tests\\fixtures\\testqualitygates',
      ),
      2 => 
      array (
        0 => 'tests\\fixtures\\__construct',
        1 => 'tests\\fixtures\\generatedashboard',
        2 => 'tests\\fixtures\\generatetestsummary',
        3 => 'tests\\fixtures\\generatecoveragechart',
        4 => 'tests\\fixtures\\generateperformancemetrics',
        5 => 'tests\\fixtures\\generatedetailedresults',
        6 => 'tests\\fixtures\\formatbytes',
        7 => 'tests\\fixtures\\gethtmltemplate',
        8 => 'tests\\fixtures\\checkallgates',
        9 => 'tests\\fixtures\\checkgate',
        10 => 'tests\\fixtures\\getmetricvalue',
        11 => 'tests\\fixtures\\calculatetestsuccessrate',
        12 => 'tests\\fixtures\\calculateerrorrate',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/tests/Integration/Api/TicketApiTest.php' => 
    array (
      0 => '6227d451423671593302b3a454c8019a3b63c5c7',
      1 => 
      array (
        0 => 'tests\\integration\\api\\ticketapitest',
      ),
      2 => 
      array (
        0 => 'tests\\integration\\api\\it_can_list_tickets_without_authentication',
        1 => 'tests\\integration\\api\\it_can_filter_tickets_by_sport_type',
        2 => 'tests\\integration\\api\\it_can_filter_tickets_by_price_range',
        3 => 'tests\\integration\\api\\it_can_filter_tickets_by_availability',
        4 => 'tests\\integration\\api\\it_can_search_tickets_by_team',
        5 => 'tests\\integration\\api\\it_can_sort_tickets_by_price',
        6 => 'tests\\integration\\api\\it_can_sort_tickets_by_event_date',
        7 => 'tests\\integration\\api\\it_can_get_single_ticket_details',
        8 => 'tests\\integration\\api\\it_returns_404_for_nonexistent_ticket',
        9 => 'tests\\integration\\api\\it_requires_authentication_to_create_ticket_alerts',
        10 => 'tests\\integration\\api\\authenticated_user_can_create_ticket_alert',
        11 => 'tests\\integration\\api\\it_validates_ticket_alert_creation_data',
        12 => 'tests\\integration\\api\\authenticated_user_can_list_their_alerts',
        13 => 'tests\\integration\\api\\authenticated_user_can_update_their_alert',
        14 => 'tests\\integration\\api\\user_cannot_update_other_users_alert',
        15 => 'tests\\integration\\api\\authenticated_user_can_delete_their_alert',
        16 => 'tests\\integration\\api\\it_requires_authentication_for_purchase_attempts',
        17 => 'tests\\integration\\api\\authenticated_user_can_create_purchase_attempt',
        18 => 'tests\\integration\\api\\it_validates_purchase_attempt_data',
        19 => 'tests\\integration\\api\\it_prevents_purchase_attempts_for_sold_out_tickets',
        20 => 'tests\\integration\\api\\premium_user_gets_higher_priority_for_purchases',
        21 => 'tests\\integration\\api\\admin_can_access_ticket_management_endpoints',
        22 => 'tests\\integration\\api\\regular_user_cannot_access_admin_endpoints',
        23 => 'tests\\integration\\api\\it_can_get_ticket_statistics',
        24 => 'tests\\integration\\api\\it_can_get_trending_tickets',
        25 => 'tests\\integration\\api\\it_implements_rate_limiting_for_api_endpoints',
        26 => 'tests\\integration\\api\\it_returns_proper_pagination_metadata',
        27 => 'tests\\integration\\api\\it_handles_api_versioning',
        28 => 'tests\\integration\\api\\setup',
        29 => 'tests\\integration\\api\\createticketalert',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/tests/Unit/Models/TicketTest.php' => 
    array (
      0 => '646ea3488d63f7d9c778e31a58e138443e4a552c',
      1 => 
      array (
        0 => 'tests\\unit\\models\\tickettest',
      ),
      2 => 
      array (
        0 => 'tests\\unit\\models\\it_can_create_a_ticket_with_basic_attributes',
        1 => 'tests\\unit\\models\\it_has_proper_fillable_attributes',
        2 => 'tests\\unit\\models\\it_casts_attributes_correctly',
        3 => 'tests\\unit\\models\\it_has_relationship_with_ticket_source',
        4 => 'tests\\unit\\models\\it_has_relationship_with_purchase_attempts',
        5 => 'tests\\unit\\models\\it_has_relationship_with_price_history',
        6 => 'tests\\unit\\models\\it_has_relationship_with_scraped_tickets',
        7 => 'tests\\unit\\models\\it_can_check_if_ticket_is_available',
        8 => 'tests\\unit\\models\\it_can_check_if_ticket_is_sold_out',
        9 => 'tests\\unit\\models\\it_can_get_ticket_price_range',
        10 => 'tests\\unit\\models\\it_can_get_formatted_event_date',
        11 => 'tests\\unit\\models\\it_can_get_team_display_name',
        12 => 'tests\\unit\\models\\it_can_check_if_event_is_upcoming',
        13 => 'tests\\unit\\models\\it_can_check_if_event_is_today',
        14 => 'tests\\unit\\models\\it_can_get_days_until_event',
        15 => 'tests\\unit\\models\\it_can_get_average_price',
        16 => 'tests\\unit\\models\\it_can_update_availability_status',
        17 => 'tests\\unit\\models\\it_can_add_price_history_entry',
        18 => 'tests\\unit\\models\\it_can_get_price_trend',
        19 => 'tests\\unit\\models\\it_can_scope_available_tickets',
        20 => 'tests\\unit\\models\\it_can_scope_tickets_by_sport',
        21 => 'tests\\unit\\models\\it_can_scope_tickets_by_price_range',
        22 => 'tests\\unit\\models\\it_can_scope_upcoming_tickets',
        23 => 'tests\\unit\\models\\it_can_scope_tickets_by_city',
        24 => 'tests\\unit\\models\\it_can_search_tickets_by_team',
        25 => 'tests\\unit\\models\\it_validates_required_fields',
        26 => 'tests\\unit\\models\\it_validates_price_constraints',
        27 => 'tests\\unit\\models\\it_can_soft_delete_ticket',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/tests/Unit/Models/UserTest.php' => 
    array (
      0 => '0f80af9469051d9c9b2af5a2a177181d8835e237',
      1 => 
      array (
        0 => 'tests\\unit\\models\\usertest',
      ),
      2 => 
      array (
        0 => 'tests\\unit\\models\\it_can_create_a_user_with_basic_attributes',
        1 => 'tests\\unit\\models\\it_has_proper_fillable_attributes',
        2 => 'tests\\unit\\models\\it_hides_sensitive_attributes',
        3 => 'tests\\unit\\models\\it_casts_attributes_correctly',
        4 => 'tests\\unit\\models\\it_has_relationship_with_subscriptions',
        5 => 'tests\\unit\\models\\it_has_relationship_with_ticket_alerts',
        6 => 'tests\\unit\\models\\it_has_relationship_with_purchase_attempts',
        7 => 'tests\\unit\\models\\it_can_check_if_user_is_premium',
        8 => 'tests\\unit\\models\\it_can_check_if_user_is_admin',
        9 => 'tests\\unit\\models\\it_can_get_user_subscription_status',
        10 => 'tests\\unit\\models\\it_can_get_user_preferences_with_defaults',
        11 => 'tests\\unit\\models\\it_can_update_user_preferences',
        12 => 'tests\\unit\\models\\it_can_enable_two_factor_authentication',
        13 => 'tests\\unit\\models\\it_can_disable_two_factor_authentication',
        14 => 'tests\\unit\\models\\it_can_check_if_two_factor_is_enabled',
        15 => 'tests\\unit\\models\\it_can_get_active_ticket_alerts_count',
        16 => 'tests\\unit\\models\\it_can_get_recent_purchase_attempts',
        17 => 'tests\\unit\\models\\it_can_soft_delete_user',
        18 => 'tests\\unit\\models\\it_validates_email_format',
        19 => 'tests\\unit\\models\\it_enforces_unique_email_constraint',
        20 => 'tests\\unit\\models\\it_can_scope_users_by_role',
        21 => 'tests\\unit\\models\\it_can_scope_active_users',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/tests/Unit/Services/NotificationServiceTest.php' => 
    array (
      0 => 'fb9dc7aeb2abf1c5e84a94b13d2b9708e053788c',
      1 => 
      array (
        0 => 'tests\\unit\\services\\notificationservicetest',
      ),
      2 => 
      array (
        0 => 'tests\\unit\\services\\it_can_send_email_notification',
        1 => 'tests\\unit\\services\\it_skips_email_when_user_disabled_email_notifications',
        2 => 'tests\\unit\\services\\it_can_send_sms_notification',
        3 => 'tests\\unit\\services\\it_skips_sms_when_user_has_no_phone_number',
        4 => 'tests\\unit\\services\\it_skips_sms_when_user_disabled_sms_notifications',
        5 => 'tests\\unit\\services\\it_can_send_push_notification',
        6 => 'tests\\unit\\services\\it_can_send_slack_notification',
        7 => 'tests\\unit\\services\\it_can_send_ticket_alert_notification',
        8 => 'tests\\unit\\services\\it_respects_user_notification_frequency_limits',
        9 => 'tests\\unit\\services\\it_allows_notifications_after_frequency_period_passes',
        10 => 'tests\\unit\\services\\it_can_send_bulk_notifications',
        11 => 'tests\\unit\\services\\it_handles_notification_failures_gracefully',
        12 => 'tests\\unit\\services\\it_can_queue_delayed_notifications',
        13 => 'tests\\unit\\services\\it_can_send_admin_notifications',
        14 => 'tests\\unit\\services\\it_can_send_purchase_confirmation_notification',
        15 => 'tests\\unit\\services\\it_can_send_payment_failure_notification',
        16 => 'tests\\unit\\services\\it_can_get_notification_statistics',
        17 => 'tests\\unit\\services\\it_can_validate_notification_preferences',
        18 => 'tests\\unit\\services\\it_can_unsubscribe_user_from_notifications',
        19 => 'tests\\unit\\services\\it_can_get_notification_delivery_status',
        20 => 'tests\\unit\\services\\it_can_send_notification_with_template',
        21 => 'tests\\unit\\services\\notificationchannelprovider',
        22 => 'tests\\unit\\services\\it_validates_notification_channels',
        23 => 'tests\\unit\\services\\setup',
        24 => 'tests\\unit\\services\\teardown',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/tests/Unit/Services/ScrapingServiceTest.php' => 
    array (
      0 => 'bcf347fa66619af6f53dd0efd489f3b32ba65cac',
      1 => 
      array (
        0 => 'tests\\unit\\services\\scrapingservicetest',
      ),
      2 => 
      array (
        0 => 'tests\\unit\\services\\it_implements_scraping_interface',
        1 => 'tests\\unit\\services\\it_initializes_with_dependencies',
        2 => 'tests\\unit\\services\\it_throws_exception_when_missing_required_dependencies',
        3 => 'tests\\unit\\services\\it_returns_available_platforms',
        4 => 'tests\\unit\\services\\it_enables_and_disables_platforms',
        5 => 'tests\\unit\\services\\it_returns_scraping_statistics',
        6 => 'tests\\unit\\services\\it_schedules_recurring_scraping',
        7 => 'tests\\unit\\services\\it_updates_scheduled_scraping_criteria',
        8 => 'tests\\unit\\services\\it_cancels_scheduled_scraping',
        9 => 'tests\\unit\\services\\it_handles_errors_gracefully',
        10 => 'tests\\unit\\services\\it_maintains_health_status',
        11 => 'tests\\unit\\services\\it_cleans_up_resources',
        12 => 'tests\\unit\\services\\setup',
        13 => 'tests\\unit\\services\\teardown',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/tests/EndToEnd/UserJourneyTest.php' => 
    array (
      0 => '1d91d2f6014a42513fbf1061afdee6094b44c950',
      1 => 
      array (
        0 => 'tests\\endtoend\\userjourneytest',
      ),
      2 => 
      array (
        0 => 'tests\\endtoend\\complete_user_registration_and_onboarding_journey',
        1 => 'tests\\endtoend\\ticket_discovery_and_purchase_attempt_journey',
        2 => 'tests\\endtoend\\premium_user_upgrade_and_benefits_journey',
        3 => 'tests\\endtoend\\ticket_alert_notification_and_response_journey',
        4 => 'tests\\endtoend\\admin_ticket_management_and_monitoring_journey',
        5 => 'tests\\endtoend\\user_account_management_and_privacy_journey',
        6 => 'tests\\endtoend\\error_handling_and_recovery_journey',
        7 => 'tests\\endtoend\\mobile_app_user_journey',
      ),
      3 => 
      array (
      ),
    ),
  ),
));