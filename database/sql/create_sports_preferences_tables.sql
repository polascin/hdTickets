-- SQL script to create sports preferences tables
-- Run this script manually due to migration system issues

-- Create user_favorite_teams table
CREATE TABLE IF NOT EXISTS `user_favorite_teams` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `user_id` bigint(20) unsigned NOT NULL,
    `sport_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
    `team_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `team_slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `league` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
    `team_logo_url` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `team_city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `aliases` json DEFAULT NULL,
    `email_alerts` tinyint(1) NOT NULL DEFAULT '1',
    `push_alerts` tinyint(1) NOT NULL DEFAULT '0',
    `sms_alerts` tinyint(1) NOT NULL DEFAULT '0',
    `priority` int(11) NOT NULL DEFAULT '3',
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `user_favorite_teams_user_id_foreign` (`user_id`),
    KEY `user_favorite_teams_sport_type_index` (`sport_type`),
    KEY `user_favorite_teams_league_index` (`league`),
    KEY `user_favorite_teams_priority_index` (`priority`),
    KEY `user_favorite_teams_email_alerts_index` (`email_alerts`),
    UNIQUE KEY `user_favorite_teams_unique` (`user_id`, `sport_type`, `team_name`, `league`),
    CONSTRAINT `user_favorite_teams_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create user_favorite_venues table
CREATE TABLE IF NOT EXISTS `user_favorite_venues` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `user_id` bigint(20) unsigned NOT NULL,
    `venue_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `venue_slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `city` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `state_province` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `country` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
    `capacity` int(11) DEFAULT NULL,
    `venue_types` json DEFAULT NULL,
    `latitude` decimal(10,7) DEFAULT NULL,
    `longitude` decimal(10,7) DEFAULT NULL,
    `venue_image_url` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `aliases` json DEFAULT NULL,
    `email_alerts` tinyint(1) NOT NULL DEFAULT '1',
    `push_alerts` tinyint(1) NOT NULL DEFAULT '0',
    `sms_alerts` tinyint(1) NOT NULL DEFAULT '0',
    `priority` int(11) NOT NULL DEFAULT '3',
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `user_favorite_venues_user_id_foreign` (`user_id`),
    KEY `user_favorite_venues_city_index` (`city`),
    KEY `user_favorite_venues_state_province_index` (`state_province`),
    KEY `user_favorite_venues_country_index` (`country`),
    KEY `user_favorite_venues_priority_index` (`priority`),
    KEY `user_favorite_venues_email_alerts_index` (`email_alerts`),
    KEY `user_favorite_venues_coordinates_index` (`latitude`, `longitude`),
    UNIQUE KEY `user_favorite_venues_unique` (`user_id`, `venue_name`, `city`),
    CONSTRAINT `user_favorite_venues_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create user_price_preferences table
CREATE TABLE IF NOT EXISTS `user_price_preferences` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `user_id` bigint(20) unsigned NOT NULL,
    `preference_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `sport_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `event_category` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `min_price` decimal(10,2) DEFAULT NULL,
    `max_price` decimal(10,2) NOT NULL,
    `preferred_quantity` int(11) NOT NULL DEFAULT '2',
    `seat_preferences` json DEFAULT NULL,
    `section_preferences` json DEFAULT NULL,
    `price_drop_threshold` decimal(5,2) DEFAULT NULL,
    `price_increase_threshold` decimal(5,2) DEFAULT NULL,
    `auto_purchase_enabled` tinyint(1) NOT NULL DEFAULT '0',
    `auto_purchase_max_price` decimal(10,2) DEFAULT NULL,
    `email_alerts` tinyint(1) NOT NULL DEFAULT '1',
    `push_alerts` tinyint(1) NOT NULL DEFAULT '0',
    `sms_alerts` tinyint(1) NOT NULL DEFAULT '0',
    `alert_frequency` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'immediate',
    `is_active` tinyint(1) NOT NULL DEFAULT '1',
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `user_price_preferences_user_id_foreign` (`user_id`),
    KEY `user_price_preferences_sport_type_index` (`sport_type`),
    KEY `user_price_preferences_event_category_index` (`event_category`),
    KEY `user_price_preferences_is_active_index` (`is_active`),
    KEY `user_price_preferences_max_price_index` (`max_price`),
    KEY `user_price_preferences_email_alerts_index` (`email_alerts`),
    KEY `user_price_preferences_auto_purchase_index` (`auto_purchase_enabled`),
    CONSTRAINT `user_price_preferences_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add indexes for better performance
CREATE INDEX `user_favorite_teams_search_index` ON `user_favorite_teams` (`team_name`, `team_city`);
CREATE INDEX `user_favorite_venues_search_index` ON `user_favorite_venues` (`venue_name`, `city`);
CREATE INDEX `user_price_preferences_price_range_index` ON `user_price_preferences` (`min_price`, `max_price`);

-- Insert some sample data (optional)
-- You can uncomment and modify these if you want some test data

/*
-- Sample favorite teams
INSERT INTO `user_favorite_teams` (`user_id`, `sport_type`, `team_name`, `team_city`, `league`, `priority`, `email_alerts`) VALUES
(1, 'football', 'Chiefs', 'Kansas City', 'NFL', 5, 1),
(1, 'basketball', 'Lakers', 'Los Angeles', 'NBA', 4, 1),
(1, 'baseball', 'Yankees', 'New York', 'MLB', 3, 1);

-- Sample favorite venues
INSERT INTO `user_favorite_venues` (`user_id`, `venue_name`, `city`, `state_province`, `country`, `venue_types`, `priority`, `email_alerts`) VALUES
(1, 'Arrowhead Stadium', 'Kansas City', 'Missouri', 'USA', '["stadium"]', 5, 1),
(1, 'Staples Center', 'Los Angeles', 'California', 'USA', '["arena"]', 4, 1),
(1, 'Yankee Stadium', 'New York', 'New York', 'USA', '["stadium"]', 3, 1);

-- Sample price preferences
INSERT INTO `user_price_preferences` (`user_id`, `preference_name`, `sport_type`, `event_category`, `max_price`, `preferred_quantity`, `alert_frequency`, `is_active`, `email_alerts`) VALUES
(1, 'NFL Regular Season', 'football', 'regular_season', 150.00, 2, 'immediate', 1, 1),
(1, 'NBA Playoffs', 'basketball', 'playoffs', 300.00, 2, 'immediate', 1, 1),
(1, 'MLB World Series', 'baseball', 'championship', 500.00, 2, 'hourly', 1, 1);
*/

-- Add comments to tables for documentation
ALTER TABLE `user_favorite_teams` COMMENT = 'Stores user favorite sports teams with notification preferences';
ALTER TABLE `user_favorite_venues` COMMENT = 'Stores user favorite venues/stadiums with location data and notification preferences';
ALTER TABLE `user_price_preferences` COMMENT = 'Stores user price preferences and alert settings for ticket monitoring';

SELECT 'Sports preferences tables created successfully!' as message;
