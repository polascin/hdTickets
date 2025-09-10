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
      0 => 'ad5ffa5aaccad4c2ad051eddee743d55c738992f',
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
      0 => '2b0d9a6bd9d49dfc525be0f93fc8e304255dc3c1',
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
      0 => '48b1a26e92e566c56722208b3f7a9bc804719247',
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
    '/var/www/hdtickets/tests/TestCase.php' => 
    array (
      0 => '9a2a32c5a334e402673460bf373ccc9b2ebbce6f',
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
      0 => '36d562fa8d112ecd5f83483b4838ec0df0a79595',
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
      0 => 'd903244866a6763a6a22fed5bde25571ec9917b3',
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
      0 => 'fb80072e633c5b65f508dd9469554c27a8e5d03e',
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
      0 => '715ffd708c7f43f90caa4126a5f5758628669f0c',
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
      0 => '8ca96cc4812dfc10ee359cbf676ce48ee37ac1fb',
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
      0 => 'd5e8b353fd42b2373a4b91479ba0e6c12626a023',
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
      0 => 'a91eed17c834aab974f7a85a2767617e889603fa',
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
    '/var/www/hdtickets/tests/Feature/AccessibilityTest.php' => 
    array (
      0 => '208460dd72c17471f1a18d467b3edced08d13def',
      1 => 
      array (
        0 => 'tests\\feature\\accessibilitytest',
      ),
      2 => 
      array (
        0 => 'tests\\feature\\setup',
        1 => 'tests\\feature\\test_login_form_has_proper_labels',
        2 => 'tests\\feature\\test_form_elements_have_aria_attributes',
        3 => 'tests\\feature\\test_skip_navigation_links_present',
        4 => 'tests\\feature\\test_screen_reader_only_content_present',
        5 => 'tests\\feature\\test_error_messages_have_proper_aria_attributes',
        6 => 'tests\\feature\\test_form_has_proper_heading_structure',
        7 => 'tests\\feature\\test_color_contrast_elements_have_proper_classes',
        8 => 'tests\\feature\\test_form_elements_have_autocomplete_attributes',
        9 => 'tests\\feature\\test_form_has_proper_tabindex_structure',
        10 => 'tests\\feature\\test_images_have_proper_alt_text_or_aria_hidden',
        11 => 'tests\\feature\\test_focus_management_elements_present',
        12 => 'tests\\feature\\test_keyboard_navigation_support',
        13 => 'tests\\feature\\test_form_validation_accessibility',
        14 => 'tests\\feature\\test_live_region_announcements',
        15 => 'tests\\feature\\test_fieldset_and_legend_structure',
        16 => 'tests\\feature\\test_button_accessibility_attributes',
        17 => 'tests\\feature\\test_link_accessibility',
        18 => 'tests\\feature\\test_spellcheck_and_language_attributes',
        19 => 'tests\\feature\\test_error_prevention_features',
        20 => 'tests\\feature\\test_progressive_enhancement_support',
        21 => 'tests\\feature\\test_mobile_accessibility_features',
        22 => 'tests\\feature\\test_security_and_privacy_accessibility',
        23 => 'tests\\feature\\test_contextual_help_and_instructions',
        24 => 'tests\\feature\\test_error_recovery_accessibility',
        25 => 'tests\\feature\\test_semantic_html_structure',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/tests/Feature/SportsTicketSystemTest.php' => 
    array (
      0 => '7e59728dd1f123933b9d8cbdd3b9377a771c3516',
      1 => 
      array (
        0 => 'tests\\feature\\sportsticketsystemtest',
      ),
      2 => 
      array (
        0 => 'tests\\feature\\test_web_scraping_functionality',
        1 => 'tests\\feature\\test_ticket_availability_monitoring',
        2 => 'tests\\feature\\test_sms_notification_system',
        3 => 'tests\\feature\\test_pusher_notification_system',
        4 => 'tests\\feature\\test_payment_integration',
        5 => 'tests\\feature\\test_two_factor_authentication',
        6 => 'tests\\feature\\test_activity_logging',
        7 => 'tests\\feature\\test_export_functionality',
        8 => 'tests\\feature\\test_real_time_websocket_updates',
        9 => 'tests\\feature\\test_system_integration_flow',
        10 => 'tests\\feature\\test_error_handling_and_resilience',
        11 => 'tests\\feature\\setup',
        12 => 'tests\\feature\\teardown',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/tests/Feature/LoginValidationTest.php' => 
    array (
      0 => '69a523d77c77ab196d145400c1b05c5016e5e815',
      1 => 
      array (
        0 => 'tests\\feature\\loginvalidationtest',
      ),
      2 => 
      array (
        0 => 'tests\\feature\\setup',
        1 => 'tests\\feature\\test_login_displays_form',
        2 => 'tests\\feature\\test_login_with_valid_credentials_succeeds',
        3 => 'tests\\feature\\test_login_with_remember_me_sets_cookie',
        4 => 'tests\\feature\\test_login_with_invalid_email_fails',
        5 => 'tests\\feature\\test_login_with_invalid_password_fails',
        6 => 'tests\\feature\\test_login_with_inactive_account_fails',
        7 => 'tests\\feature\\test_login_with_locked_account_fails',
        8 => 'tests\\feature\\test_account_locks_after_five_failed_attempts',
        9 => 'tests\\feature\\test_failed_attempts_reset_on_successful_login',
        10 => 'tests\\feature\\test_honeypot_protection_blocks_bots',
        11 => 'tests\\feature\\test_csrf_protection_is_enforced',
        12 => 'tests\\feature\\test_rate_limiting_prevents_brute_force',
        13 => 'tests\\feature\\test_email_validation_rules',
        14 => 'tests\\feature\\test_password_validation_rules',
        15 => 'tests\\feature\\test_user_login_activity_logging',
        16 => 'tests\\feature\\test_login_with_two_factor_authentication_enabled',
        17 => 'tests\\feature\\test_scraper_users_cannot_login',
        18 => 'tests\\feature\\test_login_form_accessibility_attributes',
        19 => 'tests\\feature\\test_login_session_regeneration',
        20 => 'tests\\feature\\teardown',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/tests/DuskTestCase.php' => 
    array (
      0 => '11c95ab25f37eacbdb5b382531bb181a0516bb9a',
      1 => 
      array (
        0 => 'tests\\dusktestcase',
      ),
      2 => 
      array (
        0 => 'tests\\prepare',
        1 => 'tests\\hasheadlessdisabled',
        2 => 'tests\\runninginsail',
        3 => 'tests\\driver',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/tests/Feature/ProfileStatsTest.php' => 
    array (
      0 => '8e91a0c1d1ae0a0e74ef5eb9f38c97f90a50bc68',
      1 => 
      array (
        0 => 'tests\\feature\\profilestatstest',
      ),
      2 => 
      array (
        0 => 'tests\\feature\\it_returns_profile_stats_json',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/tests/Feature/TicketPurchaseWorkflowTest.php' => 
    array (
      0 => 'd9f4b3df9bf0ba46cd0e7e292758b1c85be99d08',
      1 => 
      array (
        0 => 'tests\\feature\\ticketpurchaseworkflowtest',
      ),
      2 => 
      array (
        0 => 'tests\\feature\\customer_with_active_subscription_can_complete_purchase_workflow',
        1 => 'tests\\feature\\customer_without_subscription_is_redirected_to_subscription_plans',
        2 => 'tests\\feature\\customer_exceeding_ticket_limit_receives_proper_error',
        3 => 'tests\\feature\\agent_can_purchase_unlimited_tickets',
        4 => 'tests\\feature\\admin_can_purchase_unlimited_tickets',
        5 => 'tests\\feature\\purchase_fails_for_unavailable_ticket',
        6 => 'tests\\feature\\purchase_fails_when_quantity_exceeds_availability',
        7 => 'tests\\feature\\purchase_history_shows_correct_information',
        8 => 'tests\\feature\\purchase_failure_shows_appropriate_error_page',
        9 => 'tests\\feature\\new_customer_within_free_access_period_can_purchase',
        10 => 'tests\\feature\\customer_beyond_free_access_period_cannot_purchase_without_subscription',
        11 => 'tests\\feature\\purchase_validation_requires_terms_acceptance',
        12 => 'tests\\feature\\purchase_validation_requires_purchase_confirmation',
        13 => 'tests\\feature\\purchase_with_invalid_quantity_is_rejected',
        14 => 'tests\\feature\\unauthenticated_user_is_redirected_to_login',
        15 => 'tests\\feature\\purchase_includes_proper_fee_calculation',
        16 => 'tests\\feature\\user_can_cancel_pending_purchase',
        17 => 'tests\\feature\\setup',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/tests/Feature/Alerts/AlertCrudTest.php' => 
    array (
      0 => '4ebf8ed002b3ebdb021cfdd753374f31fa6e48c4',
      1 => 
      array (
        0 => 'tests\\feature\\alerts\\alertcrudtest',
      ),
      2 => 
      array (
        0 => 'tests\\feature\\alerts\\user_can_create_update_delete_and_toggle_alerts',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/tests/Feature/ModernRegistrationFlowTest.php' => 
    array (
      0 => '26dfdb5c1c45aaab1bacfd545a6cc9579bbfe59f',
      1 => 
      array (
        0 => 'tests\\feature\\modernregistrationflowtest',
      ),
      2 => 
      array (
        0 => 'tests\\feature\\registration_page_displays_modern_stepper_interface',
        1 => 'tests\\feature\\validation_endpoint_provides_field_level_feedback',
        2 => 'tests\\feature\\email_availability_endpoint_checks_uniqueness',
        3 => 'tests\\feature\\password_strength_endpoint_provides_detailed_feedback',
        4 => 'tests\\feature\\complete_registration_flow_with_all_features',
        5 => 'tests\\feature\\registration_fails_without_required_legal_acceptances',
        6 => 'tests\\feature\\registration_handles_step_navigation_correctly',
        7 => 'tests\\feature\\validation_endpoints_respect_rate_limiting',
        8 => 'tests\\feature\\registration_preserves_server_side_validation_as_source_of_truth',
        9 => 'tests\\feature\\registration_handles_optional_fields_correctly',
        10 => 'tests\\feature\\setup',
        11 => 'tests\\feature\\createrequiredlegaldocuments',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/tests/Feature/Dashboard/CustomerDashboardAccessTest.php' => 
    array (
      0 => 'f39855f4811a73cc411923234c12f98d00548e0a',
      1 => 
      array (
        0 => 'tests\\feature\\dashboard\\customerdashboardaccesstest',
      ),
      2 => 
      array (
        0 => 'tests\\feature\\dashboard\\customer_dashboard_route_access_matrix',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/tests/Feature/Dashboard/CustomerDashboardApiTest.php' => 
    array (
      0 => '93ad04b66c65da8bb12f19ac28427d1a9dc35e30',
      1 => 
      array (
        0 => 'tests\\feature\\dashboard\\customerdashboardapitest',
      ),
      2 => 
      array (
        0 => 'tests\\feature\\dashboard\\tiles_and_lists_payload_shapes_are_consistent',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/tests/Feature/WelcomePageTest.php' => 
    array (
      0 => '4181d2c913ced5b948b7ba7fa2176e3fffd11557',
      1 => 
      array (
        0 => 'tests\\feature\\welcomepagetest',
      ),
      2 => 
      array (
        0 => 'tests\\feature\\test_welcome_page_renders_correctly_for_guests',
        1 => 'tests\\feature\\test_welcome_page_shows_authenticated_user_greeting',
        2 => 'tests\\feature\\test_welcome_page_displays_correct_role_information',
        3 => 'tests\\feature\\welcome_page_displays_subscription_plans',
        4 => 'tests\\feature\\welcome_page_displays_security_features',
        5 => 'tests\\feature\\welcome_page_displays_legal_compliance_information',
        6 => 'tests\\feature\\test_welcome_page_contains_proper_seo_meta_tags',
        7 => 'tests\\feature\\welcome_page_includes_structured_data',
        8 => 'tests\\feature\\welcome_page_has_correct_footer_legal_links',
        9 => 'tests\\feature\\welcome_stats_api_returns_correct_data',
        10 => 'tests\\feature\\welcome_page_handles_different_user_roles_correctly',
        11 => 'tests\\feature\\welcome_page_caches_data_properly',
        12 => 'tests\\feature\\welcome_page_handles_subscription_info_for_authenticated_users',
        13 => 'tests\\feature\\welcome_page_includes_alpine_js_components',
        14 => 'tests\\feature\\welcome_page_is_mobile_responsive',
        15 => 'tests\\feature\\welcome_page_security_headers_are_present',
        16 => 'tests\\feature\\welcome_page_handles_fallback_data_gracefully',
        17 => 'tests\\feature\\welcome_page_tracks_analytics_properly',
        18 => 'tests\\feature\\welcome_page_ab_testing_works_correctly',
        19 => 'tests\\feature\\welcome_page_redirects_based_on_configuration',
        20 => 'tests\\feature\\welcome_page_includes_accessibility_features',
        21 => 'tests\\feature\\welcome_page_language_content_is_correct',
        22 => 'tests\\feature\\welcome_page_displays_scraper_role_notice',
        23 => 'tests\\feature\\setup',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/tests/Feature/Auth/LoginTest.php' => 
    array (
      0 => 'bf2b6d00c1d83de5bc3f09b3771f6eaf3a31c1ea',
      1 => 
      array (
        0 => 'tests\\feature\\auth\\logintest',
      ),
      2 => 
      array (
        0 => 'tests\\feature\\auth\\user_can_view_login_page',
        1 => 'tests\\feature\\auth\\user_can_login_with_valid_credentials',
        2 => 'tests\\feature\\auth\\user_cannot_login_with_invalid_email',
        3 => 'tests\\feature\\auth\\user_cannot_login_with_invalid_password',
        4 => 'tests\\feature\\auth\\user_cannot_login_with_empty_credentials',
        5 => 'tests\\feature\\auth\\user_cannot_login_with_invalid_email_format',
        6 => 'tests\\feature\\auth\\user_cannot_login_when_account_is_deactivated',
        7 => 'tests\\feature\\auth\\scraper_user_cannot_access_web_login',
        8 => 'tests\\feature\\auth\\user_account_gets_locked_after_failed_attempts',
        9 => 'tests\\feature\\auth\\user_cannot_login_when_account_is_locked',
        10 => 'tests\\feature\\auth\\user_can_login_after_lockout_expires',
        11 => 'tests\\feature\\auth\\failed_login_attempts_are_reset_after_successful_login',
        12 => 'tests\\feature\\auth\\login_is_rate_limited_per_ip',
        13 => 'tests\\feature\\auth\\remember_me_functionality_works',
        14 => 'tests\\feature\\auth\\honeypot_field_prevents_bot_submissions',
        15 => 'tests\\feature\\auth\\two_factor_enabled_user_is_redirected_to_2fa_challenge',
        16 => 'tests\\feature\\auth\\login_logs_successful_activity',
        17 => 'tests\\feature\\auth\\login_updates_user_login_metadata',
        18 => 'tests\\feature\\auth\\security_headers_are_present_on_login_page',
        19 => 'tests\\feature\\auth\\csrf_token_is_required_for_login',
        20 => 'tests\\feature\\auth\\device_fingerprinting_data_is_processed',
        21 => 'tests\\feature\\auth\\login_form_preserves_email_on_validation_failure',
        22 => 'tests\\feature\\auth\\redirect_after_login_works_correctly',
        23 => 'tests\\feature\\auth\\login_with_recaptcha_when_enabled',
        24 => 'tests\\feature\\auth\\login_fails_with_invalid_recaptcha',
        25 => 'tests\\feature\\auth\\enhanced_security_middleware_processes_requests',
        26 => 'tests\\feature\\auth\\setup',
        27 => 'tests\\feature\\auth\\teardown',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/tests/Feature/Auth/EnhancedLoginTest.php' => 
    array (
      0 => '3fa08160e8a3130442e2963a7d31da02ed98dfe8',
      1 => 
      array (
        0 => 'tests\\feature\\auth\\enhancedlogintest',
      ),
      2 => 
      array (
        0 => 'tests\\feature\\auth\\it_displays_the_login_page',
        1 => 'tests\\feature\\auth\\it_displays_the_enhanced_login_page_when_configured',
        2 => 'tests\\feature\\auth\\it_can_successfully_login_with_valid_credentials',
        3 => 'tests\\feature\\auth\\it_rejects_invalid_credentials',
        4 => 'tests\\feature\\auth\\it_locks_account_after_multiple_failed_attempts',
        5 => 'tests\\feature\\auth\\it_rejects_login_for_locked_account',
        6 => 'tests\\feature\\auth\\it_rejects_login_for_inactive_account',
        7 => 'tests\\feature\\auth\\it_rejects_login_for_scraper_accounts',
        8 => 'tests\\feature\\auth\\it_enforces_rate_limiting',
        9 => 'tests\\feature\\auth\\it_handles_two_factor_authentication_redirect',
        10 => 'tests\\feature\\auth\\it_can_complete_two_factor_authentication',
        11 => 'tests\\feature\\auth\\it_handles_invalid_two_factor_codes',
        12 => 'tests\\feature\\auth\\it_can_use_recovery_codes_for_two_factor',
        13 => 'tests\\feature\\auth\\it_can_send_sms_backup_codes',
        14 => 'tests\\feature\\auth\\it_can_send_email_backup_codes',
        15 => 'tests\\feature\\auth\\it_validates_required_login_fields',
        16 => 'tests\\feature\\auth\\it_validates_email_format',
        17 => 'tests\\feature\\auth\\it_handles_honeypot_field',
        18 => 'tests\\feature\\auth\\it_can_check_email_availability',
        19 => 'tests\\feature\\auth\\it_handles_nonexistent_email_check',
        20 => 'tests\\feature\\auth\\it_rate_limits_email_checks',
        21 => 'tests\\feature\\auth\\it_processes_device_fingerprinting',
        22 => 'tests\\feature\\auth\\it_remembers_user_when_requested',
        23 => 'tests\\feature\\auth\\it_clears_failed_attempts_on_successful_login',
        24 => 'tests\\feature\\auth\\it_logs_successful_login_activity',
        25 => 'tests\\feature\\auth\\it_redirects_to_intended_url_after_login',
        26 => 'tests\\feature\\auth\\it_handles_logout_correctly',
        27 => 'tests\\feature\\auth\\it_displays_two_factor_challenge_page',
        28 => 'tests\\feature\\auth\\it_redirects_to_login_if_no_2fa_session',
        29 => 'tests\\feature\\auth\\setup',
        30 => 'tests\\feature\\auth\\teardown',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/tests/Feature/UserRegistrationTest.php' => 
    array (
      0 => '3f96481b05f9cf0a9041d104dba3100c3d4b9b37',
      1 => 
      array (
        0 => 'tests\\feature\\userregistrationtest',
      ),
      2 => 
      array (
        0 => 'tests\\feature\\test_public_registration_form_displays_legal_documents',
        1 => 'tests\\feature\\test_successful_customer_registration',
        2 => 'tests\\feature\\test_registration_requires_legal_document_acceptance',
        3 => 'tests\\feature\\test_registration_validates_email_uniqueness',
        4 => 'tests\\feature\\test_registration_with_2fa_enabled',
        5 => 'tests\\feature\\test_registration_validates_phone_format',
        6 => 'tests\\feature\\test_registration_fails_without_legal_documents',
        7 => 'tests\\feature\\test_user_legal_acceptance_tracking',
        8 => 'tests\\feature\\test_customer_role_assignment',
        9 => 'tests\\feature\\setup',
        10 => 'tests\\feature\\createrequiredlegaldocuments',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/tests/Feature/Tickets/TicketFiltersTest.php' => 
    array (
      0 => 'bc5c47ba4f9c1f3b56218ca5a0c6583fe3bc72a7',
      1 => 
      array (
        0 => 'tests\\feature\\tickets\\ticketfilterstest',
      ),
      2 => 
      array (
        0 => 'tests\\feature\\tickets\\filters_and_sorting_work_and_paginate',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/tests/Integration/TicketPurchaseSystemTest.php' => 
    array (
      0 => '49197447e4dea1029a9f0e59b647ac2026044bdb',
      1 => 
      array (
        0 => 'tests\\integration\\ticketpurchasesystemtest',
      ),
      2 => 
      array (
        0 => 'tests\\integration\\testticketpurchasesystemisworkingcorrectly',
        1 => 'tests\\integration\\testagenthasunlimitedaccess',
        2 => 'tests\\integration\\testcustomerwithoutsubscriptionisdenied',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/tests/Integration/Purchases/MonthlyUsageAggregationTest.php' => 
    array (
      0 => '9d2241a011ba1f270d20258863fd60b2d8d6f62f',
      1 => 
      array (
        0 => 'tests\\integration\\purchases\\monthlyusageaggregationtest',
      ),
      2 => 
      array (
        0 => 'tests\\integration\\purchases\\monthly_usage_includes_pending_and_confirmed_and_handles_boundaries',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/tests/Unit/Models/UserSubscriptionTest.php' => 
    array (
      0 => '6c9198863dcc266dac9b14863a3079063da21a23',
      1 => 
      array (
        0 => 'tests\\unit\\models\\usersubscriptiontest',
      ),
      2 => 
      array (
        0 => 'tests\\unit\\models\\subscription_helpers_are_defined',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/tests/Unit/Models/TicketPurchaseTest.php' => 
    array (
      0 => '9be06d223ebbd006ea6fa2de8afda03917e0a484',
      1 => 
      array (
        0 => 'tests\\unit\\models\\ticketpurchasetest',
      ),
      2 => 
      array (
        0 => 'tests\\unit\\models\\it_can_create_a_ticket_purchase',
        1 => 'tests\\unit\\models\\it_has_proper_fillable_attributes',
        2 => 'tests\\unit\\models\\it_casts_attributes_correctly',
        3 => 'tests\\unit\\models\\it_has_relationship_with_user',
        4 => 'tests\\unit\\models\\it_has_relationship_with_ticket',
        5 => 'tests\\unit\\models\\it_can_check_if_purchase_is_pending',
        6 => 'tests\\unit\\models\\it_can_check_if_purchase_is_confirmed',
        7 => 'tests\\unit\\models\\it_can_check_if_purchase_is_cancelled',
        8 => 'tests\\unit\\models\\it_can_check_if_purchase_is_failed',
        9 => 'tests\\unit\\models\\it_can_calculate_total_tickets_for_purchase',
        10 => 'tests\\unit\\models\\it_can_get_formatted_purchase_date',
        11 => 'tests\\unit\\models\\it_can_get_formatted_total_amount',
        12 => 'tests\\unit\\models\\it_can_check_if_purchase_can_be_cancelled',
        13 => 'tests\\unit\\models\\it_can_get_processing_fee_percentage',
        14 => 'tests\\unit\\models\\it_handles_zero_subtotal_for_processing_fee_percentage',
        15 => 'tests\\unit\\models\\it_can_get_seat_preferences_summary',
        16 => 'tests\\unit\\models\\it_returns_empty_string_for_no_seat_preferences',
        17 => 'tests\\unit\\models\\it_can_scope_by_status',
        18 => 'tests\\unit\\models\\it_can_scope_by_user',
        19 => 'tests\\unit\\models\\it_can_scope_by_date_range',
        20 => 'tests\\unit\\models\\it_can_scope_by_current_month',
        21 => 'tests\\unit\\models\\it_validates_required_fields',
        22 => 'tests\\unit\\models\\it_generates_unique_purchase_ids',
        23 => 'tests\\unit\\models\\it_can_get_purchase_age_in_days',
        24 => 'tests\\unit\\models\\it_can_check_if_purchase_is_recent',
        25 => 'tests\\unit\\models\\createtestpurchase',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/tests/Unit/Services/TicketPurchaseServiceTest.php' => 
    array (
      0 => 'fcd84f1d388eb928e731e363fc4be0229f8c9768',
      1 => 
      array (
        0 => 'tests\\unit\\services\\ticketpurchaseservicetest',
      ),
      2 => 
      array (
        0 => 'tests\\unit\\services\\it_can_check_purchase_eligibility_for_customer_with_active_subscription',
        1 => 'tests\\unit\\services\\it_denies_purchase_for_customer_without_active_subscription',
        2 => 'tests\\unit\\services\\it_denies_purchase_for_customer_exceeding_ticket_limit',
        3 => 'tests\\unit\\services\\it_allows_unlimited_purchases_for_agent',
        4 => 'tests\\unit\\services\\it_allows_unlimited_purchases_for_admin',
        5 => 'tests\\unit\\services\\it_denies_purchase_for_unavailable_ticket',
        6 => 'tests\\unit\\services\\it_denies_purchase_when_quantity_exceeds_availability',
        7 => 'tests\\unit\\services\\it_can_create_successful_purchase',
        8 => 'tests\\unit\\services\\it_calculates_fees_correctly',
        9 => 'tests\\unit\\services\\it_generates_unique_purchase_ids',
        10 => 'tests\\unit\\services\\it_can_confirm_purchase',
        11 => 'tests\\unit\\services\\it_can_cancel_purchase',
        12 => 'tests\\unit\\services\\it_can_get_user_monthly_ticket_usage',
        13 => 'tests\\unit\\services\\it_handles_edge_case_of_zero_quantity_purchase',
        14 => 'tests\\unit\\services\\it_handles_negative_quantity_purchase',
        15 => 'tests\\unit\\services\\it_respects_free_access_period_for_new_customers',
        16 => 'tests\\unit\\services\\it_denies_purchase_after_free_access_period_expires',
        17 => 'tests\\unit\\services\\setup',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/tests/Unit/Services/WelcomePageServiceTest.php' => 
    array (
      0 => 'b8f112df9d53fadbad0d5ca208ce69f07ee3de68',
      1 => 
      array (
        0 => 'tests\\unit\\services\\welcomepageservicetest',
      ),
      2 => 
      array (
        0 => 'tests\\unit\\services\\it_returns_complete_welcome_page_data',
        1 => 'tests\\unit\\services\\it_can_exclude_specific_data_sections',
        2 => 'tests\\unit\\services\\it_returns_statistics_with_correct_structure',
        3 => 'tests\\unit\\services\\it_caches_statistics_properly',
        4 => 'tests\\unit\\services\\it_returns_pricing_information_with_defaults',
        5 => 'tests\\unit\\services\\it_returns_features_list_with_correct_categories',
        6 => 'tests\\unit\\services\\it_returns_legal_documents_information',
        7 => 'tests\\unit\\services\\it_returns_role_information_for_all_roles',
        8 => 'tests\\unit\\services\\it_returns_security_features_information',
        9 => 'tests\\unit\\services\\it_tracks_page_views_properly',
        10 => 'tests\\unit\\services\\it_applies_ab_test_variants_correctly',
        11 => 'tests\\unit\\services\\it_returns_fallback_stats_on_exception',
        12 => 'tests\\unit\\services\\it_formats_user_count_correctly',
        13 => 'tests\\unit\\services\\it_gets_user_subscription_info_correctly',
        14 => 'tests\\unit\\services\\it_handles_exceptions_gracefully_in_user_subscription_info',
        15 => 'tests\\unit\\services\\it_caches_pricing_information',
        16 => 'tests\\unit\\services\\it_caches_features_list',
        17 => 'tests\\unit\\services\\setup',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/tests/Unit/Services/Email/ImapConnectionServiceTest.php' => 
    array (
      0 => '91a82b3c2d924d42db4c0c76a0f1658c216f7235',
      1 => 
      array (
        0 => 'tests\\unit\\services\\email\\imapconnectionservicetest',
      ),
      2 => 
      array (
        0 => 'tests\\unit\\services\\email\\it_can_be_instantiated',
        1 => 'tests\\unit\\services\\email\\it_throws_exception_for_unknown_connection',
        2 => 'tests\\unit\\services\\email\\it_throws_exception_for_missing_credentials',
        3 => 'tests\\unit\\services\\email\\it_builds_connection_string_correctly',
        4 => 'tests\\unit\\services\\email\\it_gets_connection_statistics',
        5 => 'tests\\unit\\services\\email\\it_handles_connection_cleanup',
        6 => 'tests\\unit\\services\\email\\it_can_close_all_connections',
        7 => 'tests\\unit\\services\\email\\test_connection_returns_failure_for_invalid_config',
        8 => 'tests\\unit\\services\\email\\setup',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/tests/Unit/Services/Email/EmailParsingServiceTest.php' => 
    array (
      0 => 'a5bc659eca8f0b0596105b99a20ff82d39e42e72',
      1 => 
      array (
        0 => 'tests\\unit\\services\\email\\emailparsingservicetest',
      ),
      2 => 
      array (
        0 => 'tests\\unit\\services\\email\\it_can_be_instantiated',
        1 => 'tests\\unit\\services\\email\\it_detects_sport_categories_correctly',
        2 => 'tests\\unit\\services\\email\\it_parses_ticketmaster_emails',
        3 => 'tests\\unit\\services\\email\\it_parses_stubhub_emails',
        4 => 'tests\\unit\\services\\email\\it_parses_generic_sports_emails',
        5 => 'tests\\unit\\services\\email\\it_validates_parsed_data',
        6 => 'tests\\unit\\services\\email\\it_extracts_email_metadata',
        7 => 'tests\\unit\\services\\email\\it_parses_event_dates',
        8 => 'tests\\unit\\services\\email\\it_gets_parsing_statistics',
        9 => 'tests\\unit\\services\\email\\it_parses_complete_email_data',
        10 => 'tests\\unit\\services\\email\\setup',
        11 => 'tests\\unit\\services\\email\\config',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/tests/Unit/Services/RecommendationServiceTest.php' => 
    array (
      0 => 'fc28397f85e555b3212b58a49519e561e2954efc',
      1 => 
      array (
        0 => 'tests\\unit\\services\\recommendationservicetest',
      ),
      2 => 
      array (
        0 => 'tests\\unit\\services\\can_generate_recommendations_for_user',
      ),
      3 => 
      array (
      ),
    ),
    '/var/www/hdtickets/tests/Unit/Middleware/TicketPurchaseValidationMiddlewareTest.php' => 
    array (
      0 => '67481c24757fd065dc7e02d3a4e12178a4a03407',
      1 => 
      array (
        0 => 'tests\\unit\\middleware\\ticketpurchasevalidationmiddlewaretest',
      ),
      2 => 
      array (
        0 => 'tests\\unit\\middleware\\it_allows_purchase_for_customer_with_active_subscription',
        1 => 'tests\\unit\\middleware\\it_blocks_purchase_for_customer_without_active_subscription',
        2 => 'tests\\unit\\middleware\\it_allows_unlimited_purchases_for_agent',
        3 => 'tests\\unit\\middleware\\it_blocks_purchase_when_exceeding_ticket_limit',
        4 => 'tests\\unit\\middleware\\it_blocks_purchase_for_unavailable_ticket',
        5 => 'tests\\unit\\middleware\\it_blocks_purchase_when_quantity_exceeds_availability',
        6 => 'tests\\unit\\middleware\\it_handles_missing_quantity_parameter',
        7 => 'tests\\unit\\middleware\\it_handles_invalid_quantity_parameter',
        8 => 'tests\\unit\\middleware\\it_handles_zero_quantity',
        9 => 'tests\\unit\\middleware\\it_handles_missing_ticket_parameter',
        10 => 'tests\\unit\\middleware\\it_handles_unauthenticated_user',
        11 => 'tests\\unit\\middleware\\it_provides_eligibility_information_in_response',
        12 => 'tests\\unit\\middleware\\it_respects_free_access_period_for_new_customers',
        13 => 'tests\\unit\\middleware\\it_blocks_purchase_after_free_access_expires',
        14 => 'tests\\unit\\middleware\\it_handles_service_exceptions_gracefully',
        15 => 'tests\\unit\\middleware\\setup',
        16 => 'tests\\unit\\middleware\\teardown',
      ),
      3 => 
      array (
      ),
    ),
  ),
));