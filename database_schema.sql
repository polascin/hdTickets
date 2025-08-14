-- =============================================
-- Ticket Monitoring Application Database Schema
-- =============================================
-- Created: 2024
-- Database: MySQL/MariaDB
-- Charset: utf8mb4
-- Collation: utf8mb4_unicode_ci
-- =============================================

-- Set charset and collation for the session
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

-- =============================================
-- TABLE: users
-- Purpose: Application users with authentication
-- =============================================
CREATE TABLE `users` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `uuid` CHAR(36) NOT NULL UNIQUE,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(191) NOT NULL UNIQUE,
    `email_verified_at` TIMESTAMP NULL,
    `password` VARCHAR(255) NOT NULL,
    `first_name` VARCHAR(100) NULL,
    `last_name` VARCHAR(100) NULL,
    `phone` VARCHAR(20) NULL,
    `timezone` VARCHAR(50) DEFAULT 'UTC',
    `status` ENUM('active', 'inactive', 'suspended', 'banned') DEFAULT 'active',
    `last_login_at` TIMESTAMP NULL,
    `last_login_ip` VARCHAR(45) NULL,
    `failed_login_attempts` TINYINT UNSIGNED DEFAULT 0,
    `locked_until` TIMESTAMP NULL,
    `two_factor_enabled` BOOLEAN DEFAULT FALSE,
    `two_factor_secret` VARCHAR(255) NULL,
    `remember_token` VARCHAR(100) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_users_email` (`email`),
    INDEX `idx_users_username` (`username`),
    INDEX `idx_users_status` (`status`),
    INDEX `idx_users_uuid` (`uuid`),
    INDEX `idx_users_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: ticket_accounts
-- Purpose: Store encrypted credentials for Ticketmaster/Man United accounts
-- =============================================
CREATE TABLE `ticket_accounts` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `uuid` CHAR(36) NOT NULL UNIQUE,
    `account_type` ENUM('ticketmaster', 'manchester_united', 'other') NOT NULL,
    `account_name` VARCHAR(100) NOT NULL,
    `encrypted_username` TEXT NOT NULL,
    `encrypted_password` TEXT NOT NULL,
    `encrypted_additional_data` JSON NULL COMMENT 'Store security questions, phone numbers, etc.',
    `encryption_method` VARCHAR(50) DEFAULT 'AES-256-CBC',
    `last_validated_at` TIMESTAMP NULL,
    `validation_status` ENUM('valid', 'invalid', 'expired', 'locked', 'unknown') DEFAULT 'unknown',
    `validation_error` TEXT NULL,
    `is_active` BOOLEAN DEFAULT TRUE,
    `usage_count` INT UNSIGNED DEFAULT 0,
    `last_used_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_ticket_accounts_user_id` (`user_id`),
    INDEX `idx_ticket_accounts_account_type` (`account_type`),
    INDEX `idx_ticket_accounts_is_active` (`is_active`),
    INDEX `idx_ticket_accounts_validation_status` (`validation_status`),
    INDEX `idx_ticket_accounts_uuid` (`uuid`),
    UNIQUE KEY `unique_user_account` (`user_id`, `account_type`, `account_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: monitoring_criteria
-- Purpose: User-defined rules for ticket selection
-- =============================================
CREATE TABLE `monitoring_criteria` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `ticket_account_id` BIGINT UNSIGNED NOT NULL,
    `uuid` CHAR(36) NOT NULL UNIQUE,
    `name` VARCHAR(150) NOT NULL,
    `description` TEXT NULL,
    `event_keywords` JSON NULL COMMENT 'Array of keywords to match in event names',
    `venue_keywords` JSON NULL COMMENT 'Array of venue names or keywords',
    `date_range_start` DATE NULL,
    `date_range_end` DATE NULL,
    `time_range_start` TIME NULL,
    `time_range_end` TIME NULL,
    `price_range_min` DECIMAL(10,2) NULL,
    `price_range_max` DECIMAL(10,2) NULL,
    `currency` CHAR(3) DEFAULT 'GBP',
    `seat_preferences` JSON NULL COMMENT 'Section preferences, accessibility needs, etc.',
    `ticket_quantity_min` TINYINT UNSIGNED DEFAULT 1,
    `ticket_quantity_max` TINYINT UNSIGNED DEFAULT 10,
    `priority_level` TINYINT UNSIGNED DEFAULT 1 COMMENT '1=highest, 10=lowest',
    `auto_purchase_enabled` BOOLEAN DEFAULT FALSE,
    `max_purchase_attempts` TINYINT UNSIGNED DEFAULT 3,
    `notification_enabled` BOOLEAN DEFAULT TRUE,
    `is_active` BOOLEAN DEFAULT TRUE,
    `monitoring_frequency` ENUM('continuous', 'hourly', 'daily', 'weekly') DEFAULT 'hourly',
    `last_checked_at` TIMESTAMP NULL,
    `next_check_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`ticket_account_id`) REFERENCES `ticket_accounts`(`id`) ON DELETE CASCADE,
    INDEX `idx_monitoring_criteria_user_id` (`user_id`),
    INDEX `idx_monitoring_criteria_ticket_account_id` (`ticket_account_id`),
    INDEX `idx_monitoring_criteria_is_active` (`is_active`),
    INDEX `idx_monitoring_criteria_priority` (`priority_level`),
    INDEX `idx_monitoring_criteria_next_check` (`next_check_at`),
    INDEX `idx_monitoring_criteria_uuid` (`uuid`),
    INDEX `idx_monitoring_criteria_date_range` (`date_range_start`, `date_range_end`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: events
-- Purpose: Cached event information
-- =============================================
CREATE TABLE `events` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `uuid` CHAR(36) NOT NULL UNIQUE,
    `external_id` VARCHAR(100) NULL COMMENT 'ID from ticketing platform',
    `platform` ENUM('ticketmaster', 'manchester_united', 'other') NOT NULL,
    `event_name` VARCHAR(255) NOT NULL,
    `event_description` TEXT NULL,
    `venue_name` VARCHAR(255) NULL,
    `venue_address` TEXT NULL,
    `venue_city` VARCHAR(100) NULL,
    `venue_country` CHAR(2) NULL,
    `event_date` DATE NOT NULL,
    `event_time` TIME NULL,
    `event_timezone` VARCHAR(50) DEFAULT 'UTC',
    `category` VARCHAR(100) NULL COMMENT 'Football, Concert, Theatre, etc.',
    `subcategory` VARCHAR(100) NULL,
    `price_range_min` DECIMAL(10,2) NULL,
    `price_range_max` DECIMAL(10,2) NULL,
    `currency` CHAR(3) DEFAULT 'GBP',
    `total_tickets_available` INT UNSIGNED NULL,
    `tickets_remaining` INT UNSIGNED NULL,
    `sale_start_date` DATETIME NULL,
    `sale_end_date` DATETIME NULL,
    `presale_start_date` DATETIME NULL,
    `presale_end_date` DATETIME NULL,
    `status` ENUM('upcoming', 'on_sale', 'sold_out', 'cancelled', 'postponed', 'ended') DEFAULT 'upcoming',
    `image_url` VARCHAR(500) NULL,
    `event_url` VARCHAR(500) NULL,
    `metadata` JSON NULL COMMENT 'Additional platform-specific data',
    `last_scraped_at` TIMESTAMP NULL,
    `scrape_frequency` ENUM('high', 'medium', 'low') DEFAULT 'medium',
    `is_monitored` BOOLEAN DEFAULT FALSE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_events_platform` (`platform`),
    INDEX `idx_events_event_date` (`event_date`),
    INDEX `idx_events_status` (`status`),
    INDEX `idx_events_venue_city` (`venue_city`),
    INDEX `idx_events_category` (`category`),
    INDEX `idx_events_is_monitored` (`is_monitored`),
    INDEX `idx_events_sale_dates` (`sale_start_date`, `sale_end_date`),
    INDEX `idx_events_external_id` (`external_id`, `platform`),
    INDEX `idx_events_uuid` (`uuid`),
    FULLTEXT KEY `fulltext_event_search` (`event_name`, `event_description`, `venue_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: ticket_checks
-- Purpose: Log of all monitoring activities
-- =============================================
CREATE TABLE `ticket_checks` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `uuid` CHAR(36) NOT NULL UNIQUE,
    `monitoring_criteria_id` BIGINT UNSIGNED NOT NULL,
    `event_id` BIGINT UNSIGNED NULL,
    `proxy_id` BIGINT UNSIGNED NULL,
    `check_type` ENUM('scheduled', 'manual', 'triggered') DEFAULT 'scheduled',
    `status` ENUM('success', 'failed', 'timeout', 'blocked', 'error') NOT NULL,
    `response_time_ms` INT UNSIGNED NULL,
    `tickets_found` INT UNSIGNED DEFAULT 0,
    `matching_tickets` JSON NULL COMMENT 'Array of ticket details that matched criteria',
    `error_message` TEXT NULL,
    `error_code` VARCHAR(50) NULL,
    `http_status_code` INT UNSIGNED NULL,
    `user_agent` VARCHAR(500) NULL,
    `ip_address` VARCHAR(45) NULL,
    `session_id` VARCHAR(100) NULL,
    `scraped_data` JSON NULL COMMENT 'Raw data from the scraping attempt',
    `metadata` JSON NULL COMMENT 'Additional check-specific information',
    `started_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `completed_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`monitoring_criteria_id`) REFERENCES `monitoring_criteria`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`proxy_id`) REFERENCES `proxies`(`id`) ON DELETE SET NULL,
    INDEX `idx_ticket_checks_monitoring_criteria` (`monitoring_criteria_id`),
    INDEX `idx_ticket_checks_event_id` (`event_id`),
    INDEX `idx_ticket_checks_status` (`status`),
    INDEX `idx_ticket_checks_check_type` (`check_type`),
    INDEX `idx_ticket_checks_started_at` (`started_at`),
    INDEX `idx_ticket_checks_uuid` (`uuid`),
    INDEX `idx_ticket_checks_proxy_id` (`proxy_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: purchase_attempts
-- Purpose: Track purchase attempts and results
-- =============================================
CREATE TABLE `purchase_attempts` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `uuid` CHAR(36) NOT NULL UNIQUE,
    `ticket_check_id` BIGINT UNSIGNED NOT NULL,
    `monitoring_criteria_id` BIGINT UNSIGNED NOT NULL,
    `event_id` BIGINT UNSIGNED NOT NULL,
    `ticket_account_id` BIGINT UNSIGNED NOT NULL,
    `proxy_id` BIGINT UNSIGNED NULL,
    `attempt_number` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `status` ENUM('initiated', 'in_progress', 'success', 'failed', 'timeout', 'cancelled', 'payment_failed', 'verification_required') NOT NULL,
    `failure_reason` TEXT NULL,
    `error_code` VARCHAR(50) NULL,
    `ticket_quantity` TINYINT UNSIGNED NOT NULL,
    `ticket_price_each` DECIMAL(10,2) NULL,
    `total_price` DECIMAL(10,2) NULL,
    `fees` DECIMAL(10,2) NULL,
    `currency` CHAR(3) DEFAULT 'GBP',
    `seat_details` JSON NULL COMMENT 'Section, row, seat numbers if available',
    `order_reference` VARCHAR(100) NULL,
    `confirmation_number` VARCHAR(100) NULL,
    `payment_method` ENUM('credit_card', 'debit_card', 'paypal', 'bank_transfer', 'other') NULL,
    `payment_reference` VARCHAR(100) NULL,
    `session_data` JSON NULL COMMENT 'Browser session information',
    `screenshots` JSON NULL COMMENT 'Array of screenshot URLs for debugging',
    `response_time_ms` INT UNSIGNED NULL,
    `user_agent` VARCHAR(500) NULL,
    `ip_address` VARCHAR(45) NULL,
    `started_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `completed_at` TIMESTAMP NULL,
    `expires_at` TIMESTAMP NULL COMMENT 'When the purchase attempt expires',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`ticket_check_id`) REFERENCES `ticket_checks`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`monitoring_criteria_id`) REFERENCES `monitoring_criteria`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`ticket_account_id`) REFERENCES `ticket_accounts`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`proxy_id`) REFERENCES `proxies`(`id`) ON DELETE SET NULL,
    INDEX `idx_purchase_attempts_ticket_check` (`ticket_check_id`),
    INDEX `idx_purchase_attempts_monitoring_criteria` (`monitoring_criteria_id`),
    INDEX `idx_purchase_attempts_event_id` (`event_id`),
    INDEX `idx_purchase_attempts_ticket_account` (`ticket_account_id`),
    INDEX `idx_purchase_attempts_status` (`status`),
    INDEX `idx_purchase_attempts_started_at` (`started_at`),
    INDEX `idx_purchase_attempts_uuid` (`uuid`),
    INDEX `idx_purchase_attempts_order_ref` (`order_reference`),
    INDEX `idx_purchase_attempts_confirmation` (`confirmation_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: notifications
-- Purpose: Queue for user notifications
-- =============================================
CREATE TABLE `notifications` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `uuid` CHAR(36) NOT NULL UNIQUE,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `related_table` ENUM('ticket_checks', 'purchase_attempts', 'events', 'monitoring_criteria', 'ticket_accounts') NULL,
    `related_id` BIGINT UNSIGNED NULL,
    `type` ENUM('ticket_found', 'purchase_success', 'purchase_failed', 'account_locked', 'system_alert', 'info', 'warning', 'error') NOT NULL,
    `channel` ENUM('email', 'sms', 'push', 'webhook', 'in_app') NOT NULL DEFAULT 'email',
    `priority` ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    `title` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `data` JSON NULL COMMENT 'Additional notification data',
    `status` ENUM('pending', 'sent', 'failed', 'cancelled') DEFAULT 'pending',
    `sent_at` TIMESTAMP NULL,
    `delivery_attempts` TINYINT UNSIGNED DEFAULT 0,
    `max_attempts` TINYINT UNSIGNED DEFAULT 3,
    `next_retry_at` TIMESTAMP NULL,
    `error_message` TEXT NULL,
    `recipient_email` VARCHAR(191) NULL,
    `recipient_phone` VARCHAR(20) NULL,
    `webhook_url` VARCHAR(500) NULL,
    `read_at` TIMESTAMP NULL,
    `expires_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_notifications_user_id` (`user_id`),
    INDEX `idx_notifications_status` (`status`),
    INDEX `idx_notifications_type` (`type`),
    INDEX `idx_notifications_channel` (`channel`),
    INDEX `idx_notifications_priority` (`priority`),
    INDEX `idx_notifications_created_at` (`created_at`),
    INDEX `idx_notifications_sent_at` (`sent_at`),
    INDEX `idx_notifications_next_retry` (`next_retry_at`),
    INDEX `idx_notifications_uuid` (`uuid`),
    INDEX `idx_notifications_related` (`related_table`, `related_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: proxies
-- Purpose: Manage proxy rotation for scraping
-- =============================================
CREATE TABLE `proxies` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `uuid` CHAR(36) NOT NULL UNIQUE,
    `name` VARCHAR(100) NOT NULL,
    `type` ENUM('http', 'https', 'socks4', 'socks5') NOT NULL DEFAULT 'http',
    `host` VARCHAR(255) NOT NULL,
    `port` INT UNSIGNED NOT NULL,
    `username` VARCHAR(100) NULL,
    `encrypted_password` TEXT NULL,
    `country_code` CHAR(2) NULL,
    `city` VARCHAR(100) NULL,
    `provider` VARCHAR(100) NULL,
    `is_active` BOOLEAN DEFAULT TRUE,
    `is_rotating` BOOLEAN DEFAULT FALSE COMMENT 'Whether this is a rotating proxy endpoint',
    `max_concurrent_requests` TINYINT UNSIGNED DEFAULT 1,
    `current_requests` TINYINT UNSIGNED DEFAULT 0,
    `total_requests` INT UNSIGNED DEFAULT 0,
    `successful_requests` INT UNSIGNED DEFAULT 0,
    `failed_requests` INT UNSIGNED DEFAULT 0,
    `last_success_at` TIMESTAMP NULL,
    `last_failure_at` TIMESTAMP NULL,
    `last_used_at` TIMESTAMP NULL,
    `response_time_avg_ms` INT UNSIGNED NULL,
    `success_rate` DECIMAL(5,2) NULL COMMENT 'Success rate percentage',
    `cooldown_until` TIMESTAMP NULL COMMENT 'Proxy cooling down until this time',
    `banned_until` TIMESTAMP NULL COMMENT 'Proxy banned until this time',
    `health_status` ENUM('healthy', 'degraded', 'unhealthy', 'unknown') DEFAULT 'unknown',
    `last_health_check` TIMESTAMP NULL,
    `metadata` JSON NULL COMMENT 'Additional proxy information',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_proxies_is_active` (`is_active`),
    INDEX `idx_proxies_health_status` (`health_status`),
    INDEX `idx_proxies_country_code` (`country_code`),
    INDEX `idx_proxies_type` (`type`),
    INDEX `idx_proxies_success_rate` (`success_rate`),
    INDEX `idx_proxies_last_used` (`last_used_at`),
    INDEX `idx_proxies_cooldown` (`cooldown_until`),
    INDEX `idx_proxies_banned` (`banned_until`),
    INDEX `idx_proxies_uuid` (`uuid`),
    UNIQUE KEY `unique_proxy_endpoint` (`host`, `port`, `username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- ADDITIONAL INDEXES FOR PERFORMANCE
-- =============================================

-- Composite indexes for common queries
CREATE INDEX `idx_users_status_created` ON `users` (`status`, `created_at`);
CREATE INDEX `idx_events_date_status` ON `events` (`event_date`, `status`);
CREATE INDEX `idx_ticket_checks_criteria_started` ON `ticket_checks` (`monitoring_criteria_id`, `started_at`);
CREATE INDEX `idx_purchase_attempts_status_started` ON `purchase_attempts` (`status`, `started_at`);
CREATE INDEX `idx_notifications_user_status_priority` ON `notifications` (`user_id`, `status`, `priority`);
CREATE INDEX `idx_proxies_active_health` ON `proxies` (`is_active`, `health_status`);

-- Indexes for cleanup operations
CREATE INDEX `idx_ticket_checks_cleanup` ON `ticket_checks` (`created_at`, `status`);
CREATE INDEX `idx_notifications_cleanup` ON `notifications` (`created_at`, `status`);

-- =============================================
-- VIEWS FOR COMMON QUERIES
-- =============================================

-- View for active monitoring with account details
CREATE VIEW `v_active_monitoring` AS
SELECT 
    mc.id,
    mc.uuid,
    mc.name as criteria_name,
    mc.priority_level,
    mc.next_check_at,
    u.username,
    u.email,
    ta.account_type,
    ta.account_name,
    ta.validation_status
FROM monitoring_criteria mc
JOIN users u ON mc.user_id = u.id
JOIN ticket_accounts ta ON mc.ticket_account_id = ta.id
WHERE mc.is_active = TRUE 
  AND u.status = 'active'
  AND ta.is_active = TRUE;

-- View for recent purchase attempts with details
CREATE VIEW `v_recent_purchases` AS
SELECT 
    pa.id,
    pa.uuid,
    pa.status,
    pa.ticket_quantity,
    pa.total_price,
    pa.currency,
    pa.started_at,
    pa.completed_at,
    e.event_name,
    e.event_date,
    u.username,
    u.email
FROM purchase_attempts pa
JOIN events e ON pa.event_id = e.id
JOIN monitoring_criteria mc ON pa.monitoring_criteria_id = mc.id
JOIN users u ON mc.user_id = u.id
WHERE pa.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
ORDER BY pa.created_at DESC;

-- View for proxy health status
CREATE VIEW `v_proxy_health` AS
SELECT 
    id,
    uuid,
    name,
    host,
    port,
    country_code,
    is_active,
    health_status,
    success_rate,
    total_requests,
    successful_requests,
    failed_requests,
    last_used_at,
    CASE 
        WHEN banned_until > NOW() THEN 'banned'
        WHEN cooldown_until > NOW() THEN 'cooldown'
        WHEN is_active = FALSE THEN 'disabled'
        WHEN health_status = 'healthy' THEN 'available'
        ELSE 'unavailable'
    END as availability_status
FROM proxies;
