-- Create notifications table for Nyalife HMS
-- This table stores notification alerts for appointment updates

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL COMMENT 'User ID for registered users',
  `guest_email` varchar(255) DEFAULT NULL COMMENT 'Email for guest appointment notifications', 
  `guest_phone` varchar(20) DEFAULT NULL COMMENT 'Phone for guest appointment notifications',
  `appointment_id` int(11) NOT NULL COMMENT 'Related appointment ID',
  `type` enum('appointment_created','appointment_updated','appointment_cancelled','appointment_reminder','appointment_completed') NOT NULL DEFAULT 'appointment_created',
  `title` varchar(255) NOT NULL COMMENT 'Notification title',
  `message` text NOT NULL COMMENT 'Notification message content',
  `status` enum('unread','read','sent','failed') NOT NULL DEFAULT 'unread',
  `priority` enum('low','normal','high','urgent') NOT NULL DEFAULT 'normal',
  `channel` enum('system','email','sms','all') NOT NULL DEFAULT 'system',
  `metadata` json DEFAULT NULL COMMENT 'Additional notification metadata',
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`notification_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_appointment_id` (`appointment_id`),
  KEY `idx_guest_email` (`guest_email`),
  KEY `idx_status` (`status`),
  KEY `idx_type` (`type`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_notification_lookup` (`user_id`, `is_read`, `created_at`),
  KEY `idx_guest_notification_lookup` (`guest_email`, `is_read`, `created_at`),
  CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_notifications_appointment` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`appointment_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample notification preferences table (optional for future enhancements)
CREATE TABLE `notification_preferences` (
  `preference_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `guest_identifier` varchar(255) DEFAULT NULL COMMENT 'Email or phone for guests',
  `appointment_created` tinyint(1) NOT NULL DEFAULT 1,
  `appointment_updated` tinyint(1) NOT NULL DEFAULT 1,
  `appointment_cancelled` tinyint(1) NOT NULL DEFAULT 1,
  `appointment_reminder` tinyint(1) NOT NULL DEFAULT 1,
  `appointment_completed` tinyint(1) NOT NULL DEFAULT 1,
  `email_notifications` tinyint(1) NOT NULL DEFAULT 1,
  `sms_notifications` tinyint(1) NOT NULL DEFAULT 0,
  `system_notifications` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`preference_id`),
  UNIQUE KEY `unique_user_preference` (`user_id`),
  UNIQUE KEY `unique_guest_preference` (`guest_identifier`),
  CONSTRAINT `fk_preferences_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
