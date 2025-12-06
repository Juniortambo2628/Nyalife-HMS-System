-- Messages Table Schema for Nyalife HMS
-- This table stores internal messages between system users

CREATE TABLE IF NOT EXISTS `messages` (
    `message_id` INT(11) NOT NULL AUTO_INCREMENT,
    `sender_id` INT(11) NOT NULL,
    `recipient_id` INT(11) NOT NULL,
    `subject` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `priority` ENUM('low', 'normal', 'high') DEFAULT 'normal',
    `is_read` TINYINT(1) DEFAULT 0,
    `is_archived` TINYINT(1) DEFAULT 0,
    `is_deleted` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `archived_at` TIMESTAMP NULL DEFAULT NULL,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`message_id`),
    INDEX `idx_sender` (`sender_id`),
    INDEX `idx_recipient` (`recipient_id`),
    INDEX `idx_recipient_unread` (`recipient_id`, `is_read`),
    INDEX `idx_created_at` (`created_at`),
    INDEX `idx_priority` (`priority`),
    INDEX `idx_archived` (`is_archived`),
    INDEX `idx_deleted` (`is_deleted`),
    FOREIGN KEY (`sender_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
    FOREIGN KEY (`recipient_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optional: Create activity_logs table if it doesn't exist
CREATE TABLE IF NOT EXISTS `activity_logs` (
    `activity_id` INT(11) NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) NOT NULL,
    `action` VARCHAR(100) NOT NULL,
    `description` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`activity_id`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_action` (`action`),
    INDEX `idx_created_at` (`created_at`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
