/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `account_deletion_audit_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `account_deletion_audit_log` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status_from` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_to` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `context` json DEFAULT NULL,
  `occurred_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `account_deletion_audit_log_user_id_action_index` (`user_id`,`action`),
  KEY `account_deletion_audit_log_action_occurred_at_index` (`action`,`occurred_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `account_deletion_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `account_deletion_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `confirmation_token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `user_data_snapshot` json NOT NULL,
  `initiated_at` timestamp NOT NULL,
  `email_confirmed_at` timestamp NULL DEFAULT NULL,
  `grace_period_expires_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_deletion_requests_confirmation_token_unique` (`confirmation_token`),
  KEY `account_deletion_requests_user_id_status_index` (`user_id`,`status`),
  KEY `account_deletion_requests_status_grace_period_expires_at_index` (`status`,`grace_period_expires_at`),
  KEY `account_deletion_requests_confirmation_token_index` (`confirmation_token`),
  CONSTRAINT `account_deletion_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `activity_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_log` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `log_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `event` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject_id` bigint unsigned DEFAULT NULL,
  `causer_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `causer_id` bigint unsigned DEFAULT NULL,
  `properties` json DEFAULT NULL,
  `batch_uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subject` (`subject_type`,`subject_id`),
  KEY `causer` (`causer_type`,`causer_id`),
  KEY `activity_log_log_name_index` (`log_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `alert_delivery_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `alert_delivery_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `alert_uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `ticket_alert_id` bigint unsigned DEFAULT NULL,
  `channel_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `delivery_status` enum('pending','sent','delivered','failed','bounced') COLLATE utf8mb4_unicode_ci NOT NULL,
  `delivery_details` json DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `acknowledged_at` timestamp NULL DEFAULT NULL,
  `user_interaction` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `alert_delivery_logs_ticket_alert_id_foreign` (`ticket_alert_id`),
  KEY `alert_delivery_logs_alert_uuid_index` (`alert_uuid`),
  KEY `alert_delivery_logs_user_id_delivery_status_index` (`user_id`,`delivery_status`),
  KEY `alert_delivery_logs_channel_type_delivery_status_index` (`channel_type`,`delivery_status`),
  KEY `alert_delivery_logs_sent_at_index` (`sent_at`),
  CONSTRAINT `alert_delivery_logs_ticket_alert_id_foreign` FOREIGN KEY (`ticket_alert_id`) REFERENCES `ticket_alerts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `alert_delivery_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `alert_escalation_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `alert_escalation_rules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `rule_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `trigger_conditions` json NOT NULL,
  `escalation_steps` json NOT NULL,
  `max_escalation_level` int NOT NULL DEFAULT '3',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `times_triggered` int NOT NULL DEFAULT '0',
  `last_triggered_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `alert_escalation_rules_uuid_unique` (`uuid`),
  KEY `alert_escalation_rules_user_id_is_active_index` (`user_id`,`is_active`),
  CONSTRAINT `alert_escalation_rules_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `alert_escalations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `alert_escalations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `alert_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `priority` tinyint NOT NULL DEFAULT '2',
  `strategy` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `scheduled_at` timestamp NOT NULL,
  `attempts` int NOT NULL DEFAULT '0',
  `max_attempts` int NOT NULL DEFAULT '3',
  `status` enum('scheduled','retrying','completed','failed','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'scheduled',
  `alert_data` json NOT NULL,
  `escalation_config` json NOT NULL,
  `last_attempted_at` timestamp NULL DEFAULT NULL,
  `next_retry_at` timestamp NULL DEFAULT NULL,
  `cancellation_reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `alert_escalations_status_scheduled_at_index` (`status`,`scheduled_at`),
  KEY `alert_escalations_user_id_status_index` (`user_id`,`status`),
  KEY `alert_escalations_alert_id_status_index` (`alert_id`,`status`),
  KEY `alert_escalations_next_retry_at_index` (`next_retry_at`),
  CONSTRAINT `alert_escalations_alert_id_foreign` FOREIGN KEY (`alert_id`) REFERENCES `ticket_alerts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `alert_escalations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `alert_event_criteria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `alert_event_criteria` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `alert_id` bigint unsigned NOT NULL,
  `event_id` bigint unsigned DEFAULT NULL,
  `criteria_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `criteria_value` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `weight` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `alert_event_criteria_alert_id_criteria_type_index` (`alert_id`,`criteria_type`),
  KEY `alert_event_criteria_event_id_criteria_type_index` (`event_id`,`criteria_type`),
  CONSTRAINT `alert_event_criteria_alert_id_foreign` FOREIGN KEY (`alert_id`) REFERENCES `ticket_alerts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `alert_event_criteria_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `sports_events` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `alert_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `alert_history` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `alert_id` bigint unsigned NOT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `previous_price` decimal(10,2) DEFAULT NULL,
  `availability_status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `platform_source` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `triggered_alert` tinyint(1) NOT NULL DEFAULT '0',
  `change_details` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'checked',
  `message` text COLLATE utf8mb4_unicode_ci,
  `checked_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `alert_history_alert_id_checked_at_index` (`alert_id`,`checked_at`),
  KEY `alert_history_triggered_alert_checked_at_index` (`triggered_alert`,`checked_at`),
  CONSTRAINT `alert_history_alert_id_foreign` FOREIGN KEY (`alert_id`) REFERENCES `ticket_alerts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `alert_learning_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `alert_learning_data` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `ticket_alert_id` bigint unsigned NOT NULL,
  `user_behavior_patterns` json NOT NULL,
  `optimal_timing_data` json NOT NULL,
  `channel_effectiveness` json NOT NULL,
  `engagement_score` decimal(5,4) NOT NULL DEFAULT '0.5000',
  `prediction_accuracy` json DEFAULT NULL,
  `last_updated_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `alert_learning_data_user_id_ticket_alert_id_unique` (`user_id`,`ticket_alert_id`),
  KEY `alert_learning_data_ticket_alert_id_foreign` (`ticket_alert_id`),
  KEY `alert_learning_data_engagement_score_index` (`engagement_score`),
  KEY `alert_learning_data_last_updated_at_index` (`last_updated_at`),
  CONSTRAINT `alert_learning_data_ticket_alert_id_foreign` FOREIGN KEY (`ticket_alert_id`) REFERENCES `ticket_alerts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `alert_learning_data_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `alert_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `alert_notifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `alert_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `notification_type` enum('email','sms','browser','webhook') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'email',
  `trigger_reason` enum('price_drop','availability_change','alert_expired','manual') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'price_drop',
  `status` enum('pending','sent','delivered','failed','bounced') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `recipient` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` text COLLATE utf8mb4_unicode_ci,
  `metadata` json DEFAULT NULL,
  `error_message` text COLLATE utf8mb4_unicode_ci,
  `sent_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `retry_count` int NOT NULL DEFAULT '0',
  `next_retry_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `alert_notifications_alert_id_notification_type_index` (`alert_id`,`notification_type`),
  KEY `alert_notifications_user_id_status_index` (`user_id`,`status`),
  KEY `alert_notifications_status_next_retry_at_index` (`status`,`next_retry_at`),
  CONSTRAINT `alert_notifications_alert_id_foreign` FOREIGN KEY (`alert_id`) REFERENCES `ticket_alerts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `alert_notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `alert_statistics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `alert_statistics` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `alert_id` bigint unsigned NOT NULL,
  `date` date NOT NULL,
  `checks_count` int NOT NULL DEFAULT '0',
  `triggers_count` int NOT NULL DEFAULT '0',
  `min_price` decimal(10,2) DEFAULT NULL,
  `max_price` decimal(10,2) DEFAULT NULL,
  `avg_price` decimal(10,2) DEFAULT NULL,
  `price_variance` decimal(10,2) DEFAULT NULL,
  `availability_changes` json DEFAULT NULL,
  `notifications_sent` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `alert_statistics_alert_id_date_unique` (`alert_id`,`date`),
  KEY `alert_statistics_date_index` (`date`),
  CONSTRAINT `alert_statistics_alert_id_foreign` FOREIGN KEY (`alert_id`) REFERENCES `ticket_alerts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `alert_triggers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `alert_triggers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ticket_alert_id` bigint unsigned NOT NULL,
  `scraped_ticket_id` bigint unsigned DEFAULT NULL,
  `triggered_at` timestamp NOT NULL DEFAULT '2025-09-14 04:57:15',
  `match_score` decimal(5,2) NOT NULL DEFAULT '0.00',
  `trigger_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notification_sent` tinyint(1) NOT NULL DEFAULT '0',
  `user_acknowledged` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `alert_triggers_scraped_ticket_id_foreign` (`scraped_ticket_id`),
  KEY `alert_triggers_ticket_alert_id_triggered_at_index` (`ticket_alert_id`,`triggered_at`),
  KEY `alert_triggers_triggered_at_index` (`triggered_at`),
  KEY `alert_triggers_notification_sent_index` (`notification_sent`),
  CONSTRAINT `alert_triggers_scraped_ticket_id_foreign` FOREIGN KEY (`scraped_ticket_id`) REFERENCES `scraped_tickets` (`id`) ON DELETE SET NULL,
  CONSTRAINT `alert_triggers_ticket_alert_id_foreign` FOREIGN KEY (`ticket_alert_id`) REFERENCES `ticket_alerts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `analytics_ab_tests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `analytics_ab_tests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `test_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `feature_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `variant_configurations` json NOT NULL,
  `traffic_split` decimal(5,4) NOT NULL DEFAULT '0.5000',
  `started_at` timestamp NOT NULL,
  `ends_at` timestamp NULL DEFAULT NULL,
  `status` enum('draft','running','paused','completed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL,
  `success_metrics` json NOT NULL,
  `current_results` json DEFAULT NULL,
  `auto_promote_winner` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `analytics_ab_tests_feature_name_status_index` (`feature_name`,`status`),
  KEY `analytics_ab_tests_started_at_ends_at_index` (`started_at`,`ends_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `analytics_cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `analytics_cache` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cache_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `analytics_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `data` json NOT NULL,
  `filters_applied` json DEFAULT NULL,
  `generated_at` timestamp NOT NULL,
  `expires_at` timestamp NOT NULL,
  `is_real_time` tinyint(1) NOT NULL DEFAULT '0',
  `access_count` int NOT NULL DEFAULT '0',
  `last_accessed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `analytics_cache_cache_key_unique` (`cache_key`),
  KEY `analytics_cache_analytics_type_expires_at_index` (`analytics_type`,`expires_at`),
  KEY `analytics_cache_cache_key_index` (`cache_key`),
  KEY `analytics_cache_generated_at_index` (`generated_at`),
  KEY `analytics_cache_is_real_time_index` (`is_real_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `analytics_dashboards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `analytics_dashboards` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `configuration` json NOT NULL,
  `widgets` json NOT NULL,
  `filters` json NOT NULL,
  `refresh_interval` int NOT NULL DEFAULT '300',
  `is_public` tinyint(1) NOT NULL DEFAULT '0',
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `shared_with` json DEFAULT NULL,
  `last_accessed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `analytics_dashboards_user_id_is_default_index` (`user_id`,`is_default`),
  KEY `analytics_dashboards_is_public_index` (`is_public`),
  KEY `analytics_dashboards_last_accessed_at_index` (`last_accessed_at`),
  CONSTRAINT `analytics_dashboards_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `analytics_insights`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `analytics_insights` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `insight_uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `insight_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_points` json NOT NULL,
  `priority` enum('low','medium','high','critical') COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('active','acknowledged','resolved','dismissed') COLLATE utf8mb4_unicode_ci NOT NULL,
  `confidence_score` decimal(5,4) DEFAULT NULL,
  `recommended_actions` json DEFAULT NULL,
  `valid_until` timestamp NULL DEFAULT NULL,
  `generated_by_user_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `analytics_insights_insight_uuid_unique` (`insight_uuid`),
  KEY `analytics_insights_generated_by_user_id_foreign` (`generated_by_user_id`),
  KEY `analytics_insights_insight_type_status_index` (`insight_type`,`status`),
  KEY `analytics_insights_category_priority_index` (`category`,`priority`),
  KEY `analytics_insights_valid_until_index` (`valid_until`),
  KEY `analytics_insights_confidence_score_index` (`confidence_score`),
  CONSTRAINT `analytics_insights_generated_by_user_id_foreign` FOREIGN KEY (`generated_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `analytics_user_interactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `analytics_user_interactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `interaction_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dashboard_section` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `interaction_details` json NOT NULL,
  `interaction_time` timestamp NOT NULL,
  `session_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_context` json DEFAULT NULL,
  `time_spent_seconds` decimal(8,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `analytics_user_interactions_user_id_interaction_time_index` (`user_id`,`interaction_time`),
  KEY `analytics_user_interactions_interaction_type_index` (`interaction_type`),
  KEY `analytics_user_interactions_dashboard_section_index` (`dashboard_section`),
  KEY `analytics_user_interactions_session_id_index` (`session_id`),
  CONSTRAINT `analytics_user_interactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `audit_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `auditable_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `auditable_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `event_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `action_description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `changed_fields` json DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `session_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `request_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `route_name` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `http_method` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `request_url` text COLLATE utf8mb4_unicode_ci,
  `request_data` json DEFAULT NULL,
  `response_data` json DEFAULT NULL,
  `response_status` int DEFAULT NULL,
  `severity` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'info',
  `tags` json DEFAULT NULL,
  `context` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_auditable_time` (`auditable_type`,`auditable_id`,`created_at`),
  KEY `idx_user_audit_time` (`user_id`,`created_at`),
  KEY `idx_event_audit_time` (`event_type`,`created_at`),
  KEY `idx_severity_time` (`severity`,`created_at`),
  KEY `idx_session_audit` (`session_id`,`created_at`),
  KEY `idx_request_audit` (`request_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `automation_parameter_adjustments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `automation_parameter_adjustments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `parameter_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `old_value` json NOT NULL,
  `new_value` json NOT NULL,
  `adjustment_reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `performance_impact` decimal(5,4) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `automation_parameter_adjustments_parameter_name_created_at_index` (`parameter_name`,`created_at`),
  KEY `automation_parameter_adjustments_is_active_index` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `automation_tracking`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `automation_tracking` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `transaction_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `platform` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `success` tinyint(1) NOT NULL,
  `execution_time` int NOT NULL DEFAULT '0',
  `price_difference` decimal(10,2) NOT NULL DEFAULT '0.00',
  `user_id` bigint unsigned NOT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `automation_tracking_transaction_id_unique` (`transaction_id`),
  KEY `automation_tracking_platform_success_created_at_index` (`platform`,`success`,`created_at`),
  KEY `automation_tracking_user_id_created_at_index` (`user_id`,`created_at`),
  CONSTRAINT `automation_tracking_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_entries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cache_key` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cache_group` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'default',
  `cache_value` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `value_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'serialized',
  `ttl_seconds` int DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_accessed_at` timestamp NULL DEFAULT NULL,
  `access_count` int NOT NULL DEFAULT '0',
  `hit_count` int NOT NULL DEFAULT '0',
  `miss_count` int NOT NULL DEFAULT '0',
  `hit_rate` decimal(5,2) GENERATED ALWAYS AS (((`hit_count` / greatest((`hit_count` + `miss_count`),1)) * 100)) STORED,
  `size_bytes` bigint DEFAULT NULL,
  `tags` json DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `is_compressed` tinyint(1) NOT NULL DEFAULT '0',
  `compression_type` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cache_entries_cache_key_unique` (`cache_key`),
  KEY `idx_group_expiry` (`cache_group`,`expires_at`),
  KEY `idx_expiry_cleanup` (`expires_at`),
  KEY `idx_group_access` (`cache_group`,`last_accessed_at`),
  KEY `idx_hit_rate_group` (`hit_rate`,`cache_group`),
  KEY `idx_access_count_group` (`access_count`,`cache_group`),
  KEY `idx_size_analysis` (`size_bytes`),
  KEY `idx_created_group` (`created_at`,`cache_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`hdtickets`@`localhost`*/ /*!50003 TRIGGER `update_cache_stats` BEFORE UPDATE ON `cache_entries` FOR EACH ROW SET 
                    NEW.last_accessed_at = NOW(),
                    NEW.access_count = OLD.access_count + 1,
                    NEW.hit_count = OLD.hit_count + 1 */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `color` varchar(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#007bff',
  `icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int NOT NULL DEFAULT '0',
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `parent_id` bigint unsigned DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_slug_unique` (`slug`),
  KEY `categories_uuid_index` (`uuid`),
  KEY `categories_parent_id_index` (`parent_id`),
  KEY `categories_sort_order_index` (`sort_order`),
  KEY `categories_is_active_index` (`is_active`),
  CONSTRAINT `categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `custom_analytics_queries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `custom_analytics_queries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `query_uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `query_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `query_configuration` json NOT NULL,
  `visualization_config` json NOT NULL,
  `execution_frequency` enum('manual','hourly','daily','weekly','monthly') COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_executed_at` timestamp NULL DEFAULT NULL,
  `next_execution_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_shared` tinyint(1) NOT NULL DEFAULT '0',
  `execution_count` int NOT NULL DEFAULT '0',
  `performance_stats` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `custom_analytics_queries_query_uuid_unique` (`query_uuid`),
  KEY `custom_analytics_queries_user_id_is_active_index` (`user_id`,`is_active`),
  KEY `custom_queries_execution_idx` (`execution_frequency`,`next_execution_at`),
  KEY `custom_analytics_queries_is_shared_index` (`is_shared`),
  CONSTRAINT `custom_analytics_queries_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `data_export_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_export_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `export_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'full',
  `data_types` json NOT NULL,
  `format` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'json',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_size` int DEFAULT NULL,
  `expires_at` timestamp NOT NULL,
  `error_message` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `data_export_requests_user_id_status_index` (`user_id`,`status`),
  KEY `data_export_requests_status_expires_at_index` (`status`,`expires_at`),
  CONSTRAINT `data_export_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `data_validation_results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_validation_results` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `migration_execution_id` bigint unsigned DEFAULT NULL,
  `validation_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `validation_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `table_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `validation_query` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `expected_count` bigint DEFAULT NULL,
  `actual_count` bigint DEFAULT NULL,
  `validation_status` enum('passed','failed','warning','skipped') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'passed',
  `validation_message` text COLLATE utf8mb4_unicode_ci,
  `validation_details` json DEFAULT NULL,
  `execution_time_ms` decimal(10,2) NOT NULL,
  `validated_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `data_validation_results_migration_execution_id_foreign` (`migration_execution_id`),
  KEY `data_validation_results_validation_status_validated_at_index` (`validation_status`,`validated_at`),
  KEY `data_validation_results_table_name_validation_type_index` (`table_name`,`validation_type`),
  KEY `data_validation_results_validated_at_index` (`validated_at`),
  CONSTRAINT `data_validation_results_migration_execution_id_foreign` FOREIGN KEY (`migration_execution_id`) REFERENCES `migration_executions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `data_validation_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_validation_rules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `rule_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rule_category` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_table` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `validation_sql` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `severity` enum('critical','high','medium','low') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'high',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `run_pre_migration` tinyint(1) NOT NULL DEFAULT '1',
  `run_post_migration` tinyint(1) NOT NULL DEFAULT '1',
  `timeout_seconds` int NOT NULL DEFAULT '300',
  `expected_result` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `data_validation_rules_rule_name_unique` (`rule_name`),
  KEY `data_validation_rules_rule_category_is_active_index` (`rule_category`,`is_active`),
  KEY `data_validation_rules_target_table_is_active_index` (`target_table`,`is_active`),
  KEY `data_validation_rules_severity_index` (`severity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `database_connections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `database_connections` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `connection_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection_type` enum('master','read_replica','analytics') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'master',
  `host` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `port` int NOT NULL DEFAULT '3306',
  `database` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_encrypted` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection_options` json DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `weight` int NOT NULL DEFAULT '1',
  `max_connections` int NOT NULL DEFAULT '100',
  `current_connections` int NOT NULL DEFAULT '0',
  `lag_threshold_seconds` decimal(5,2) NOT NULL DEFAULT '1.00',
  `current_lag_seconds` decimal(5,2) DEFAULT NULL,
  `last_health_check` timestamp NULL DEFAULT NULL,
  `health_status` enum('healthy','warning','critical','offline') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'healthy',
  `performance_metrics` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `database_connections_connection_name_unique` (`connection_name`),
  KEY `database_connections_connection_type_is_active_weight_index` (`connection_type`,`is_active`,`weight`),
  KEY `database_connections_health_status_is_active_index` (`health_status`,`is_active`),
  KEY `database_connections_last_health_check_index` (`last_health_check`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `deleted_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `deleted_users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `original_user_id` bigint unsigned NOT NULL,
  `user_data` json NOT NULL,
  `related_data` json NOT NULL,
  `deletion_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NOT NULL,
  `recoverable_until` timestamp NOT NULL,
  `is_recovered` tinyint(1) NOT NULL DEFAULT '0',
  `recovered_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `deleted_users_original_user_id_index` (`original_user_id`),
  KEY `deleted_users_deleted_at_recoverable_until_index` (`deleted_at`,`recoverable_until`),
  KEY `deleted_users_is_recovered_index` (`is_recovered`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `domain_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `domain_events` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `event_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `aggregate_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `aggregate_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `aggregate_version` bigint unsigned NOT NULL,
  `event_type` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `event_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `event_data` json NOT NULL,
  `event_metadata` json DEFAULT NULL,
  `caused_by_user_id` bigint unsigned DEFAULT NULL,
  `correlation_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `causation_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `occurred_at` timestamp NOT NULL,
  `recorded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_processed` tinyint(1) NOT NULL DEFAULT '0',
  `processed_at` timestamp NULL DEFAULT NULL,
  `processing_error` text COLLATE utf8mb4_unicode_ci,
  `processing_attempts` tinyint NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_aggregate_version` (`aggregate_type`,`aggregate_id`,`aggregate_version`),
  UNIQUE KEY `domain_events_event_id_unique` (`event_id`),
  KEY `idx_aggregate_stream` (`aggregate_type`,`aggregate_id`,`aggregate_version`),
  KEY `idx_event_type_time` (`event_type`,`occurred_at`),
  KEY `idx_correlation_time` (`correlation_id`,`occurred_at`),
  KEY `idx_causation` (`causation_id`),
  KEY `idx_processing_queue` (`is_processed`,`occurred_at`),
  KEY `idx_user_events` (`caused_by_user_id`,`occurred_at`),
  KEY `domain_events_recorded_at_index` (`recorded_at`),
  KEY `domain_events_occurred_at_index` (`occurred_at`),
  CONSTRAINT `domain_events_caused_by_user_id_foreign` FOREIGN KEY (`caused_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `event_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `event_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `event_id` bigint unsigned NOT NULL,
  `category_id` bigint unsigned NOT NULL,
  `relevance_score` tinyint NOT NULL DEFAULT '100',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `event_categories_event_id_category_id_unique` (`event_id`,`category_id`),
  KEY `event_categories_event_id_relevance_score_index` (`event_id`,`relevance_score`),
  KEY `event_categories_category_id_relevance_score_index` (`category_id`,`relevance_score`),
  CONSTRAINT `event_categories_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `event_categories_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `sports_events` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `event_metadata`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `event_metadata` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `event_id` bigint unsigned NOT NULL,
  `key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('string','integer','decimal','boolean','json') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'string',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `event_metadata_event_id_key_unique` (`event_id`,`key`),
  KEY `event_metadata_event_id_key_index` (`event_id`,`key`),
  KEY `event_metadata_key_index` (`key`),
  CONSTRAINT `event_metadata_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `sports_events` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `event_processing_failures`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `event_processing_failures` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `event_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subscription_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `handler_class` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `error_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `error_message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `error_context` json DEFAULT NULL,
  `event_payload` json NOT NULL,
  `retry_count` int NOT NULL DEFAULT '0',
  `failed_at` timestamp NOT NULL,
  `retry_after` timestamp NULL DEFAULT NULL,
  `is_resolved` tinyint(1) NOT NULL DEFAULT '0',
  `resolved_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `event_processing_failures_subscription_name_failed_at_index` (`subscription_name`,`failed_at`),
  KEY `event_processing_failures_is_resolved_retry_after_index` (`is_resolved`,`retry_after`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `event_projections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `event_projections` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `projection_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_processed_event_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position` int NOT NULL DEFAULT '0',
  `state` json DEFAULT NULL,
  `last_updated_at` timestamp NULL DEFAULT NULL,
  `is_locked` tinyint(1) NOT NULL DEFAULT '0',
  `locked_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `locked_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `event_projections_projection_name_unique` (`projection_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `event_snapshots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `event_snapshots` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `aggregate_root_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `aggregate_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `aggregate_version` int NOT NULL,
  `aggregate_data` json NOT NULL,
  `created_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `snapshots_unique_idx` (`aggregate_root_id`,`aggregate_type`,`aggregate_version`),
  KEY `event_snapshots_aggregate_root_id_index` (`aggregate_root_id`),
  KEY `event_snapshots_aggregate_type_index` (`aggregate_type`),
  KEY `event_snapshots_aggregate_version_index` (`aggregate_version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `event_store`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `event_store` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `event_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `event_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `aggregate_root_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `aggregate_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `aggregate_version` int NOT NULL,
  `payload` json NOT NULL,
  `metadata` json DEFAULT NULL,
  `recorded_at` timestamp NOT NULL,
  `event_version` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1.0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `event_store_event_id_unique` (`event_id`),
  KEY `event_store_aggregate_root_id_aggregate_version_index` (`aggregate_root_id`,`aggregate_version`),
  KEY `event_store_event_type_recorded_at_index` (`event_type`,`recorded_at`),
  KEY `event_store_aggregate_type_recorded_at_index` (`aggregate_type`,`recorded_at`),
  KEY `event_store_event_type_index` (`event_type`),
  KEY `event_store_aggregate_root_id_index` (`aggregate_root_id`),
  KEY `event_store_aggregate_type_index` (`aggregate_type`),
  KEY `event_store_aggregate_version_index` (`aggregate_version`),
  KEY `event_store_recorded_at_index` (`recorded_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `event_streams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `event_streams` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `stream_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stream_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NOT NULL,
  `last_event_at` timestamp NULL DEFAULT NULL,
  `version` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `event_streams_stream_name_unique` (`stream_name`),
  KEY `event_streams_stream_type_index` (`stream_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `event_subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `event_subscriptions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `subscription_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `handler_class` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `event_types` json NOT NULL,
  `last_processed_event_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_catch_up` tinyint(1) NOT NULL DEFAULT '0',
  `configuration` json DEFAULT NULL,
  `created_at` timestamp NOT NULL,
  `last_processed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `event_subscriptions_subscription_name_unique` (`subscription_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `in_app_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `in_app_notifications` (
  `id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `data` json DEFAULT NULL,
  `priority` tinyint NOT NULL DEFAULT '2',
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `is_dismissed` tinyint(1) NOT NULL DEFAULT '0',
  `read_at` timestamp NULL DEFAULT NULL,
  `dismissed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL,
  `expires_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  KEY `in_app_notifications_user_id_is_read_created_at_index` (`user_id`,`is_read`,`created_at`),
  KEY `in_app_notifications_user_id_type_created_at_index` (`user_id`,`type`,`created_at`),
  KEY `in_app_notifications_user_id_priority_created_at_index` (`user_id`,`priority`,`created_at`),
  KEY `in_app_notifications_expires_at_is_dismissed_index` (`expires_at`,`is_dismissed`),
  KEY `in_app_notifications_type_index` (`type`),
  KEY `in_app_notifications_priority_index` (`priority`),
  KEY `in_app_notifications_is_read_index` (`is_read`),
  KEY `in_app_notifications_is_dismissed_index` (`is_dismissed`),
  KEY `in_app_notifications_expires_at_index` (`expires_at`),
  CONSTRAINT `in_app_notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `job_failures`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_failures` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `failure_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `job_class` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `job_method` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `queue_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `job_payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception_message` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception_trace` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception_class` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception_file` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `exception_line` int DEFAULT NULL,
  `job_data` json DEFAULT NULL,
  `context_data` json DEFAULT NULL,
  `attempt_number` int NOT NULL DEFAULT '1',
  `max_attempts` int NOT NULL DEFAULT '3',
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `next_retry_at` timestamp NULL DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `resolution_status` enum('pending','retried','resolved','discarded') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `resolution_notes` text COLLATE utf8mb4_unicode_ci,
  `resolved_by_user_id` bigint unsigned DEFAULT NULL,
  `severity` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'error',
  `is_business_critical` tinyint(1) NOT NULL DEFAULT '0',
  `tags` json DEFAULT NULL,
  `execution_time_ms` decimal(10,2) DEFAULT NULL,
  `memory_usage_mb` int DEFAULT NULL,
  `system_metrics` json DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `job_failures_failure_id_unique` (`failure_id`),
  KEY `idx_job_class_time` (`job_class`,`failed_at`),
  KEY `idx_queue_time` (`queue_name`,`failed_at`),
  KEY `idx_resolution_time` (`resolution_status`,`failed_at`),
  KEY `idx_severity_time` (`severity`,`failed_at`),
  KEY `idx_critical_time` (`is_business_critical`,`failed_at`),
  KEY `idx_retry_queue` (`next_retry_at`),
  KEY `idx_exception_time` (`exception_class`,`failed_at`),
  KEY `idx_retry_attempts` (`attempt_number`,`max_attempts`),
  KEY `job_failures_failed_at_index` (`failed_at`),
  KEY `job_failures_resolved_by_user_id_foreign` (`resolved_by_user_id`),
  CONSTRAINT `job_failures_resolved_by_user_id_foreign` FOREIGN KEY (`resolved_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `legal_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `legal_documents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `version` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1.0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `requires_acceptance` tinyint(1) NOT NULL DEFAULT '0',
  `effective_date` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `legal_documents_type_active_unique` (`type`,`is_active`),
  UNIQUE KEY `legal_documents_slug_unique` (`slug`),
  KEY `legal_documents_type_index` (`type`),
  KEY `legal_documents_is_active_index` (`is_active`),
  KEY `legal_documents_effective_date_index` (`effective_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `login_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `login_history` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `device_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `browser` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `operating_system` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `success` tinyint(1) NOT NULL DEFAULT '0',
  `failure_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_suspicious` tinyint(1) NOT NULL DEFAULT '0',
  `suspicious_flags` text COLLATE utf8mb4_unicode_ci,
  `session_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attempted_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `login_history_user_id_attempted_at_index` (`user_id`,`attempted_at`),
  KEY `login_history_user_id_success_index` (`user_id`,`success`),
  KEY `login_history_user_id_is_suspicious_index` (`user_id`,`is_suspicious`),
  KEY `login_history_ip_address_attempted_at_index` (`ip_address`,`attempted_at`),
  KEY `login_history_session_id_index` (`session_id`),
  CONSTRAINT `login_history_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `market_data_snapshots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `market_data_snapshots` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `snapshot_time` timestamp NOT NULL,
  `platform_metrics` json NOT NULL,
  `category_metrics` json NOT NULL,
  `demand_indicators` json NOT NULL,
  `price_indicators` json NOT NULL,
  `user_activity_metrics` json NOT NULL,
  `market_health_score` decimal(5,2) NOT NULL DEFAULT '100.00',
  `anomalies_detected` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `market_data_snapshots_snapshot_time_index` (`snapshot_time`),
  KEY `market_data_snapshots_market_health_score_index` (`market_health_score`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migration_executions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migration_executions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `migration_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `migration_batch` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `execution_type` enum('up','down','rollback') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'up',
  `execution_status` enum('started','in_progress','completed','failed','rolled_back') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'started',
  `execution_plan` text COLLATE utf8mb4_unicode_ci,
  `started_at` timestamp NOT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `execution_time_seconds` decimal(10,3) DEFAULT NULL,
  `error_message` text COLLATE utf8mb4_unicode_ci,
  `rollback_sql` text COLLATE utf8mb4_unicode_ci,
  `affected_tables` json DEFAULT NULL,
  `affected_rows` bigint NOT NULL DEFAULT '0',
  `performance_metrics` json DEFAULT NULL,
  `executed_by_user_id` bigint unsigned DEFAULT NULL,
  `environment` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'production',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `migration_executions_migration_name_execution_status_index` (`migration_name`,`execution_status`),
  KEY `migration_executions_execution_status_started_at_index` (`execution_status`,`started_at`),
  KEY `migration_executions_migration_batch_execution_status_index` (`migration_batch`,`execution_status`),
  KEY `migration_executions_executed_by_user_id_foreign` (`executed_by_user_id`),
  CONSTRAINT `migration_executions_executed_by_user_id_foreign` FOREIGN KEY (`executed_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ml_model_performance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ml_model_performance` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `model_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `version` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `accuracy_score` decimal(5,4) NOT NULL DEFAULT '0.0000',
  `performance_metrics` json NOT NULL,
  `evaluated_at` timestamp NOT NULL,
  `training_data_stats` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ml_model_performance_model_name_version_index` (`model_name`,`version`),
  KEY `ml_model_performance_evaluated_at_index` (`evaluated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `monitoring_platforms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `monitoring_platforms` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `identifier` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `check_interval_minutes` int NOT NULL DEFAULT '15',
  `rate_limit_per_minute` int NOT NULL DEFAULT '10',
  `supported_sports` json DEFAULT NULL,
  `supported_regions` json DEFAULT NULL,
  `reliability_score` decimal(5,2) DEFAULT NULL,
  `last_successful_check` timestamp NULL DEFAULT NULL,
  `last_failed_check` timestamp NULL DEFAULT NULL,
  `consecutive_failures` int NOT NULL DEFAULT '0',
  `failure_reason` text COLLATE utf8mb4_unicode_ci,
  `configuration` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `monitoring_platforms_name_unique` (`name`),
  UNIQUE KEY `monitoring_platforms_identifier_unique` (`identifier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `monitoring_read_models`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `monitoring_read_models` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `monitor_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `platform` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `criteria` json NOT NULL,
  `matches_found` int NOT NULL DEFAULT '0',
  `started_at` timestamp NOT NULL,
  `stopped_at` timestamp NULL DEFAULT NULL,
  `last_match_at` timestamp NULL DEFAULT NULL,
  `performance_metrics` json NOT NULL,
  `version` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `monitoring_read_models_monitor_id_unique` (`monitor_id`),
  KEY `monitoring_read_models_user_id_status_index` (`user_id`,`status`),
  KEY `monitoring_read_models_platform_status_index` (`platform`,`status`),
  KEY `monitoring_read_models_user_id_index` (`user_id`),
  KEY `monitoring_read_models_platform_index` (`platform`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `monitoring_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `monitoring_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'string',
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_public` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `monitoring_settings_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mv_daily_platform_stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mv_daily_platform_stats` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `stats_date` date NOT NULL,
  `platform` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_tickets_scraped` int NOT NULL DEFAULT '0',
  `available_tickets` int NOT NULL DEFAULT '0',
  `successful_scrapes` int NOT NULL DEFAULT '0',
  `failed_scrapes` int NOT NULL DEFAULT '0',
  `success_rate` decimal(5,2) NOT NULL DEFAULT '0.00',
  `avg_response_time_ms` decimal(10,2) NOT NULL DEFAULT '0.00',
  `avg_min_price` decimal(10,2) DEFAULT NULL,
  `avg_max_price` decimal(10,2) DEFAULT NULL,
  `unique_events` int NOT NULL DEFAULT '0',
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mv_daily_platform_stats_stats_date_platform_unique` (`stats_date`,`platform`),
  KEY `mv_daily_platform_stats_stats_date_platform_index` (`stats_date`,`platform`),
  KEY `mv_daily_platform_stats_stats_date_index` (`stats_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mv_monthly_revenue_analytics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mv_monthly_revenue_analytics` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `month_start` date NOT NULL,
  `platform` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sport` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_purchases` int NOT NULL DEFAULT '0',
  `total_revenue` decimal(15,2) NOT NULL DEFAULT '0.00',
  `avg_ticket_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_fees` decimal(12,2) NOT NULL DEFAULT '0.00',
  `unique_buyers` int NOT NULL DEFAULT '0',
  `tickets_sold` int NOT NULL DEFAULT '0',
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mv_monthly_revenue_analytics_month_start_platform_sport_unique` (`month_start`,`platform`,`sport`),
  KEY `mv_monthly_revenue_analytics_month_start_platform_index` (`month_start`,`platform`),
  KEY `mv_monthly_revenue_analytics_month_start_index` (`month_start`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mv_weekly_user_activity`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mv_weekly_user_activity` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `week_start` date NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `alerts_created` int NOT NULL DEFAULT '0',
  `alerts_triggered` int NOT NULL DEFAULT '0',
  `purchase_attempts` int NOT NULL DEFAULT '0',
  `successful_purchases` int NOT NULL DEFAULT '0',
  `total_spent` decimal(12,2) NOT NULL DEFAULT '0.00',
  `tickets_viewed` int NOT NULL DEFAULT '0',
  `login_count` int NOT NULL DEFAULT '0',
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mv_weekly_user_activity_week_start_user_id_unique` (`week_start`,`user_id`),
  KEY `mv_weekly_user_activity_user_id_foreign` (`user_id`),
  KEY `mv_weekly_user_activity_week_start_user_id_index` (`week_start`,`user_id`),
  CONSTRAINT `mv_weekly_user_activity_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint unsigned NOT NULL,
  `data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `oauth_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `oauth_access_tokens` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `client_id` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scopes` text COLLATE utf8mb4_unicode_ci,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `oauth_access_tokens_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `oauth_auth_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `oauth_auth_codes` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `client_id` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `scopes` text COLLATE utf8mb4_unicode_ci,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `oauth_auth_codes_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `oauth_clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `oauth_clients` (
  `id` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `secret` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `redirect` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `personal_access_client` tinyint(1) NOT NULL,
  `password_client` tinyint(1) NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `oauth_clients_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `oauth_personal_access_clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `oauth_personal_access_clients` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `client_id` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `oauth_refresh_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `oauth_refresh_tokens` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `access_token_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `oauth_refresh_tokens_access_token_id_index` (`access_token_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `payment_plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payment_plans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(10,2) NOT NULL,
  `billing_cycle` enum('monthly','yearly','lifetime') COLLATE utf8mb4_unicode_ci NOT NULL,
  `features` json NOT NULL,
  `max_tickets_per_month` int NOT NULL DEFAULT '0',
  `max_concurrent_purchases` int NOT NULL DEFAULT '1',
  `max_platforms` int NOT NULL DEFAULT '1',
  `priority_support` tinyint(1) NOT NULL DEFAULT '0',
  `advanced_analytics` tinyint(1) NOT NULL DEFAULT '0',
  `automated_purchasing` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int NOT NULL DEFAULT '0',
  `stripe_price_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_plans_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `category` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_system_permission` tinyint(1) NOT NULL DEFAULT '0',
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_unique` (`name`),
  KEY `permissions_name_index` (`name`),
  KEY `permissions_category_index` (`category`),
  KEY `permissions_is_system_permission_index` (`is_system_permission`),
  KEY `permissions_created_by_foreign` (`created_by`),
  CONSTRAINT `permissions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `platform_cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `platform_cache` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `platform` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `cache_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `cache_data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_cache_platform_key` (`platform`,`cache_key`),
  KEY `idx_cache_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `platform_config_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `platform_config_details` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `config_id` bigint unsigned NOT NULL,
  `detail_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `detail_value` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `value_type` enum('string','integer','decimal','boolean','array') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'string',
  `is_encrypted` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `platform_config_details_config_id_detail_key_unique` (`config_id`,`detail_key`),
  KEY `platform_config_details_config_id_detail_key_index` (`config_id`,`detail_key`),
  CONSTRAINT `platform_config_details_config_id_foreign` FOREIGN KEY (`config_id`) REFERENCES `platform_configurations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `platform_configurations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `platform_configurations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `platform` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `config_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `config_value` json NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `platform_configurations_platform_config_key_unique` (`platform`,`config_key`),
  KEY `platform_configurations_platform_index` (`platform`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `popular_tickets_view`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `popular_tickets_view` (
  `id` bigint unsigned NOT NULL DEFAULT '0',
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `platform` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `external_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `venue` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(2) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'US',
  `timezone` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'UTC',
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `event_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sports',
  `sport` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'football',
  `team` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `event_date` datetime DEFAULT NULL,
  `min_price` decimal(8,2) DEFAULT NULL,
  `max_price` decimal(8,2) DEFAULT NULL,
  `previous_min_price` decimal(8,2) DEFAULT NULL,
  `previous_max_price` decimal(8,2) DEFAULT NULL,
  `last_price_change` timestamp NULL DEFAULT NULL,
  `price_change_percentage` decimal(5,2) NOT NULL DEFAULT '0.00',
  `currency` varchar(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'USD',
  `availability` int NOT NULL DEFAULT '0',
  `is_available` tinyint(1) NOT NULL DEFAULT '1',
  `last_available_at` timestamp NULL DEFAULT NULL,
  `availability_changes` int unsigned NOT NULL DEFAULT '0',
  `is_high_demand` tinyint(1) NOT NULL DEFAULT '0',
  `status` enum('active','sold_out','expired','cancelled','pending_verification','invalid') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `ticket_url` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `search_keyword` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `tags` json DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scraped_at` timestamp NOT NULL,
  `last_scraped_successfully` timestamp NULL DEFAULT NULL,
  `scraping_quality_score` tinyint unsigned NOT NULL DEFAULT '100',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `home_team_id` bigint unsigned DEFAULT NULL,
  `away_team_id` bigint unsigned DEFAULT NULL,
  `venue_id` bigint unsigned DEFAULT NULL,
  `league_id` bigint unsigned DEFAULT NULL,
  `competition_round` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `weather_conditions` json DEFAULT NULL,
  `predicted_demand` decimal(5,2) DEFAULT NULL,
  `view_count` int unsigned NOT NULL DEFAULT '0',
  `bookmark_count` int unsigned NOT NULL DEFAULT '0',
  `share_count` int unsigned NOT NULL DEFAULT '0',
  `popularity_score` decimal(5,2) NOT NULL DEFAULT '0.00',
  `category_id` bigint unsigned DEFAULT NULL,
  `total_views` bigint NOT NULL DEFAULT '0',
  `total_bookmarks` bigint NOT NULL DEFAULT '0',
  `calculated_popularity` decimal(13,0) NOT NULL DEFAULT '0',
  KEY `idx_popular_tickets_popularity` (`calculated_popularity` DESC,`event_date`),
  KEY `idx_popular_tickets_sport` (`sport`,`calculated_popularity` DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `prediction_model_metrics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `prediction_model_metrics` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `model_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_version` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prediction_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `performance_metrics` json NOT NULL,
  `training_data_info` json NOT NULL,
  `last_trained_at` timestamp NOT NULL,
  `next_training_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `predictions_made` int NOT NULL DEFAULT '0',
  `average_accuracy` decimal(5,4) DEFAULT NULL,
  `feature_importance` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `prediction_model_metrics_model_name_model_version_unique` (`model_name`,`model_version`),
  KEY `prediction_model_metrics_prediction_type_is_active_index` (`prediction_type`,`is_active`),
  KEY `prediction_model_metrics_average_accuracy_index` (`average_accuracy`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `price_alert_thresholds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `price_alert_thresholds` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `ticket_id` bigint unsigned NOT NULL,
  `target_price` decimal(10,2) NOT NULL,
  `alert_type` enum('below','above','percentage_change') COLLATE utf8mb4_unicode_ci NOT NULL,
  `percentage_threshold` decimal(5,2) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `last_triggered_at` timestamp NULL DEFAULT NULL,
  `trigger_count` int NOT NULL DEFAULT '0',
  `notification_channels` json NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `price_alert_thresholds_uuid_unique` (`uuid`),
  KEY `price_alert_thresholds_user_id_is_active_index` (`user_id`,`is_active`),
  KEY `price_alert_thresholds_ticket_id_is_active_index` (`ticket_id`,`is_active`),
  KEY `price_alert_thresholds_target_price_index` (`target_price`),
  CONSTRAINT `price_alert_thresholds_ticket_id_foreign` FOREIGN KEY (`ticket_id`) REFERENCES `scraped_tickets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `price_alert_thresholds_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `price_monitoring_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `price_monitoring_queue` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ticket_id` bigint unsigned NOT NULL,
  `priority` enum('low','medium','high','critical') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `next_check_at` timestamp NOT NULL,
  `check_interval_minutes` int NOT NULL DEFAULT '15',
  `consecutive_failures` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `monitoring_settings` json DEFAULT NULL,
  `last_checked_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `price_monitoring_queue_next_check_at_is_active_index` (`next_check_at`,`is_active`),
  KEY `price_monitoring_queue_priority_index` (`priority`),
  KEY `price_monitoring_queue_ticket_id_index` (`ticket_id`),
  CONSTRAINT `price_monitoring_queue_ticket_id_foreign` FOREIGN KEY (`ticket_id`) REFERENCES `scraped_tickets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `price_volatility_analytics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `price_volatility_analytics` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ticket_id` bigint unsigned NOT NULL,
  `analysis_date` date NOT NULL,
  `avg_price` decimal(10,2) NOT NULL,
  `min_price` decimal(10,2) NOT NULL,
  `max_price` decimal(10,2) NOT NULL,
  `volatility_score` decimal(5,4) NOT NULL,
  `price_changes_count` int NOT NULL,
  `max_single_change` decimal(5,2) NOT NULL,
  `trend_direction` enum('increasing','decreasing','stable') COLLATE utf8mb4_unicode_ci NOT NULL,
  `hourly_data` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `price_volatility_analytics_ticket_id_analysis_date_unique` (`ticket_id`,`analysis_date`),
  KEY `price_volatility_analytics_analysis_date_index` (`analysis_date`),
  KEY `price_volatility_analytics_volatility_score_index` (`volatility_score`),
  KEY `price_volatility_analytics_trend_direction_index` (`trend_direction`),
  CONSTRAINT `price_volatility_analytics_ticket_id_foreign` FOREIGN KEY (`ticket_id`) REFERENCES `scraped_tickets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `purchase_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `purchase_attempts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `purchase_queue_id` bigint unsigned NOT NULL,
  `scraped_ticket_id` bigint unsigned NOT NULL,
  `status` enum('pending','in_progress','success','failed','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `platform` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempted_price` decimal(10,2) DEFAULT NULL,
  `attempted_quantity` int NOT NULL DEFAULT '1',
  `transaction_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `confirmation_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `final_price` decimal(10,2) DEFAULT NULL,
  `fees` decimal(10,2) DEFAULT NULL,
  `platform_fee` decimal(10,2) DEFAULT NULL,
  `total_paid` decimal(10,2) DEFAULT NULL,
  `purchase_details` json DEFAULT NULL,
  `error_message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `failure_reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `response_data` json DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `retry_count` int NOT NULL DEFAULT '0',
  `next_retry_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `purchase_attempts_uuid_unique` (`uuid`),
  KEY `purchase_attempts_scraped_ticket_id_foreign` (`scraped_ticket_id`),
  KEY `purchase_attempts_purchase_queue_id_status_index` (`purchase_queue_id`,`status`),
  KEY `purchase_attempts_platform_status_index` (`platform`,`status`),
  KEY `purchase_attempts_transaction_id_index` (`transaction_id`),
  KEY `purchase_attempts_confirmation_number_index` (`confirmation_number`),
  KEY `idx_purchase_status_created` (`status`,`created_at`),
  KEY `idx_purchase_platform_status` (`platform`,`status`),
  KEY `purchase_attempts_user_id_foreign` (`user_id`),
  CONSTRAINT `purchase_attempts_purchase_queue_id_foreign` FOREIGN KEY (`purchase_queue_id`) REFERENCES `purchase_queues` (`id`) ON DELETE CASCADE,
  CONSTRAINT `purchase_attempts_scraped_ticket_id_foreign` FOREIGN KEY (`scraped_ticket_id`) REFERENCES `scraped_tickets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `purchase_attempts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`hdtickets`@`localhost`*/ /*!50003 TRIGGER `update_user_activity_on_purchase` AFTER INSERT ON `purchase_attempts` FOR EACH ROW UPDATE users u 
                JOIN purchase_queues pq ON pq.id = NEW.purchase_queue_id
                SET u.last_activity_at = NOW() 
                WHERE u.id = pq.selected_by_user_id */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
DROP TABLE IF EXISTS `purchase_queues`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `purchase_queues` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `scraped_ticket_id` bigint unsigned NOT NULL,
  `selected_by_user_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `status` enum('queued','processing','completed','failed','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'queued',
  `priority` enum('low','medium','high','urgent','critical') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `transaction_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `max_price` decimal(10,2) DEFAULT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `purchase_criteria` json DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `metadata` json DEFAULT NULL,
  `scheduled_for` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `started_processing_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `purchase_queues_uuid_unique` (`uuid`),
  KEY `purchase_queues_status_priority_scheduled_for_index` (`status`,`priority`,`scheduled_for`),
  KEY `purchase_queues_scraped_ticket_id_status_index` (`scraped_ticket_id`,`status`),
  KEY `purchase_queues_selected_by_user_id_index` (`selected_by_user_id`),
  KEY `purchase_queues_user_id_foreign` (`user_id`),
  CONSTRAINT `purchase_queues_scraped_ticket_id_foreign` FOREIGN KEY (`scraped_ticket_id`) REFERENCES `scraped_tickets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `purchase_queues_selected_by_user_id_foreign` FOREIGN KEY (`selected_by_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `purchase_queues_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `purchase_read_models`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `purchase_read_models` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `purchase_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ticket_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'GBP',
  `payment_method` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `purchase_details` json NOT NULL,
  `initiated_at` timestamp NOT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `failed_at` timestamp NULL DEFAULT NULL,
  `failure_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `version` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `purchase_read_models_purchase_id_unique` (`purchase_id`),
  KEY `purchase_read_models_user_id_status_index` (`user_id`,`status`),
  KEY `purchase_read_models_status_initiated_at_index` (`status`,`initiated_at`),
  KEY `purchase_read_models_user_id_index` (`user_id`),
  KEY `purchase_read_models_ticket_id_index` (`ticket_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `purchase_tracking`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `purchase_tracking` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `transaction_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `success` tinyint(1) NOT NULL,
  `platform` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `execution_time` int NOT NULL DEFAULT '0',
  `final_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `purchase_tracking_transaction_id_unique` (`transaction_id`),
  KEY `purchase_tracking_platform_success_index` (`platform`,`success`),
  KEY `purchase_tracking_user_id_created_at_index` (`user_id`,`created_at`),
  CONSTRAINT `purchase_tracking_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `query_routing_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `query_routing_rules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `rule_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `query_pattern` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `route_to` enum('master','read_replica','analytics') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'read_replica',
  `priority` int NOT NULL DEFAULT '100',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `conditions` json DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `query_routing_rules_rule_name_unique` (`rule_name`),
  KEY `query_routing_rules_is_active_priority_index` (`is_active`,`priority`),
  KEY `query_routing_rules_route_to_index` (`route_to`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `resource_access`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `resource_access` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `resource_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `resource_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `action` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `granted_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `granted_by` bigint unsigned DEFAULT NULL,
  `context` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `resource_access_user_id_index` (`user_id`),
  KEY `resource_access_resource_type_index` (`resource_type`),
  KEY `resource_access_resource_id_index` (`resource_id`),
  KEY `resource_access_action_index` (`action`),
  KEY `resource_access_expires_at_index` (`expires_at`),
  KEY `user_resource_action_index` (`user_id`,`resource_type`,`resource_id`,`action`),
  KEY `resource_access_granted_by_foreign` (`granted_by`),
  CONSTRAINT `resource_access_granted_by_foreign` FOREIGN KEY (`granted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `resource_access_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `role_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `role_id` bigint unsigned NOT NULL,
  `permission_id` bigint unsigned NOT NULL,
  `granted_at` timestamp NULL DEFAULT NULL,
  `granted_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_permissions_role_id_permission_id_unique` (`role_id`,`permission_id`),
  KEY `role_permissions_role_id_index` (`role_id`),
  KEY `role_permissions_permission_id_index` (`permission_id`),
  KEY `role_permissions_granted_by_foreign` (`granted_by`),
  CONSTRAINT `role_permissions_granted_by_foreign` FOREIGN KEY (`granted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `role_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_system_role` tinyint(1) NOT NULL DEFAULT '0',
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_unique` (`name`),
  KEY `roles_name_index` (`name`),
  KEY `roles_is_system_role_index` (`is_system_role`),
  KEY `roles_created_by_foreign` (`created_by`),
  CONSTRAINT `roles_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `scheduled_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `scheduled_reports` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `parameters` json DEFAULT NULL,
  `recipients` json DEFAULT NULL,
  `frequency` enum('daily','weekly','monthly') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'weekly',
  `format` enum('pdf','xlsx','csv') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pdf',
  `next_run` datetime NOT NULL,
  `last_run` datetime DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `scheduled_reports_created_by_foreign` (`created_by`),
  KEY `scheduled_reports_is_active_next_run_index` (`is_active`,`next_run`),
  CONSTRAINT `scheduled_reports_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `schema_snapshots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `schema_snapshots` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `snapshot_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `migration_batch` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `snapshot_type` enum('pre_migration','post_migration','manual') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pre_migration',
  `schema_sql` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `table_counts` json DEFAULT NULL,
  `index_definitions` json DEFAULT NULL,
  `foreign_keys` json DEFAULT NULL,
  `triggers` json DEFAULT NULL,
  `total_database_size_bytes` bigint DEFAULT NULL,
  `created_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  KEY `schema_snapshots_migration_batch_snapshot_type_index` (`migration_batch`,`snapshot_type`),
  KEY `schema_snapshots_created_at_index` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `scraped_tickets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `scraped_tickets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `platform` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `external_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `venue` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(2) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'US',
  `timezone` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'UTC',
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `event_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sports',
  `sport` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'football',
  `team` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `event_date` datetime DEFAULT NULL,
  `min_price` decimal(8,2) DEFAULT NULL,
  `max_price` decimal(8,2) DEFAULT NULL,
  `previous_min_price` decimal(8,2) DEFAULT NULL,
  `previous_max_price` decimal(8,2) DEFAULT NULL,
  `last_price_change` timestamp NULL DEFAULT NULL,
  `price_change_percentage` decimal(5,2) NOT NULL DEFAULT '0.00',
  `currency` enum('USD','EUR','GBP','CAD','AUD') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'USD',
  `availability` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unknown',
  `is_available` tinyint(1) NOT NULL DEFAULT '1',
  `last_available_at` timestamp NULL DEFAULT NULL,
  `availability_changes` int unsigned NOT NULL DEFAULT '0',
  `is_high_demand` tinyint(1) NOT NULL DEFAULT '0',
  `status` enum('active','sold_out','expired','cancelled','pending_verification','invalid') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `ticket_url` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `search_keyword` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `tags` json DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scraped_at` timestamp NOT NULL,
  `last_scraped_successfully` timestamp NULL DEFAULT NULL,
  `scraping_quality_score` tinyint unsigned NOT NULL DEFAULT '100',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `home_team_id` bigint unsigned DEFAULT NULL,
  `away_team_id` bigint unsigned DEFAULT NULL,
  `venue_id` bigint unsigned DEFAULT NULL,
  `league_id` bigint unsigned DEFAULT NULL,
  `competition_round` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `weather_conditions` json DEFAULT NULL,
  `predicted_demand` decimal(5,2) DEFAULT NULL,
  `view_count` int unsigned NOT NULL DEFAULT '0',
  `bookmark_count` int unsigned NOT NULL DEFAULT '0',
  `share_count` int unsigned NOT NULL DEFAULT '0',
  `popularity_score` decimal(5,2) NOT NULL DEFAULT '0.00',
  `category_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `scraped_tickets_uuid_unique` (`uuid`),
  UNIQUE KEY `scraped_tickets_slug_unique` (`slug`),
  KEY `scraped_tickets_platform_external_id_index` (`platform`,`external_id`),
  KEY `scraped_tickets_is_available_event_date_index` (`is_available`,`event_date`),
  KEY `scraped_tickets_is_high_demand_min_price_index` (`is_high_demand`,`min_price`),
  KEY `scraped_tickets_title_venue_index` (`title`,`venue`),
  KEY `scraped_tickets_scraped_at_index` (`scraped_at`),
  KEY `scraped_tickets_away_team_id_foreign` (`away_team_id`),
  KEY `scraped_tickets_league_id_foreign` (`league_id`),
  KEY `scraped_tickets_home_team_id_away_team_id_index` (`home_team_id`,`away_team_id`),
  KEY `scraped_tickets_venue_id_event_date_index` (`venue_id`,`event_date`),
  KEY `scraped_tickets_predicted_demand_index` (`predicted_demand`),
  KEY `scraped_tickets_category_id_index` (`category_id`),
  KEY `scraped_tickets_status_index` (`status`),
  KEY `idx_scraped_tickets_popular` (`view_count`,`created_at`),
  KEY `idx_scraped_tickets_bookmarked` (`bookmark_count`,`is_available`),
  KEY `idx_scraped_tickets_price_changes` (`last_price_change`,`price_change_percentage`),
  KEY `idx_scraped_tickets_location_sport` (`country`,`sport`,`event_date`),
  KEY `idx_scraped_tickets_demand` (`popularity_score`,`is_high_demand`),
  KEY `idx_scraped_tickets_complex_search` (`sport`,`is_available`,`event_date`,`min_price`,`is_high_demand`),
  KEY `idx_scraped_tickets_location_search` (`venue`,`location`,`country`,`event_date`),
  KEY `idx_scraped_tickets_platform_performance` (`platform`,`scraping_quality_score`,`last_scraped_successfully`),
  FULLTEXT KEY `idx_scraped_tickets_fulltext_search` (`title`,`venue`,`location`,`team`,`description`),
  CONSTRAINT `scraped_tickets_away_team_id_foreign` FOREIGN KEY (`away_team_id`) REFERENCES `sports_teams` (`id`) ON DELETE SET NULL,
  CONSTRAINT `scraped_tickets_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `scraped_tickets_home_team_id_foreign` FOREIGN KEY (`home_team_id`) REFERENCES `sports_teams` (`id`) ON DELETE SET NULL,
  CONSTRAINT `scraped_tickets_league_id_foreign` FOREIGN KEY (`league_id`) REFERENCES `sports_leagues` (`id`) ON DELETE SET NULL,
  CONSTRAINT `scraped_tickets_venue_id_foreign` FOREIGN KEY (`venue_id`) REFERENCES `sports_venues` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`hdtickets`@`localhost`*/ /*!50003 TRIGGER `update_ticket_availability` BEFORE UPDATE ON `scraped_tickets` FOR EACH ROW SET NEW.is_available = CASE 
                    WHEN NEW.availability > 0 AND NEW.status = "active" THEN 1
                    ELSE 0
                END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
DROP TABLE IF EXISTS `scraping_configurations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `scraping_configurations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `platform` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `settings` json DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `scraping_configurations_platform_index` (`platform`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `scraping_patterns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `scraping_patterns` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `platform` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sport` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pattern_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `pattern` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `replacement` json DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `priority` int NOT NULL DEFAULT '10',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `scraping_patterns_platform_sport_is_active_index` (`platform`,`sport`,`is_active`),
  KEY `scraping_patterns_pattern_type_index` (`pattern_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `scraping_selector_metrics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `scraping_selector_metrics` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `selector_id` bigint unsigned NOT NULL,
  `metric_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `metric_value` decimal(10,4) NOT NULL,
  `recorded_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `scraping_selector_metrics_selector_id_recorded_at_index` (`selector_id`,`recorded_at`),
  KEY `scraping_selector_metrics_metric_name_recorded_at_index` (`metric_name`,`recorded_at`),
  CONSTRAINT `scraping_selector_metrics_selector_id_foreign` FOREIGN KEY (`selector_id`) REFERENCES `selector_effectiveness` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `scraping_stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `scraping_stats` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `platform` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `method` enum('api','scraping') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'scraping',
  `operation` enum('search','event_details','venue_details') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `search_criteria` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` enum('success','failed','timeout','rate_limited','bot_detected') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `response_time_ms` int unsigned DEFAULT NULL,
  `results_count` int unsigned NOT NULL DEFAULT '0',
  `error_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `error_message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `selectors_used` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `selector_effectiveness` json DEFAULT NULL,
  `user_agent` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `scraping_stats_platform_status_created_at_index` (`platform`,`status`,`created_at`),
  KEY `scraping_stats_created_at_platform_index` (`created_at`,`platform`),
  KEY `scraping_stats_status_method_index` (`status`,`method`),
  KEY `scraping_stats_platform_index` (`platform`),
  KEY `scraping_stats_operation_index` (`operation`),
  KEY `scraping_stats_status_index` (`status`),
  KEY `idx_stats_platform_created` (`platform`,`created_at`),
  KEY `idx_stats_platform_status` (`platform`,`status`),
  KEY `idx_stats_platform_response_time` (`platform`,`response_time_ms`),
  KEY `idx_stats_created_platform` (`created_at`,`platform`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `search_analytics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `search_analytics` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `session_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `search_query` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `filters_applied` json DEFAULT NULL,
  `results_count` int unsigned NOT NULL DEFAULT '0',
  `clicks_count` int unsigned NOT NULL DEFAULT '0',
  `click_through_rate` decimal(5,4) NOT NULL DEFAULT '0.0000',
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `searched_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_search_query_time` (`search_query`,`searched_at`),
  KEY `idx_search_user_time` (`user_id`,`searched_at`),
  KEY `idx_search_performance` (`results_count`,`click_through_rate`),
  CONSTRAINT `search_analytics_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `security_incidents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `security_incidents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `severity` enum('low','medium','high','critical') COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('open','investigating','in_progress','resolved','closed') COLLATE utf8mb4_unicode_ci NOT NULL,
  `priority` enum('low','medium','high','critical') COLLATE utf8mb4_unicode_ci NOT NULL,
  `affected_user_id` bigint unsigned DEFAULT NULL,
  `source_ip` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `detection_method` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `incident_data` json DEFAULT NULL,
  `detected_at` timestamp NOT NULL,
  `assigned_to` bigint unsigned DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `resolution_notes` text COLLATE utf8mb4_unicode_ci,
  `false_positive` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `security_incidents_status_index` (`status`),
  KEY `security_incidents_severity_index` (`severity`),
  KEY `security_incidents_priority_index` (`priority`),
  KEY `security_incidents_detected_at_index` (`detected_at`),
  KEY `security_incidents_source_ip_index` (`source_ip`),
  KEY `security_incidents_affected_user_id_index` (`affected_user_id`),
  KEY `security_incidents_assigned_to_index` (`assigned_to`),
  CONSTRAINT `security_incidents_affected_user_id_foreign` FOREIGN KEY (`affected_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `security_incidents_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `selector_effectiveness`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `selector_effectiveness` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `platform` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `selector` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `page_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `success_count` int NOT NULL DEFAULT '0',
  `failure_count` int NOT NULL DEFAULT '0',
  `success_rate` decimal(5,2) NOT NULL DEFAULT '0.00',
  `last_used_at` timestamp NULL DEFAULT NULL,
  `last_successful_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_selector_platform_type` (`platform`,`selector`,`page_type`),
  KEY `idx_selector_platform_type` (`platform`,`page_type`),
  KEY `idx_selector_success_rate` (`success_rate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `shadow_table_operations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `shadow_table_operations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `operation_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `source_table` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `shadow_table` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `operation_type` enum('create','sync','swap','cleanup') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'create',
  `operation_status` enum('pending','running','completed','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `operation_sql` text COLLATE utf8mb4_unicode_ci,
  `rows_processed` bigint NOT NULL DEFAULT '0',
  `total_rows` bigint DEFAULT NULL,
  `progress_percentage` decimal(5,2) NOT NULL DEFAULT '0.00',
  `started_at` timestamp NOT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `error_message` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `shadow_table_operations_source_table_operation_status_index` (`source_table`,`operation_status`),
  KEY `shadow_table_operations_operation_status_started_at_index` (`operation_status`,`started_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sports_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sports_events` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `venue` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `city` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `country` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `event_date` date NOT NULL,
  `event_time` time NOT NULL,
  `category` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `league` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `home_team` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `away_team` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('scheduled','live','completed','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'scheduled',
  `is_monitored` tinyint(1) NOT NULL DEFAULT '0',
  `ticket_platforms` json DEFAULT NULL,
  `min_price` decimal(10,2) DEFAULT NULL,
  `max_price` decimal(10,2) DEFAULT NULL,
  `total_tickets` int DEFAULT NULL,
  `available_tickets` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sports_leagues`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sports_leagues` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sport` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `country` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'United States',
  `level` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'professional',
  `season_structure` json DEFAULT NULL,
  `aliases` json DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sports_leagues_slug_unique` (`slug`),
  KEY `sports_leagues_sport_is_active_index` (`sport`,`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sports_teams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sports_teams` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sport` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `league` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'United States',
  `aliases` json DEFAULT NULL,
  `logo_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `colors` json DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sports_teams_slug_unique` (`slug`),
  KEY `sports_teams_sport_is_active_index` (`sport`,`is_active`),
  KEY `sports_teams_league_index` (`league`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sports_venues`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sports_venues` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `city` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `state` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'United States',
  `capacity` int DEFAULT NULL,
  `coordinates` json DEFAULT NULL,
  `aliases` json DEFAULT NULL,
  `timezone` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'America/New_York',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sports_venues_slug_unique` (`slug`),
  KEY `sports_venues_city_is_active_index` (`city`,`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `system_alerts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_alerts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `alert_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alert_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alert_category` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `severity` enum('info','warning','error','critical') COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `alert_data` json DEFAULT NULL,
  `source_component` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `instance_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'default',
  `status` enum('active','acknowledged','resolved','suppressed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `triggered_at` timestamp NOT NULL,
  `acknowledged_at` timestamp NULL DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `acknowledged_by_user_id` bigint unsigned DEFAULT NULL,
  `resolved_by_user_id` bigint unsigned DEFAULT NULL,
  `resolution_notes` text COLLATE utf8mb4_unicode_ci,
  `auto_resolved` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `system_alerts_alert_id_unique` (`alert_id`),
  KEY `idx_alert_status_time` (`alert_type`,`status`,`triggered_at`),
  KEY `idx_severity_status` (`severity`,`status`),
  KEY `idx_status_time` (`status`,`triggered_at`),
  KEY `idx_instance_status` (`instance_id`,`status`),
  KEY `system_alerts_acknowledged_by_user_id_foreign` (`acknowledged_by_user_id`),
  KEY `system_alerts_resolved_by_user_id_foreign` (`resolved_by_user_id`),
  CONSTRAINT `system_alerts_acknowledged_by_user_id_foreign` FOREIGN KEY (`acknowledged_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `system_alerts_resolved_by_user_id_foreign` FOREIGN KEY (`resolved_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `system_health_metrics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_health_metrics` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `service_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('healthy','warning','critical','down') COLLATE utf8mb4_unicode_ci NOT NULL,
  `metrics_data` json NOT NULL,
  `uptime_percentage` decimal(5,2) NOT NULL DEFAULT '100.00',
  `error_count_last_hour` int NOT NULL DEFAULT '0',
  `average_response_time` decimal(8,2) DEFAULT NULL,
  `last_check_at` timestamp NOT NULL,
  `alert_thresholds` json NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `system_health_metrics_service_name_status_index` (`service_name`,`status`),
  KEY `system_health_metrics_last_check_at_index` (`last_check_at`),
  KEY `system_health_metrics_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `system_metrics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_metrics` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `metric_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `metric_group` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'system',
  `metric_value` decimal(15,4) NOT NULL,
  `metric_unit` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `instance_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'default',
  `dimensions` json DEFAULT NULL,
  `recorded_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_metric_time` (`metric_name`,`recorded_at`),
  KEY `idx_group_time` (`metric_group`,`recorded_at`),
  KEY `idx_instance_time` (`instance_id`,`recorded_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `telescope_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `telescope_entries` (
  `sequence` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `family_hash` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `should_display_on_index` tinyint(1) NOT NULL DEFAULT '1',
  `type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`sequence`),
  UNIQUE KEY `telescope_entries_uuid_unique` (`uuid`),
  KEY `telescope_entries_batch_id_index` (`batch_id`),
  KEY `telescope_entries_family_hash_index` (`family_hash`),
  KEY `telescope_entries_created_at_index` (`created_at`),
  KEY `telescope_entries_type_should_display_on_index_index` (`type`,`should_display_on_index`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `telescope_entries_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `telescope_entries_tags` (
  `entry_uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tag` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`entry_uuid`,`tag`),
  KEY `telescope_entries_tags_tag_index` (`tag`),
  CONSTRAINT `telescope_entries_tags_entry_uuid_foreign` FOREIGN KEY (`entry_uuid`) REFERENCES `telescope_entries` (`uuid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `telescope_monitoring`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `telescope_monitoring` (
  `tag` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ticket_alerts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ticket_alerts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `sports_event_id` bigint unsigned NOT NULL,
  `alert_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `max_price` decimal(10,2) DEFAULT NULL,
  `min_price` decimal(10,2) DEFAULT NULL,
  `min_quantity` int NOT NULL DEFAULT '1',
  `preferred_sections` json DEFAULT NULL,
  `platforms` json DEFAULT NULL,
  `status` enum('active','paused','triggered','expired') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `matches_found` int NOT NULL DEFAULT '0',
  `priority_score` int NOT NULL DEFAULT '50',
  `ml_prediction_data` json DEFAULT NULL,
  `escalation_level` int NOT NULL DEFAULT '0',
  `last_escalated_at` timestamp NULL DEFAULT NULL,
  `success_rate` decimal(5,4) NOT NULL DEFAULT '0.5000',
  `channel_preferences` json DEFAULT NULL,
  `email_notifications` tinyint(1) NOT NULL DEFAULT '1',
  `sms_notifications` tinyint(1) NOT NULL DEFAULT '0',
  `auto_purchase` tinyint(1) NOT NULL DEFAULT '0',
  `last_checked_at` timestamp NULL DEFAULT NULL,
  `triggered_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ticket_alerts_user_id_foreign` (`user_id`),
  KEY `ticket_alerts_sports_event_id_foreign` (`sports_event_id`),
  KEY `idx_alerts_user_status` (`user_id`,`status`),
  KEY `idx_alerts_created_status` (`created_at`,`status`),
  KEY `idx_alerts_price_range` (`min_price`,`max_price`),
  CONSTRAINT `ticket_alerts_sports_event_id_foreign` FOREIGN KEY (`sports_event_id`) REFERENCES `sports_events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ticket_alerts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`hdtickets`@`localhost`*/ /*!50003 TRIGGER `update_user_activity_on_alert_create` AFTER INSERT ON `ticket_alerts` FOR EACH ROW UPDATE users SET last_activity_at = NOW() WHERE id = NEW.user_id */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
DROP TABLE IF EXISTS `ticket_bookmarks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ticket_bookmarks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `scraped_ticket_id` bigint unsigned NOT NULL,
  `notify_price_drop` tinyint(1) NOT NULL DEFAULT '1',
  `notify_availability` tinyint(1) NOT NULL DEFAULT '1',
  `price_alert_threshold` decimal(8,2) DEFAULT NULL,
  `notification_preferences` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_ticket_bookmark` (`user_id`,`scraped_ticket_id`),
  KEY `idx_bookmarks_user_recent` (`user_id`,`created_at`),
  KEY `idx_bookmarks_ticket_alerts` (`scraped_ticket_id`,`notify_price_drop`),
  CONSTRAINT `ticket_bookmarks_scraped_ticket_id_foreign` FOREIGN KEY (`scraped_ticket_id`) REFERENCES `scraped_tickets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ticket_bookmarks_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ticket_price_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ticket_price_histories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ticket_id` bigint unsigned NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int NOT NULL DEFAULT '0',
  `recorded_at` timestamp NOT NULL,
  `source` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'scraper',
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ticket_price_histories_ticket_id_recorded_at_index` (`ticket_id`,`recorded_at`),
  KEY `ticket_price_histories_ticket_id_source_index` (`ticket_id`,`source`),
  KEY `ticket_price_histories_recorded_at_index` (`recorded_at`),
  KEY `ticket_price_histories_price_recorded_at_index` (`price`,`recorded_at`),
  CONSTRAINT `ticket_price_histories_ticket_id_foreign` FOREIGN KEY (`ticket_id`) REFERENCES `scraped_tickets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ticket_price_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ticket_price_history` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `scraped_ticket_id` bigint unsigned NOT NULL,
  `min_price` decimal(8,2) DEFAULT NULL,
  `max_price` decimal(8,2) DEFAULT NULL,
  `currency` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'USD',
  `is_available` tinyint(1) NOT NULL DEFAULT '1',
  `available_quantity` int unsigned DEFAULT NULL,
  `price_breakdown` json DEFAULT NULL,
  `recorded_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_price_history_ticket_time` (`scraped_ticket_id`,`recorded_at`),
  KEY `idx_price_history_time_price` (`recorded_at`,`min_price`),
  KEY `idx_price_history_availability` (`is_available`,`recorded_at`),
  CONSTRAINT `ticket_price_history_scraped_ticket_id_foreign` FOREIGN KEY (`scraped_ticket_id`) REFERENCES `scraped_tickets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ticket_read_models`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ticket_read_models` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ticket_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `platform_source` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `event_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `event_category` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `venue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `event_date` datetime NOT NULL,
  `current_price` decimal(10,2) NOT NULL,
  `original_price` decimal(10,2) DEFAULT NULL,
  `availability_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `available_quantity` int DEFAULT NULL,
  `price_history` json NOT NULL,
  `availability_history` json NOT NULL,
  `is_high_demand` tinyint(1) NOT NULL DEFAULT '0',
  `is_sold_out` tinyint(1) NOT NULL DEFAULT '0',
  `first_discovered_at` timestamp NOT NULL,
  `last_updated_at` timestamp NOT NULL,
  `version` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ticket_read_models_ticket_id_unique` (`ticket_id`),
  KEY `ticket_read_models_platform_source_availability_status_index` (`platform_source`,`availability_status`),
  KEY `ticket_read_models_event_category_event_date_index` (`event_category`,`event_date`),
  KEY `ticket_read_models_is_high_demand_current_price_index` (`is_high_demand`,`current_price`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ticket_seat_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ticket_seat_details` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ticket_id` bigint unsigned NOT NULL,
  `section` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `row` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `seat_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `seat_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `seat_price` decimal(10,2) DEFAULT NULL,
  `accessibility_features` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ticket_seat_details_ticket_id_index` (`ticket_id`),
  KEY `ticket_seat_details_section_row_index` (`section`,`row`),
  KEY `ticket_seat_details_seat_type_index` (`seat_type`),
  CONSTRAINT `ticket_seat_details_ticket_id_foreign` FOREIGN KEY (`ticket_id`) REFERENCES `scraped_tickets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ticket_sources`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ticket_sources` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `external_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `platform` enum('ac_milan','arsenal','atletico_madrid','axs','bandsintown','barcelona','bayern_munich','borussia_dortmund','celtic','chelsea','england_cricket','entradas','eventbrite','eventim','fnac_spectacles','inter_milan','juventus','liverpoolfc','lords_cricket','manchester_city','manchester_united','newcastle_united','official','other','psg','real_madrid','seatgeek','seetickets_uk','silverstone_f1','stubhub','ticketek_uk','ticketmaster','tickpick','tottenham','twickenham','viagogo','vivaticket','wembley','wimbledon') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `event_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `event_date` datetime NOT NULL,
  `venue` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `price_min` decimal(10,2) DEFAULT NULL,
  `price_max` decimal(10,2) DEFAULT NULL,
  `currency` varchar(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'GBP',
  `language` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'en-GB',
  `country` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'GB',
  `availability_status` enum('available','low_inventory','sold_out','not_on_sale','unknown') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unknown',
  `url` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `last_checked` datetime DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ticket_sources_platform_availability_status_index` (`platform`,`availability_status`),
  KEY `ticket_sources_event_date_index` (`event_date`),
  KEY `ticket_sources_is_active_index` (`is_active`),
  KEY `ticket_sources_external_id_index` (`external_id`),
  KEY `ticket_sources_category_id_index` (`category_id`),
  KEY `ticket_sources_currency_index` (`currency`),
  KEY `ticket_sources_language_index` (`language`),
  KEY `ticket_sources_country_index` (`country`),
  CONSTRAINT `ticket_sources_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ticket_views`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ticket_views` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `scraped_ticket_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `session_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `referrer` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `view_duration_seconds` int unsigned DEFAULT NULL,
  `interaction_data` json DEFAULT NULL,
  `viewed_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_views_ticket_time` (`scraped_ticket_id`,`viewed_at`),
  KEY `idx_views_user_time` (`user_id`,`viewed_at`),
  KEY `idx_views_time_ip` (`viewed_at`,`ip_address`),
  CONSTRAINT `ticket_views_scraped_ticket_id_foreign` FOREIGN KEY (`scraped_ticket_id`) REFERENCES `scraped_tickets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ticket_views_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tickets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tickets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('open','in_progress','pending','resolved','closed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `priority` enum('low','medium','high','urgent') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `requester_id` bigint unsigned NOT NULL,
  `assignee_id` bigint unsigned DEFAULT NULL,
  `category_id` bigint unsigned DEFAULT NULL,
  `due_date` timestamp NULL DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `last_activity_at` timestamp NULL DEFAULT NULL,
  `platform` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Source of the ticket for tracking purposes',
  `tags` json DEFAULT NULL COMMENT 'Tags for ticket categorization and filtering',
  `external_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `currency` varchar(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'USD',
  `location` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `venue` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `event_date` datetime DEFAULT NULL,
  `event_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sport` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `performer_artist` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `seat_details` json DEFAULT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT '1',
  `ticket_url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scraping_metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `additional_metadata` json DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_external_platform` (`external_id`,`platform`),
  KEY `tickets_requester_id_foreign` (`requester_id`),
  KEY `tickets_assignee_id_foreign` (`assignee_id`),
  KEY `tickets_category_id_foreign` (`category_id`),
  KEY `tickets_status_priority_index` (`status`,`priority`),
  KEY `tickets_due_date_index` (`due_date`),
  KEY `tickets_platform_is_available_index` (`platform`,`is_available`),
  KEY `tickets_event_date_platform_index` (`event_date`,`platform`),
  KEY `tickets_location_event_date_index` (`location`,`event_date`),
  KEY `idx_tickets_platform_created` (`platform`,`created_at`),
  KEY `idx_tickets_platform_event_date` (`platform`,`event_date`),
  KEY `idx_tickets_platform_location` (`platform`,`location`),
  KEY `idx_tickets_platform_status` (`platform`,`status`),
  KEY `idx_tickets_external_platform` (`external_id`,`platform`),
  KEY `idx_tickets_title_platform` (`title`,`platform`),
  KEY `idx_tickets_price_platform` (`price`,`platform`),
  KEY `tickets_last_activity_at_index` (`last_activity_at`),
  KEY `tickets_uuid_index` (`uuid`),
  FULLTEXT KEY `title` (`title`,`description`),
  CONSTRAINT `tickets_assignee_id_foreign` FOREIGN KEY (`assignee_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tickets_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tickets_requester_id_foreign` FOREIGN KEY (`requester_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_alert_preferences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_alert_preferences` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `notification_methods` json DEFAULT NULL,
  `quiet_hours_start` time DEFAULT NULL,
  `quiet_hours_end` time DEFAULT NULL,
  `quiet_days` json DEFAULT NULL,
  `timezone` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'UTC',
  `email_notifications` tinyint(1) NOT NULL DEFAULT '1',
  `sms_notifications` tinyint(1) NOT NULL DEFAULT '0',
  `browser_notifications` tinyint(1) NOT NULL DEFAULT '1',
  `daily_summary` tinyint(1) NOT NULL DEFAULT '1',
  `weekly_report` tinyint(1) NOT NULL DEFAULT '0',
  `max_daily_notifications` int NOT NULL DEFAULT '50',
  `price_drop_threshold_percent` int NOT NULL DEFAULT '10',
  `price_drop_threshold_amount` decimal(8,2) NOT NULL DEFAULT '20.00',
  `favorite_sports` json DEFAULT NULL,
  `favorite_venues` json DEFAULT NULL,
  `blocked_platforms` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_alert_preferences_user_id_unique` (`user_id`),
  CONSTRAINT `user_alert_preferences_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_dashboard_configs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_dashboard_configs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `dashboard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `widget_configuration` json NOT NULL,
  `default_filters` json DEFAULT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `is_shared` tinyint(1) NOT NULL DEFAULT '0',
  `access_permissions` json DEFAULT NULL,
  `usage_count` int NOT NULL DEFAULT '0',
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_dashboard_configs_user_id_is_default_index` (`user_id`,`is_default`),
  KEY `user_dashboard_configs_is_shared_index` (`is_shared`),
  CONSTRAINT `user_dashboard_configs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_dashboard_preferences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_dashboard_preferences` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `auto_refresh_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `refresh_interval` int NOT NULL DEFAULT '30',
  `refresh_widgets` json DEFAULT NULL,
  `enabled_widgets` json DEFAULT NULL,
  `widget_order` json DEFAULT NULL,
  `widget_sizes` json DEFAULT NULL,
  `chart_data_points` int NOT NULL DEFAULT '30',
  `chart_type_preference` enum('line','bar','area') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'line',
  `show_trends` tinyint(1) NOT NULL DEFAULT '1',
  `show_predictions` tinyint(1) NOT NULL DEFAULT '1',
  `saved_filters` json DEFAULT NULL,
  `default_filters` json DEFAULT NULL,
  `default_view` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'overview',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_dashboard_preferences_user_id_unique` (`user_id`),
  KEY `user_dashboard_preferences_auto_refresh_enabled_index` (`auto_refresh_enabled`),
  CONSTRAINT `user_dashboard_preferences_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_display_preferences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_display_preferences` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `theme` enum('light','dark','auto') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'light',
  `density` enum('compact','comfortable','spacious') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'comfortable',
  `high_contrast` tinyint(1) NOT NULL DEFAULT '0',
  `animations_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `tooltips_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `sidebar_collapsed` tinyint(1) NOT NULL DEFAULT '0',
  `widget_layout` json DEFAULT NULL,
  `widget_settings` json DEFAULT NULL,
  `compact_mode` tinyint(1) NOT NULL DEFAULT '0',
  `items_per_page` int NOT NULL DEFAULT '25',
  `column_preferences` json DEFAULT NULL,
  `sort_preferences` json DEFAULT NULL,
  `filter_preferences` json DEFAULT NULL,
  `show_price_history` tinyint(1) NOT NULL DEFAULT '1',
  `show_availability_chart` tinyint(1) NOT NULL DEFAULT '1',
  `show_trend_indicators` tinyint(1) NOT NULL DEFAULT '1',
  `date_format` enum('Y-m-d','d/m/Y','m/d/Y','d-m-Y') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Y-m-d',
  `time_format` enum('H:i','h:i A') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'H:i',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_display_preferences_user_id_unique` (`user_id`),
  KEY `user_display_preferences_theme_index` (`theme`),
  KEY `user_display_preferences_density_index` (`density`),
  CONSTRAINT `user_display_preferences_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_favorite_teams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_favorite_teams` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `sport_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `team_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `team_slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `league` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `team_logo_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `team_city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `aliases` json DEFAULT NULL,
  `email_alerts` tinyint(1) NOT NULL DEFAULT '1',
  `push_alerts` tinyint(1) NOT NULL DEFAULT '1',
  `sms_alerts` tinyint(1) NOT NULL DEFAULT '0',
  `priority` int NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_team_unique` (`user_id`,`team_slug`),
  KEY `user_favorite_teams_user_id_sport_type_index` (`user_id`,`sport_type`),
  KEY `user_favorite_teams_sport_type_league_index` (`sport_type`,`league`),
  KEY `user_favorite_teams_team_slug_index` (`team_slug`),
  FULLTEXT KEY `teams_search_fulltext` (`team_name`,`team_city`),
  CONSTRAINT `user_favorite_teams_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_favorite_venues`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_favorite_venues` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `venue_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `venue_slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `state_province` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'USA',
  `capacity` int DEFAULT NULL,
  `venue_types` json DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `venue_image_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `aliases` json DEFAULT NULL,
  `email_alerts` tinyint(1) NOT NULL DEFAULT '1',
  `push_alerts` tinyint(1) NOT NULL DEFAULT '1',
  `sms_alerts` tinyint(1) NOT NULL DEFAULT '0',
  `priority` int NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_venue_unique` (`user_id`,`venue_slug`),
  KEY `user_favorite_venues_user_id_city_index` (`user_id`,`city`),
  KEY `user_favorite_venues_venue_slug_index` (`venue_slug`),
  KEY `user_favorite_venues_country_state_province_index` (`country`,`state_province`),
  FULLTEXT KEY `venues_search_fulltext` (`venue_name`,`city`),
  CONSTRAINT `user_favorite_venues_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_legal_acceptances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_legal_acceptances` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `legal_document_id` bigint unsigned NOT NULL,
  `document_version` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `acceptance_method` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'explicit',
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `accepted_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_legal_acceptances_user_id_accepted_at_index` (`user_id`,`accepted_at`),
  KEY `user_legal_acceptances_legal_document_id_accepted_at_index` (`legal_document_id`,`accepted_at`),
  CONSTRAINT `user_legal_acceptances_legal_document_id_foreign` FOREIGN KEY (`legal_document_id`) REFERENCES `legal_documents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_legal_acceptances_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_notification_channels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_notification_channels` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `channel_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `configuration` json NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `priority_level` enum('low','medium','high','critical') COLLATE utf8mb4_unicode_ci NOT NULL,
  `delivery_schedule` json DEFAULT NULL,
  `delivery_success_count` int NOT NULL DEFAULT '0',
  `delivery_failure_count` int NOT NULL DEFAULT '0',
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_channel_priority_unique` (`user_id`,`channel_type`,`priority_level`),
  KEY `user_notification_channels_user_id_is_active_index` (`user_id`,`is_active`),
  KEY `user_notification_channels_channel_type_index` (`channel_type`),
  CONSTRAINT `user_notification_channels_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_notification_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_notification_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `channel` enum('slack','discord','telegram','webhook') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `quiet_hours_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `quiet_hours_start` time NOT NULL DEFAULT '23:00:00',
  `quiet_hours_end` time NOT NULL DEFAULT '07:00:00',
  `frequency` enum('immediate','hourly','daily') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'immediate',
  `preferences` json DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `webhook_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `channel_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slack_user_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ping_role_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `discord_user_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `chat_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `auth_type` enum('none','bearer','api_key','basic') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none',
  `auth_token` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `api_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `basic_username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `basic_password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `webhook_secret` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `custom_headers` json DEFAULT NULL,
  `max_retries` int NOT NULL DEFAULT '3',
  `retry_delay` int NOT NULL DEFAULT '1',
  `settings` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_notification_settings_user_id_channel_unique` (`user_id`,`channel`),
  KEY `user_notification_settings_user_id_is_enabled_index` (`user_id`,`is_enabled`),
  KEY `user_notification_settings_channel_index` (`channel`),
  CONSTRAINT `user_notification_settings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `permission_id` bigint unsigned NOT NULL,
  `resource_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `resource_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `granted_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `granted_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_permissions_user_id_index` (`user_id`),
  KEY `user_permissions_permission_id_index` (`permission_id`),
  KEY `user_permissions_resource_type_index` (`resource_type`),
  KEY `user_permissions_resource_id_index` (`resource_id`),
  KEY `user_permissions_expires_at_index` (`expires_at`),
  KEY `user_permissions_user_id_permission_id_resource_type_index` (`user_id`,`permission_id`,`resource_type`),
  KEY `user_permissions_granted_by_foreign` (`granted_by`),
  CONSTRAINT `user_permissions_granted_by_foreign` FOREIGN KEY (`granted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `user_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_permissions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_platform_preferences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_platform_preferences` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `platform` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `priority` tinyint NOT NULL DEFAULT '5',
  `auto_purchase_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `max_price_threshold` decimal(10,2) DEFAULT NULL,
  `notification_settings` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_platform_preferences_user_id_platform_unique` (`user_id`,`platform`),
  KEY `user_platform_preferences_user_id_priority_index` (`user_id`,`priority`),
  KEY `user_platform_preferences_platform_index` (`platform`),
  CONSTRAINT `user_platform_preferences_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_preference_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_preference_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_preference_categories_name_unique` (`name`),
  KEY `user_preference_categories_name_index` (`name`),
  KEY `user_preference_categories_is_active_index` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_preferences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_preferences` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `key` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` json NOT NULL,
  `type` enum('string','integer','boolean','array','json') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'json',
  `category` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_preferences_user_id_key_unique` (`user_id`,`key`),
  KEY `user_preferences_user_id_category_index` (`user_id`,`category`),
  KEY `user_preferences_key_category_index` (`key`,`category`),
  KEY `user_preferences_key_index` (`key`),
  KEY `user_preferences_category_index` (`category`),
  CONSTRAINT `user_preferences_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_price_preferences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_price_preferences` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `preference_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sport_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `event_category` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `min_price` decimal(10,2) DEFAULT NULL,
  `max_price` decimal(10,2) NOT NULL,
  `preferred_quantity` int NOT NULL DEFAULT '2',
  `seat_preferences` json DEFAULT NULL,
  `section_preferences` json DEFAULT NULL,
  `price_drop_threshold` decimal(5,2) NOT NULL DEFAULT '15.00',
  `price_increase_threshold` decimal(5,2) NOT NULL DEFAULT '25.00',
  `auto_purchase_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `auto_purchase_max_price` decimal(10,2) DEFAULT NULL,
  `email_alerts` tinyint(1) NOT NULL DEFAULT '1',
  `push_alerts` tinyint(1) NOT NULL DEFAULT '1',
  `sms_alerts` tinyint(1) NOT NULL DEFAULT '0',
  `alert_frequency` enum('immediate','hourly','daily') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'immediate',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_price_preferences_user_id_sport_type_index` (`user_id`,`sport_type`),
  KEY `user_price_preferences_user_id_is_active_index` (`user_id`,`is_active`),
  KEY `user_price_preferences_min_price_max_price_index` (`min_price`,`max_price`),
  CONSTRAINT `user_price_preferences_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  `assigned_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `assigned_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_roles_user_id_role_id_unique` (`user_id`,`role_id`),
  KEY `user_roles_user_id_index` (`user_id`),
  KEY `user_roles_role_id_index` (`role_id`),
  KEY `user_roles_expires_at_index` (`expires_at`),
  KEY `user_roles_assigned_by_foreign` (`assigned_by`),
  CONSTRAINT `user_roles_assigned_by_foreign` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `user_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_roles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_seat_preferences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_seat_preferences` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `venue_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `preferred_levels` json DEFAULT NULL,
  `preferred_locations` json DEFAULT NULL,
  `accessibility_needs` json DEFAULT NULL,
  `covered_seating` tinyint(1) NOT NULL DEFAULT '0',
  `close_to_amenities` tinyint(1) NOT NULL DEFAULT '0',
  `max_row` int DEFAULT NULL,
  `min_row` int DEFAULT NULL,
  `aisle_seats_preferred` tinyint(1) NOT NULL DEFAULT '0',
  `avoid_sun_glare` tinyint(1) NOT NULL DEFAULT '0',
  `group_size_preference` int NOT NULL DEFAULT '2',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_venue_type_unique` (`user_id`,`venue_type`),
  CONSTRAINT `user_seat_preferences_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `device_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `browser` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `operating_system` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_current` tinyint(1) NOT NULL DEFAULT '0',
  `is_trusted` tinyint(1) NOT NULL DEFAULT '0',
  `last_activity` timestamp NOT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_sessions_user_id_last_activity_index` (`user_id`,`last_activity`),
  KEY `user_sessions_user_id_is_current_index` (`user_id`,`is_current`),
  KEY `user_sessions_user_id_is_trusted_index` (`user_id`,`is_trusted`),
  KEY `user_sessions_last_activity_index` (`last_activity`),
  KEY `user_sessions_expires_at_index` (`expires_at`),
  CONSTRAINT `user_sessions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_sport_preferences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_sport_preferences` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `sport` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `priority` tinyint NOT NULL DEFAULT '1',
  `max_budget` decimal(10,2) DEFAULT NULL,
  `preferred_teams` json DEFAULT NULL,
  `preferred_venues` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_sport_preferences_user_id_sport_unique` (`user_id`,`sport`),
  KEY `user_sport_preferences_user_id_priority_index` (`user_id`,`priority`),
  KEY `user_sport_preferences_sport_index` (`sport`),
  CONSTRAINT `user_sport_preferences_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_sports_preferences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_sports_preferences` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `favorite_sports` json DEFAULT NULL,
  `excluded_sports` json DEFAULT NULL,
  `include_playoffs` tinyint(1) NOT NULL DEFAULT '1',
  `include_preseason` tinyint(1) NOT NULL DEFAULT '0',
  `include_exhibitions` tinyint(1) NOT NULL DEFAULT '0',
  `weekend_preference` tinyint(1) NOT NULL DEFAULT '0',
  `preferred_times` json DEFAULT NULL,
  `max_travel_distance` int DEFAULT NULL,
  `home_location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `default_max_budget` decimal(10,2) NOT NULL DEFAULT '500.00',
  `ticket_delivery_preference` enum('electronic','mobile','physical') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'mobile',
  `parking_alerts` tinyint(1) NOT NULL DEFAULT '0',
  `weather_alerts` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_sports_preferences_user_id_unique` (`user_id`),
  CONSTRAINT `user_sports_preferences_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_subscriptions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `payment_plan_id` bigint unsigned NOT NULL,
  `status` enum('active','inactive','cancelled','expired','trial') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'trial',
  `starts_at` timestamp NOT NULL,
  `ends_at` timestamp NULL DEFAULT NULL,
  `trial_ends_at` timestamp NULL DEFAULT NULL,
  `stripe_subscription_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stripe_customer_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount_paid` decimal(10,2) NOT NULL DEFAULT '0.00',
  `payment_method` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_subscriptions_user_id_status_index` (`user_id`,`status`),
  KEY `user_subscriptions_payment_plan_id_status_index` (`payment_plan_id`,`status`),
  CONSTRAINT `user_subscriptions_payment_plan_id_foreign` FOREIGN KEY (`payment_plan_id`) REFERENCES `payment_plans` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_subscriptions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `surname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` longtext COLLATE utf8mb4_unicode_ci,
  `username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_history` json DEFAULT NULL,
  `two_factor_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `two_factor_secret` longtext COLLATE utf8mb4_unicode_ci,
  `two_factor_confirmed_at` timestamp NULL DEFAULT NULL,
  `two_factor_recovery_codes` longtext COLLATE utf8mb4_unicode_ci,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_login_ip` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_login_user_agent` text COLLATE utf8mb4_unicode_ci,
  `login_count` int NOT NULL DEFAULT '0',
  `failed_login_attempts` int NOT NULL DEFAULT '0',
  `locked_until` timestamp NULL DEFAULT NULL,
  `require_2fa` tinyint(1) NOT NULL DEFAULT '0',
  `trusted_devices` json DEFAULT NULL,
  `password_changed_at` timestamp NULL DEFAULT NULL,
  `registration_source` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'public_web',
  `profile_picture` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bio` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `timezone` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'UTC',
  `language` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'en',
  `theme_preference` enum('light','dark','auto') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'light',
  `display_density` enum('compact','comfortable','spacious') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'comfortable',
  `sidebar_collapsed` tinyint(1) NOT NULL DEFAULT '0',
  `dashboard_auto_refresh` tinyint(1) NOT NULL DEFAULT '1',
  `dashboard_refresh_interval` int NOT NULL DEFAULT '30',
  `currency_preference` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'USD',
  `performance_settings` json DEFAULT NULL,
  `created_by_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'self',
  `created_by_id` bigint unsigned DEFAULT NULL,
  `last_activity_at` timestamp NULL DEFAULT NULL,
  `custom_permissions` json DEFAULT NULL,
  `email_notifications` tinyint(1) NOT NULL DEFAULT '1',
  `push_notifications` tinyint(1) NOT NULL DEFAULT '1',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_scraper_account` tinyint(1) NOT NULL DEFAULT '0',
  `deletion_protection_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `last_deletion_attempt_at` timestamp NULL DEFAULT NULL,
  `deletion_attempt_count` int NOT NULL DEFAULT '0',
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `preferences` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `role` enum('admin','agent','customer','scraper') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'customer',
  `current_subscription_id` bigint unsigned DEFAULT NULL,
  `has_trial_used` tinyint(1) NOT NULL DEFAULT '0',
  `billing_address` json DEFAULT NULL,
  `stripe_customer_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_username_unique` (`username`),
  KEY `users_created_by_id_foreign` (`created_by_id`),
  KEY `users_last_activity_at_index` (`last_activity_at`),
  KEY `users_uuid_index` (`uuid`),
  KEY `users_timezone_language_index` (`timezone`,`language`),
  KEY `users_theme_density_index` (`theme_preference`,`display_density`),
  KEY `users_two_factor_enabled_index` (`two_factor_enabled`),
  KEY `users_last_login_at_index` (`last_login_at`),
  KEY `users_failed_login_attempts_index` (`failed_login_attempts`),
  KEY `users_locked_until_index` (`locked_until`),
  KEY `users_deleted_at_index` (`deleted_at`),
  KEY `users_current_subscription_id_foreign` (`current_subscription_id`),
  KEY `idx_users_role_active` (`role`,`is_active`),
  KEY `idx_users_last_login_active` (`last_login_at`,`is_active`),
  CONSTRAINT `users_created_by_id_foreign` FOREIGN KEY (`created_by_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_current_subscription_id_foreign` FOREIGN KEY (`current_subscription_id`) REFERENCES `user_subscriptions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`hdtickets`@`localhost`*/ /*!50003 TRIGGER `log_user_changes` AFTER UPDATE ON `users` FOR EACH ROW INSERT INTO domain_events (
                    event_id, aggregate_type, aggregate_id, aggregate_version,
                    event_type, event_name, event_data, caused_by_user_id,
                    occurred_at
                )
                VALUES (
                    UUID(), "User", NEW.id, 1,
                    "UserUpdated", "User profile updated",
                    JSON_OBJECT(
                        "old_values", JSON_OBJECT("name", OLD.name, "email", OLD.email),
                        "new_values", JSON_OBJECT("name", NEW.name, "email", NEW.email)
                    ),
                    NEW.id,
                    NOW()
                ) */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
DROP TABLE IF EXISTS `v_active_ticket_monitoring`;
/*!50001 DROP VIEW IF EXISTS `v_active_ticket_monitoring`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `v_active_ticket_monitoring` AS SELECT 
 1 AS `alert_id`,
 1 AS `alert_name`,
 1 AS `alert_status`,
 1 AS `max_price`,
 1 AS `min_price`,
 1 AS `auto_purchase`,
 1 AS `user_id`,
 1 AS `user_name`,
 1 AS `user_email`,
 1 AS `event_id`,
 1 AS `event_name`,
 1 AS `event_venue`,
 1 AS `event_date`,
 1 AS `event_status`,
 1 AS `last_checked_at`,
 1 AS `triggered_at`,
 1 AS `created_at`*/;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `v_alert_effectiveness`;
/*!50001 DROP VIEW IF EXISTS `v_alert_effectiveness`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `v_alert_effectiveness` AS SELECT 
 1 AS `alert_id`,
 1 AS `alert_name`,
 1 AS `user_name`,
 1 AS `max_price`,
 1 AS `min_price`,
 1 AS `alert_created`,
 1 AS `triggered_at`,
 1 AS `days_to_trigger`,
 1 AS `purchase_attempts`,
 1 AS `successful_purchases`,
 1 AS `avg_purchase_price`,
 1 AS `avg_savings`,
 1 AS `event_name`,
 1 AS `event_date`*/;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `v_migration_health_status`;
/*!50001 DROP VIEW IF EXISTS `v_migration_health_status`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `v_migration_health_status` AS SELECT 
 1 AS `migration_batch`,
 1 AS `total_migrations`,
 1 AS `completed_count`,
 1 AS `failed_count`,
 1 AS `running_count`,
 1 AS `avg_execution_time`,
 1 AS `latest_migration`,
 1 AS `failed_validations`*/;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `v_platform_performance_metrics`;
/*!50001 DROP VIEW IF EXISTS `v_platform_performance_metrics`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `v_platform_performance_metrics` AS SELECT 
 1 AS `platform`,
 1 AS `total_operations`,
 1 AS `successful_operations`,
 1 AS `failed_operations`,
 1 AS `success_rate`,
 1 AS `avg_response_time`,
 1 AS `min_response_time`,
 1 AS `max_response_time`,
 1 AS `avg_results_per_operation`,
 1 AS `last_operation`,
 1 AS `active_days`*/;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `v_price_trend_analysis`;
/*!50001 DROP VIEW IF EXISTS `v_price_trend_analysis`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `v_price_trend_analysis` AS SELECT 
 1 AS `platform`,
 1 AS `sport`,
 1 AS `venue`,
 1 AS `price_date`,
 1 AS `tickets_tracked`,
 1 AS `avg_price`,
 1 AS `min_price`,
 1 AS `max_price`,
 1 AS `price_volatility`,
 1 AS `total_quantity`,
 1 AS `price_records`*/;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `v_ticket_availability_summary`;
/*!50001 DROP VIEW IF EXISTS `v_ticket_availability_summary`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `v_ticket_availability_summary` AS SELECT 
 1 AS `platform`,
 1 AS `sport`,
 1 AS `event_date`,
 1 AS `total_tickets`,
 1 AS `available_tickets`,
 1 AS `avg_min_price`,
 1 AS `avg_max_price`,
 1 AS `lowest_price`,
 1 AS `highest_price`,
 1 AS `total_availability`,
 1 AS `venues_count`,
 1 AS `last_updated`*/;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `v_user_purchase_analytics`;
/*!50001 DROP VIEW IF EXISTS `v_user_purchase_analytics`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `v_user_purchase_analytics` AS SELECT 
 1 AS `user_id`,
 1 AS `user_name`,
 1 AS `email`,
 1 AS `total_attempts`,
 1 AS `successful_purchases`,
 1 AS `failed_purchases`,
 1 AS `success_rate`,
 1 AS `avg_purchase_price`,
 1 AS `total_spent`,
 1 AS `first_purchase_attempt`,
 1 AS `latest_purchase_attempt`,
 1 AS `platforms_used`*/;
SET character_set_client = @saved_cs_client;
/*!50003 DROP PROCEDURE IF EXISTS `CheckMigrationHealth` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`hdtickets`@`localhost` PROCEDURE `CheckMigrationHealth`()
BEGIN
                    DECLARE v_failed_migrations INT DEFAULT 0;
                    DECLARE v_failed_validations INT DEFAULT 0;
                    DECLARE v_running_too_long INT DEFAULT 0;
                    
                    -- Check for failed migrations in last 24 hours
                    SELECT COUNT(*) INTO v_failed_migrations
                    FROM migration_executions 
                    WHERE execution_status = "failed" 
                      AND started_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR);
                    
                    -- Check for failed validations in last 24 hours
                    SELECT COUNT(*) INTO v_failed_validations
                    FROM data_validation_results 
                    WHERE validation_status = "failed" 
                      AND validated_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR);
                    
                    -- Check for migrations running too long (over 1 hour)
                    SELECT COUNT(*) INTO v_running_too_long
                    FROM migration_executions 
                    WHERE execution_status IN ("started", "in_progress")
                      AND started_at < DATE_SUB(NOW(), INTERVAL 1 HOUR);
                    
                    -- Create alerts if needed
                    IF v_failed_migrations > 0 OR v_failed_validations > 0 OR v_running_too_long > 0 THEN
                        INSERT INTO system_alerts (
                            alert_id, alert_type, alert_category, severity, title, message,
                            alert_data, source_component, triggered_at, created_at, updated_at
                        ) VALUES (
                            UUID(), "migration_health", "data_integrity", 
                            CASE 
                                WHEN v_failed_migrations > 0 OR v_running_too_long > 0 THEN "critical"
                                WHEN v_failed_validations > 0 THEN "warning"
                                ELSE "info"
                            END,
                            "Database Migration Health Alert",
                            CONCAT("Failed migrations: ", v_failed_migrations, 
                                   ", Failed validations: ", v_failed_validations,
                                   ", Long-running migrations: ", v_running_too_long),
                            JSON_OBJECT(
                                "failed_migrations", v_failed_migrations,
                                "failed_validations", v_failed_validations,
                                "long_running", v_running_too_long
                            ),
                            "migration_system",
                            NOW(), NOW(), NOW()
                        );
                    END IF;
                    
                END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `CleanupOldData` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`hdtickets`@`localhost` PROCEDURE `CleanupOldData`()
BEGIN
                    DECLARE cleanup_date DATE DEFAULT DATE_SUB(CURDATE(), INTERVAL 6 MONTH);
                    
                    -- Clean up old scraping stats (keep last 6 months)
                    DELETE FROM scraping_stats WHERE created_at < cleanup_date;
                    
                    -- Clean up old activity logs (keep last 6 months)
                    DELETE FROM activity_log WHERE created_at < cleanup_date;
                    
                    -- Clean up old cache entries
                    DELETE FROM cache_entries WHERE expires_at < NOW();
                    
                    -- Clean up old sessions (keep last 30 days)
                    DELETE FROM sessions WHERE last_activity < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 30 DAY));
                    
                    -- Clean up resolved system alerts (keep last 3 months)
                    DELETE FROM system_alerts 
                    WHERE status = "resolved" 
                      AND resolved_at < DATE_SUB(NOW(), INTERVAL 3 MONTH);
                    
                    -- Optimize tables after cleanup
                    OPTIMIZE TABLE scraping_stats, activity_log, cache_entries, sessions, system_alerts;
                END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `CreateDataBackup` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`hdtickets`@`localhost` PROCEDURE `CreateDataBackup`(
                    IN p_backup_name VARCHAR(255),
                    IN p_table_names TEXT,
                    OUT p_result VARCHAR(255)
                )
BEGIN
                    DECLARE v_table_name VARCHAR(100);
                    DECLARE v_backup_table VARCHAR(100);
                    DECLARE v_pos INT DEFAULT 1;
                    DECLARE v_next_pos INT;
                    
                    DECLARE EXIT HANDLER FOR SQLEXCEPTION 
                    BEGIN
                        GET DIAGNOSTICS CONDITION 1 @p1 = MESSAGE_TEXT;
                        SET p_result = CONCAT("Backup failed: ", @p1);
                        ROLLBACK;
                        RESIGNAL;
                    END;
                    
                    START TRANSACTION;
                    
                    -- Parse table names and create backups
                    table_loop: LOOP
                        SET v_next_pos = LOCATE(",", p_table_names, v_pos);
                        IF v_next_pos = 0 THEN
                            SET v_table_name = TRIM(SUBSTRING(p_table_names, v_pos));
                        ELSE
                            SET v_table_name = TRIM(SUBSTRING(p_table_names, v_pos, v_next_pos - v_pos));
                        END IF;
                        
                        IF LENGTH(v_table_name) > 0 THEN
                            SET v_backup_table = CONCAT("backup_", p_backup_name, "_", v_table_name);
                            
                            -- Drop existing backup table if exists
                            SET @sql = CONCAT("DROP TABLE IF EXISTS ", v_backup_table);
                            PREPARE stmt FROM @sql;
                            EXECUTE stmt;
                            DEALLOCATE PREPARE stmt;
                            
                            -- Create backup table
                            SET @sql = CONCAT("CREATE TABLE ", v_backup_table, " AS SELECT * FROM ", v_table_name);
                            PREPARE stmt FROM @sql;
                            EXECUTE stmt;
                            DEALLOCATE PREPARE stmt;
                        END IF;
                        
                        IF v_next_pos = 0 THEN
                            LEAVE table_loop;
                        END IF;
                        
                        SET v_pos = v_next_pos + 1;
                    END LOOP;
                    
                    COMMIT;
                    SET p_result = "Backup completed successfully";
                    
                END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `ExecuteMigrationRollback` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`hdtickets`@`localhost` PROCEDURE `ExecuteMigrationRollback`(
                    IN p_migration_batch VARCHAR(50),
                    IN p_user_id BIGINT UNSIGNED,
                    OUT p_result VARCHAR(255)
                )
BEGIN
                    DECLARE v_rollback_sql TEXT;
                    DECLARE v_migration_name VARCHAR(255);
                    DECLARE v_execution_id BIGINT UNSIGNED;
                    DECLARE done INT DEFAULT FALSE;
                    DECLARE rollback_cursor CURSOR FOR 
                        SELECT id, migration_name, rollback_sql 
                        FROM migration_executions 
                        WHERE migration_batch = p_migration_batch 
                          AND execution_status = "completed"
                        ORDER BY id DESC;
                    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
                    DECLARE EXIT HANDLER FOR SQLEXCEPTION 
                    BEGIN
                        GET DIAGNOSTICS CONDITION 1 @p1 = MESSAGE_TEXT;
                        SET p_result = CONCAT("Rollback failed: ", @p1);
                        ROLLBACK;
                        RESIGNAL;
                    END;
                    
                    START TRANSACTION;
                    
                    -- Create rollback execution record
                    INSERT INTO migration_executions (
                        migration_name, migration_batch, execution_type, execution_status,
                        started_at, executed_by_user_id, environment
                    ) VALUES (
                        CONCAT("ROLLBACK_", p_migration_batch), p_migration_batch, "rollback", "started",
                        NOW(), p_user_id, "production"
                    );
                    
                    SET v_execution_id = LAST_INSERT_ID();
                    
                    -- Execute rollback for each migration in reverse order
                    OPEN rollback_cursor;
                    rollback_loop: LOOP
                        FETCH rollback_cursor INTO v_execution_id, v_migration_name, v_rollback_sql;
                        IF done THEN
                            LEAVE rollback_loop;
                        END IF;
                        
                        -- Execute rollback SQL if available
                        IF v_rollback_sql IS NOT NULL AND LENGTH(v_rollback_sql) > 0 THEN
                            SET @sql = v_rollback_sql;
                            PREPARE stmt FROM @sql;
                            EXECUTE stmt;
                            DEALLOCATE PREPARE stmt;
                        END IF;
                        
                        -- Mark original migration as rolled back
                        UPDATE migration_executions 
                        SET execution_status = "rolled_back", completed_at = NOW() 
                        WHERE id = v_execution_id;
                        
                    END LOOP;
                    CLOSE rollback_cursor;
                    
                    -- Update rollback execution status
                    UPDATE migration_executions 
                    SET execution_status = "completed", completed_at = NOW(),
                        execution_time_seconds = TIMESTAMPDIFF(MICROSECOND, started_at, NOW()) / 1000000
                    WHERE id = v_execution_id;
                    
                    COMMIT;
                    SET p_result = "Rollback completed successfully";
                    
                END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `RefreshMaterializedViews` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`hdtickets`@`localhost` PROCEDURE `RefreshMaterializedViews`()
BEGIN
                    DECLARE EXIT HANDLER FOR SQLEXCEPTION 
                    BEGIN
                        ROLLBACK;
                        RESIGNAL;
                    END;
                    
                    START TRANSACTION;
                    
                    -- Refresh daily platform stats
                    DELETE FROM mv_daily_platform_stats WHERE stats_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY);
                    
                    INSERT INTO mv_daily_platform_stats (
                        stats_date, platform, total_tickets_scraped, available_tickets,
                        successful_scrapes, failed_scrapes, success_rate, avg_response_time_ms,
                        avg_min_price, avg_max_price, unique_events
                    )
                    SELECT 
                        DATE(ss.created_at) as stats_date,
                        ss.platform,
                        COUNT(*) as total_tickets_scraped,
                        SUM(CASE WHEN ss.status = "success" THEN ss.results_count ELSE 0 END) as available_tickets,
                        SUM(CASE WHEN ss.status = "success" THEN 1 ELSE 0 END) as successful_scrapes,
                        SUM(CASE WHEN ss.status = "failed" THEN 1 ELSE 0 END) as failed_scrapes,
                        ROUND((SUM(CASE WHEN ss.status = "success" THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as success_rate,
                        AVG(ss.response_time_ms) as avg_response_time_ms,
                        (SELECT AVG(st.min_price) FROM scraped_tickets st WHERE st.platform = ss.platform AND DATE(st.created_at) = DATE(ss.created_at)) as avg_min_price,
                        (SELECT AVG(st.max_price) FROM scraped_tickets st WHERE st.platform = ss.platform AND DATE(st.created_at) = DATE(ss.created_at)) as avg_max_price,
                        (SELECT COUNT(DISTINCT st.venue) FROM scraped_tickets st WHERE st.platform = ss.platform AND DATE(st.created_at) = DATE(ss.created_at)) as unique_events
                    FROM scraping_stats ss
                    WHERE ss.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                    GROUP BY DATE(ss.created_at), ss.platform;
                    
                    COMMIT;
                END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `UpdateTicketPriceStats` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`hdtickets`@`localhost` PROCEDURE `UpdateTicketPriceStats`()
BEGIN
                    UPDATE scraped_tickets st
                    SET 
                        predicted_demand = (
                            SELECT AVG(tph.price) / st.min_price 
                            FROM ticket_price_histories tph 
                            WHERE tph.ticket_id = st.id 
                              AND tph.recorded_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                        )
                    WHERE st.id IN (
                        SELECT DISTINCT tph2.ticket_id 
                        FROM ticket_price_histories tph2 
                        WHERE tph2.recorded_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                    );
                END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50001 DROP VIEW IF EXISTS `v_active_ticket_monitoring`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`hdtickets`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_active_ticket_monitoring` AS select `ta`.`id` AS `alert_id`,`ta`.`alert_name` AS `alert_name`,`ta`.`status` AS `alert_status`,`ta`.`max_price` AS `max_price`,`ta`.`min_price` AS `min_price`,`ta`.`auto_purchase` AS `auto_purchase`,`u`.`id` AS `user_id`,`u`.`name` AS `user_name`,`u`.`email` AS `user_email`,`se`.`id` AS `event_id`,`se`.`name` AS `event_name`,`se`.`venue` AS `event_venue`,`se`.`event_date` AS `event_date`,`se`.`status` AS `event_status`,`ta`.`last_checked_at` AS `last_checked_at`,`ta`.`triggered_at` AS `triggered_at`,`ta`.`created_at` AS `created_at` from ((`ticket_alerts` `ta` join `users` `u` on((`ta`.`user_id` = `u`.`id`))) join `sports_events` `se` on((`ta`.`sports_event_id` = `se`.`id`))) where ((`ta`.`status` = 'active') and (`u`.`is_active` = 1) and (`se`.`status` in ('scheduled','on_sale'))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!50001 DROP VIEW IF EXISTS `v_alert_effectiveness`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`hdtickets`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_alert_effectiveness` AS select `ta`.`id` AS `alert_id`,`ta`.`alert_name` AS `alert_name`,`u`.`name` AS `user_name`,`ta`.`max_price` AS `max_price`,`ta`.`min_price` AS `min_price`,`ta`.`created_at` AS `alert_created`,`ta`.`triggered_at` AS `triggered_at`,(to_days(`ta`.`triggered_at`) - to_days(`ta`.`created_at`)) AS `days_to_trigger`,count(`pa`.`id`) AS `purchase_attempts`,sum((case when (`pa`.`status` = 'success') then 1 else 0 end)) AS `successful_purchases`,avg(`pa`.`final_price`) AS `avg_purchase_price`,(`ta`.`max_price` - avg(`pa`.`final_price`)) AS `avg_savings`,`se`.`name` AS `event_name`,`se`.`event_date` AS `event_date` from ((((`ticket_alerts` `ta` join `users` `u` on((`ta`.`user_id` = `u`.`id`))) join `sports_events` `se` on((`ta`.`sports_event_id` = `se`.`id`))) left join `purchase_queues` `pq` on(`pq`.`scraped_ticket_id` in (select `st`.`id` from `scraped_tickets` `st` where ((`st`.`home_team_id` = `se`.`id`) or (`st`.`away_team_id` = `se`.`id`) or (`st`.`venue_id` = `se`.`id`))))) left join `purchase_attempts` `pa` on((`pq`.`id` = `pa`.`purchase_queue_id`))) where (`ta`.`triggered_at` is not null) group by `ta`.`id`,`ta`.`alert_name`,`u`.`name`,`ta`.`max_price`,`ta`.`min_price`,`ta`.`created_at`,`ta`.`triggered_at`,`se`.`name`,`se`.`event_date` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!50001 DROP VIEW IF EXISTS `v_migration_health_status`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`hdtickets`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_migration_health_status` AS select `me`.`migration_batch` AS `migration_batch`,count(0) AS `total_migrations`,sum((case when (`me`.`execution_status` = 'completed') then 1 else 0 end)) AS `completed_count`,sum((case when (`me`.`execution_status` = 'failed') then 1 else 0 end)) AS `failed_count`,sum((case when (`me`.`execution_status` in ('started','in_progress')) then 1 else 0 end)) AS `running_count`,avg(`me`.`execution_time_seconds`) AS `avg_execution_time`,max(`me`.`started_at`) AS `latest_migration`,(select count(0) from (`data_validation_results` `dvr` join `migration_executions` `me2` on((`dvr`.`migration_execution_id` = `me2`.`id`))) where ((`me2`.`migration_batch` = `me`.`migration_batch`) and (`dvr`.`validation_status` = 'failed'))) AS `failed_validations` from `migration_executions` `me` group by `me`.`migration_batch` order by `latest_migration` desc */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!50001 DROP VIEW IF EXISTS `v_platform_performance_metrics`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`hdtickets`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_platform_performance_metrics` AS select `ss`.`platform` AS `platform`,count(0) AS `total_operations`,sum((case when (`ss`.`status` = 'success') then 1 else 0 end)) AS `successful_operations`,sum((case when (`ss`.`status` = 'failed') then 1 else 0 end)) AS `failed_operations`,round(((sum((case when (`ss`.`status` = 'success') then 1 else 0 end)) / count(0)) * 100),2) AS `success_rate`,avg(`ss`.`response_time_ms`) AS `avg_response_time`,min(`ss`.`response_time_ms`) AS `min_response_time`,max(`ss`.`response_time_ms`) AS `max_response_time`,avg(`ss`.`results_count`) AS `avg_results_per_operation`,max(`ss`.`created_at`) AS `last_operation`,count(distinct cast(`ss`.`created_at` as date)) AS `active_days` from `scraping_stats` `ss` where (`ss`.`created_at` >= (now() - interval 30 day)) group by `ss`.`platform` order by `success_rate` desc,`avg_response_time` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!50001 DROP VIEW IF EXISTS `v_price_trend_analysis`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`hdtickets`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_price_trend_analysis` AS select `st`.`platform` AS `platform`,`st`.`sport` AS `sport`,`st`.`venue` AS `venue`,cast(`tph`.`recorded_at` as date) AS `price_date`,count(distinct `tph`.`ticket_id`) AS `tickets_tracked`,avg(`tph`.`price`) AS `avg_price`,min(`tph`.`price`) AS `min_price`,max(`tph`.`price`) AS `max_price`,std(`tph`.`price`) AS `price_volatility`,sum(`tph`.`quantity`) AS `total_quantity`,count(0) AS `price_records` from (`ticket_price_histories` `tph` join `scraped_tickets` `st` on((`tph`.`ticket_id` = `st`.`id`))) where (`tph`.`recorded_at` >= (now() - interval 90 day)) group by `st`.`platform`,`st`.`sport`,`st`.`venue`,cast(`tph`.`recorded_at` as date) order by `price_date` desc,`st`.`platform`,`st`.`sport` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!50001 DROP VIEW IF EXISTS `v_ticket_availability_summary`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`hdtickets`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_ticket_availability_summary` AS select `st`.`platform` AS `platform`,`st`.`sport` AS `sport`,`st`.`event_date` AS `event_date`,count(0) AS `total_tickets`,sum((case when (`st`.`is_available` = 1) then 1 else 0 end)) AS `available_tickets`,avg(`st`.`min_price`) AS `avg_min_price`,avg(`st`.`max_price`) AS `avg_max_price`,min(`st`.`min_price`) AS `lowest_price`,max(`st`.`max_price`) AS `highest_price`,sum(`st`.`availability`) AS `total_availability`,count(distinct `st`.`venue`) AS `venues_count`,max(`st`.`scraped_at`) AS `last_updated` from `scraped_tickets` `st` where (`st`.`status` = 'active') group by `st`.`platform`,`st`.`sport`,`st`.`event_date` having (`total_tickets` > 0) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!50001 DROP VIEW IF EXISTS `v_user_purchase_analytics`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`hdtickets`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_user_purchase_analytics` AS select `u`.`id` AS `user_id`,`u`.`name` AS `user_name`,`u`.`email` AS `email`,count(`pa`.`id`) AS `total_attempts`,sum((case when (`pa`.`status` = 'success') then 1 else 0 end)) AS `successful_purchases`,sum((case when (`pa`.`status` = 'failed') then 1 else 0 end)) AS `failed_purchases`,round(((sum((case when (`pa`.`status` = 'success') then 1 else 0 end)) / count(`pa`.`id`)) * 100),2) AS `success_rate`,avg(`pa`.`final_price`) AS `avg_purchase_price`,sum(`pa`.`final_price`) AS `total_spent`,min(`pa`.`started_at`) AS `first_purchase_attempt`,max(`pa`.`started_at`) AS `latest_purchase_attempt`,count(distinct `pa`.`platform`) AS `platforms_used` from ((`users` `u` left join `purchase_queues` `pq` on((`u`.`id` = `pq`.`selected_by_user_id`))) left join `purchase_attempts` `pa` on((`pq`.`id` = `pa`.`purchase_queue_id`))) where (`u`.`is_active` = 1) group by `u`.`id`,`u`.`name`,`u`.`email` having (`total_attempts` > 0) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'2024_01_14_000000_create_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'2024_01_14_120440_create_sports_events_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'2024_01_15_000000_create_ticket_alerts_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'2024_01_15_000001_create_alert_escalations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5,'2024_01_15_000002_create_automated_purchase_tracking_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6,'2024_01_15_000002_create_user_notification_settings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7,'2024_01_15_000003_create_scraped_tickets_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8,'2024_01_15_000004_create_ticket_price_histories_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9,'2024_01_15_000004_create_user_preferences_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10,'2024_01_25_000014_create_analytics_dashboards_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (11,'2025_01_20_000000_create_cache_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (12,'2025_01_21_140000_create_notifications_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (13,'2025_01_22_000000_create_scraping_stats_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (14,'2025_01_24_000000_add_enhanced_user_info_fields',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (15,'2025_01_25_000001_create_purchase_queues_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (16,'2025_01_25_000002_create_purchase_attempts_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (17,'2025_01_25_200000_create_in_app_notifications_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (18,'2025_07_21_104558_create_categories_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (19,'2025_07_21_104603_create_tickets_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (20,'2025_07_21_112500_create_ticket_sources_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (21,'2025_07_21_120000_add_platform_performance_indexes',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (22,'2025_07_21_153923_create_sessions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (23,'2025_07_21_153934_create_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (24,'2025_07_22_121653_add_last_activity_at_to_tickets_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (25,'2025_07_22_121731_add_soft_deletes_to_tickets_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (26,'2025_07_22_121948_add_missing_columns_to_categories_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (27,'2025_07_22_122538_add_missing_columns_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (28,'2025_07_22_150257_add_surname_username_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (29,'2025_07_22_162550_update_user_roles_for_scraping_system',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (30,'2025_07_22_171921_enhance_database_for_advanced_scraping_features',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (31,'2025_07_22_172441_create_sports_metadata_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (32,'2025_07_22_175852_add_phone_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (33,'2025_07_23_115843_create_activity_log_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (34,'2025_07_23_115844_add_event_column_to_activity_log_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (35,'2025_07_23_115845_add_batch_uuid_column_to_activity_log_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (36,'2025_07_23_163500_add_category_id_to_scraped_tickets_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (37,'2025_07_24_194454_add_category_id_to_ticket_sources_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (38,'2025_07_24_200253_add_status_to_scraped_tickets_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (39,'2025_07_24_230344_add_uk_sports_platforms_to_ticket_sources_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (40,'2025_07_24_231112_add_tier3_uk_sports_platforms',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (41,'2025_07_24_233036_add_european_football_platforms_to_ticket_sources',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (42,'2025_07_26_202100_add_two_factor_fields_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (43,'2025_01_27_000000_create_scheduled_reports_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (44,'2025_07_26_210543_add_source_and_tags_to_tickets_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (45,'2024_01_10_120000_ensure_profile_picture_column_exists',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (46,'2024_01_20_000000_create_ticket_alerts_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (47,'2025_01_06_160000_enhance_ticket_scraping_infrastructure',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (48,'2025_01_15_000001_create_dynamic_price_tracking_tables',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (49,'2025_01_15_000002_create_enhanced_alert_system_tables',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (50,'2025_01_15_000003_create_enhanced_analytics_tables',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (51,'2025_01_15_120000_create_comprehensive_user_preferences_tables',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (52,'2025_01_16_000000_create_sports_event_preferences_tables',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (53,'2025_01_19_000001_create_login_history_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (54,'2025_01_19_000002_create_user_sessions_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (55,'2025_01_20_120000_add_password_history_to_users_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (56,'2025_01_28_000000_add_two_factor_fields_to_users_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (57,'2025_01_30_000000_create_account_deletion_protection_tables',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (58,'2025_01_30_120000_phase4_database_normalization',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (59,'2025_09_04_093621_create_legal_documents_table',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (60,'2025_01_30_120002_phase4_new_system_tables',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (61,'2025_01_30_120003_phase4_views_and_performance_features',7);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (62,'2025_01_30_120004_phase4_migration_strategy_and_validation',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (63,'2025_01_30_140000_create_event_store_infrastructure',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (64,'2025_07_27_212256_add_failed_login_attempts_to_users_table',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (65,'2025_07_27_212451_add_locked_until_to_users_table',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (66,'2025_07_28_000001_create_payment_plans_table',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (67,'2025_07_28_000002_create_user_subscriptions_table',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (68,'2025_07_28_000003_add_subscription_fields_to_users_table',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (69,'2025_07_29_112110_create_telescope_entries_table',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (70,'2025_07_29_163322_create_oauth_auth_codes_table',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (71,'2025_07_29_163323_create_oauth_access_tokens_table',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (72,'2025_07_29_163324_create_oauth_refresh_tokens_table',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (73,'2025_07_29_163325_create_oauth_clients_table',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (74,'2025_07_29_163326_create_oauth_personal_access_clients_table',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (75,'2025_08_04_000001_add_performance_indexes',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (76,'2025_08_04_091052_create_personal_access_tokens_table',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (77,'2025_08_04_171118_add_missing_columns_to_users_table',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (78,'2025_08_04_171138_fix_scraped_tickets_availability_column',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (79,'2025_08_04_193145_increase_email_column_size_for_encryption',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (80,'2025_08_04_194423_decrypt_user_emails',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (81,'2025_08_04_194835_consolidate_and_optimize_schema',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (82,'2025_08_07_183232_create_failed_jobs_table',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (83,'2025_08_07_200706_create_activity_log_table',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (84,'2025_08_07_200707_add_event_column_to_activity_log_table',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (85,'2025_08_07_200708_add_batch_uuid_column_to_activity_log_table',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (86,'2025_08_09_123218_add_matches_found_column_to_ticket_alerts_table',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (87,'2025_08_11_172046_fix_purchase_system_fields',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (88,'2025_08_14_103223_fix_oauth_clients_uuid_field',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (89,'2025_08_14_103436_fix_oauth_access_tokens_client_id_field',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (90,'2025_08_14_103459_fix_oauth_auth_codes_client_id_field',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (91,'2025_08_14_103508_fix_oauth_personal_access_clients_client_id_field',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (92,'2025_08_20_102948_add_popularity_score_to_scraped_tickets_table',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (93,'2025_08_20_105032_create_alert_triggers_table',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (94,'2025_08_25_180816_create_cache_table',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (95,'2025_09_04_093658_create_user_legal_acceptances_table',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (96,'2025_09_05_152217_add_registration_source_to_users_table',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (97,'2025_09_05_152420_fix_phone_column_size_for_encryption',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (98,'2025_09_05_185302_create_roles_table',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (99,'2025_09_05_185321_create_permissions_table',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (100,'2025_09_05_185350_create_user_roles_table',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (101,'2025_09_05_185408_create_role_permissions_table',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (102,'2025_09_05_185422_create_user_permissions_table',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (103,'2025_09_05_185445_create_resource_access_table',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (104,'2025_09_05_190214_create_security_incidents_table',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (105,'2025_09_05_190243_create_audit_logs_table',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (106,'2025_09_05_190258_update_security_events_table_for_monitoring',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (107,'2025_09_06_075617_update_trusted_devices_table',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (108,'2025_09_06_101823_fix_encrypted_columns_size',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (109,'2025_09_06_102439_decrypt_double_encrypted_phone_data',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (110,'2025_09_06_102657_reencrypt_plain_text_phone_data',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (111,'2025_09_06_130943_create_scheduled_reports_table',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (112,'2025_09_08_083020_create_user_favorite_teams_table',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (113,'2025_09_08_083110_create_user_favorite_venues_table',8);
